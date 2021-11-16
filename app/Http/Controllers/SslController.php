<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use App\PaymentLog;
use App\Provider;
use App\Http\Controllers\ProviderResources\TripController;
use App\Http\Controllers\SendPushNotification;
use Illuminate\Support\Str;
use App\UserRequests;
use App\UserRequestPayment;

class SslController extends Controller
{
    /**
     * add wallet money for user.
     *
     * @return \Illuminate\Http\Response
     */

    public function payment(Request $request)
    {
        $this->validate($request, [
            'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,'.Auth::user()->id
        ]);

        if(Str::contains(url()->current(), 'api')){
            $val_red = 'api';
        }else{
            $val_red = 'web';
        }

        $UserRequest = UserRequests::find($request->request_id);
        $paymentMode = 'SSL';
        $tip_amount = 0;
        $random = config('constants.booking_prefix').mt_rand(100000, 999999);

        $RequestPayment = UserRequestPayment::where('request_id', $request->request_id)->first();

        if (isset($request->tips) && !empty($request->tips)) {
            $tip_amount = round($request->tips, 2);
        }

        //total amount with discount

        $totalAmount = $RequestPayment->payable + $tip_amount - $RequestPayment->discount;

        if ($totalAmount == 0) {

            $UserRequest->payment_mode = 'CARD';
            $RequestPayment->card = $RequestPayment->payable;
            $RequestPayment->payable = 0;
            $RequestPayment->tips = $tip_amount;
            $RequestPayment->provider_pay = $RequestPayment->provider_pay + $tip_amount;
            $RequestPayment->save();

            $UserRequest->paid = 1;
            $UserRequest->status = 'COMPLETED';
            $UserRequest->save();

            //for create the transaction
            (new TripController)->callTransaction($request->request_id);

            if ($val_red == 'api') {
                return response()->json(['message' => trans('api.paid')]);
            } else {
                return redirect('dashboard')->with('flash_success', trans('api.paid'));
            }

        } else {
            $log = new PaymentLog();
            $log->user_type = 'user';
            $log->transaction_code = $random;
            $log->amount = $totalAmount;
            $log->transaction_id = $UserRequest->id;
            $log->payment_mode = $paymentMode;
            $log->user_id = \Auth::user()->id;
            $log->save();

            $ssl_store_id = config('constants.ssl_store_id');
            $ssl_store_pass = config('constants.ssl_store_pass');

            if (config('constants.ssl_environment') == 'secure') {
                $ssl_url = "https://securepay.sslcommerz.com/gwprocess/v3/api.php";
            }else{
                $ssl_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";
            }

            $ssl_currency = "BDT";

            $post_data = array();
            $post_data['store_id'] = $ssl_store_id;
            $post_data['store_passwd'] = $ssl_store_pass;
            $post_data['total_amount'] = $totalAmount;
            $post_data['currency'] = $ssl_currency;
            $post_data['tran_id'] = $random;
            $post_data['success_url'] = url('/ssl/success');
            $post_data['fail_url'] = url('/ssl/fail');
            $post_data['cancel_url'] = url('/ssl/fail');
            # $post_data['multi_card_name'] = "mastercard,visacard,amexcard";  # DISABLE TO DISPLAY ALL AVAILABLE

            # EMI INFO
            $post_data['emi_option'] = "0";
            $post_data['emi_max_inst_option'] = "9";
            $post_data['emi_selected_inst'] = "9";

            # CUSTOMER INFORMATION
            $post_data['cus_name'] = $user->first_name;
            $post_data['cus_email'] = $user->email;
            $post_data['cus_add1'] = "Dhaka";
            $post_data['cus_add2'] = "Dhaka";
            $post_data['cus_city'] = "Dhaka";
            $post_data['cus_state'] = "Dhaka";
            $post_data['cus_postcode'] = "1000";
            $post_data['cus_country'] = "Bangladesh";
            $post_data['cus_phone'] = $user->mobile;
            $post_data['cus_fax'] = "01711111111";

            # SHIPMENT INFORMATION
            $post_data['ship_name'] = "Wallet Recharge";
            $post_data['ship_add1 '] = "Dhaka";
            $post_data['ship_add2'] = "Dhaka";
            $post_data['ship_city'] = "Dhaka";
            $post_data['ship_state'] = "Dhaka";
            $post_data['ship_postcode'] = "1000";
            $post_data['ship_country'] = "Bangladesh";

            # OPTIONAL PARAMETERS
            $post_data['value_a'] = $log->id;
            $post_data['value_b '] = $user_type;
            $post_data['value_c'] = $user_type;
            $post_data['value_d'] = $val_red;

            # CART PARAMETERS
            $post_data['cart'] = json_encode(array(
                array("product" => "DHK TO BRS AC A1", "amount" => "200.00"),
                array("product" => "DHK TO BRS AC A2", "amount" => "200.00"),
                array("product" => "DHK TO BRS AC A3", "amount" => "200.00"),
                array("product" => "DHK TO BRS AC A4", "amount" => "200.00"),
            ));
            $post_data['product_amount'] = "100";
            $post_data['vat'] = "5";
            $post_data['discount_amount'] = "5";
            $post_data['convenience_fee'] = "3";
            # REQUEST SEND TO SSLCOMMERZ
            $direct_api_url = $ssl_url;

            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, $direct_api_url);
            curl_setopt($handle, CURLOPT_TIMEOUT, 30);
            curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC

            $content = curl_exec($handle);

            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if ($code == 200 && !(curl_errno($handle))) {
                curl_close($handle);
                $sslcommerzResponse = $content;
            } else {
                curl_close($handle);
                echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
                exit;
            }

            # PARSE THE JSON RESPONSE
            $sslcz = json_decode($sslcommerzResponse, true);

            if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
                # THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
                echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
                #echo "<meta http-equiv='refresh' content='0;url=" . $sslcz['GatewayPageURL'] . "'>";
                # header("Location: ". $sslcz['GatewayPageURL']);
                exit;
            } else {
                echo "JSON Data parsing error!";
            }
        }    
    }

    public function add_money(Request $request)
    {
        $request->validate([
            'amount' => 'required'
        ]);
        
        if(Str::contains(url()->current(), 'api')){
            $val_red = 'api';
        }else{
            $val_red = 'web';
        }

        # REQUEST SEND TO SSLCOMMERZ
		//$direct_api_url = "https://securepay.sslcommerz.com/gwprocess/v3/api.php";

		//demo Link
		// $direct_api_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";
        //Demo Link
		// $post_data['store_id'] = "demo5da4716756f41";
		// $post_data['store_passwd'] = "demo5da4716756f41@ssl";

        $random = config('constants.booking_prefix').mt_rand(100000, 999999);

        $user_type = $request->user_type;

        $log = new PaymentLog();
        $log->user_type = $user_type;
        $log->is_wallet = '1';
        $log->amount = $request->amount;
        $log->transaction_code = $random;
        $log->payment_mode = strtoupper($request->payment_mode);
        $log->user_id = \Auth::user()->id;
        $log->save();


        $ssl_store_id = config('constants.ssl_store_id');
        $ssl_store_pass = config('constants.ssl_store_pass');

        if (config('constants.ssl_environment') == 'secure') {
            $ssl_url = "https://securepay.sslcommerz.com/gwprocess/v3/api.php";
        }else{
            $ssl_url = "https://sandbox.sslcommerz.com/gwprocess/v3/api.php";
        }

        $ssl_currency = "BDT";

        $post_data = array();
        $post_data['store_id'] = $ssl_store_id;
        $post_data['store_passwd'] = $ssl_store_pass;
        $post_data['total_amount'] = $request->amount;
        $post_data['currency'] = $ssl_currency;
        $post_data['tran_id'] = $random;
        $post_data['success_url'] = url('ssl/success');
        $post_data['fail_url'] = url('/ssl/fail');
        $post_data['cancel_url'] = url('/ssl/fail');
        # $post_data['multi_card_name'] = "mastercard,visacard,amexcard";  # DISABLE TO DISPLAY ALL AVAILABLE

        # EMI INFO
        $post_data['emi_option'] = "0";
        $post_data['emi_max_inst_option'] = "9";
        $post_data['emi_selected_inst'] = "9";

        # CUSTOMER INFORMATION
        $post_data['cus_name'] = $user->first_name;
        $post_data['cus_email'] = $user->email;
        $post_data['cus_add1'] = "Dhaka";
        $post_data['cus_add2'] = "Dhaka";
        $post_data['cus_city'] = "Dhaka";
        $post_data['cus_state'] = "Dhaka";
        $post_data['cus_postcode'] = "1000";
        $post_data['cus_country'] = "Bangladesh";
        $post_data['cus_phone'] = $user->mobile;
        $post_data['cus_fax'] = "01711111111";

        # SHIPMENT INFORMATION
        $post_data['ship_name'] = "Wallet Recharge";
        $post_data['ship_add1 '] = "Dhaka";
        $post_data['ship_add2'] = "Dhaka";
        $post_data['ship_city'] = "Dhaka";
        $post_data['ship_state'] = "Dhaka";
        $post_data['ship_postcode'] = "1000";
        $post_data['ship_country'] = "Bangladesh";

        # OPTIONAL PARAMETERS
        $post_data['value_a'] = $log->id;
        $post_data['value_b '] = $user_type;
        $post_data['value_c'] = $user_type;
        $post_data['value_d'] = $val_red;

        # CART PARAMETERS
        $post_data['cart'] = json_encode(array(
            array("product" => "DHK TO BRS AC A1", "amount" => "200.00"),
            array("product" => "DHK TO BRS AC A2", "amount" => "200.00"),
            array("product" => "DHK TO BRS AC A3", "amount" => "200.00"),
            array("product" => "DHK TO BRS AC A4", "amount" => "200.00"),
        ));
        $post_data['product_amount'] = "100";
        $post_data['vat'] = "5";
        $post_data['discount_amount'] = "5";
        $post_data['convenience_fee'] = "3";

        # REQUEST SEND TO SSLCOMMERZ
        $direct_api_url = $ssl_url;

        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $direct_api_url);
        curl_setopt($handle, CURLOPT_TIMEOUT, 30);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false); # KEEP IT FALSE IF YOU RUN FROM LOCAL PC

        $content = curl_exec($handle);

        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($code == 200 && !(curl_errno($handle))) {
            curl_close($handle);
            $sslcommerzResponse = $content;
        } else {
            curl_close($handle);
            echo "FAILED TO CONNECT WITH SSLCOMMERZ API";
            exit;
        }

        # PARSE THE JSON RESPONSE
        $sslcz = json_decode($sslcommerzResponse, true);

        if (isset($sslcz['GatewayPageURL']) && $sslcz['GatewayPageURL'] != "") {
            # THERE ARE MANY WAYS TO REDIRECT - Javascript, Meta Tag or Php Header Redirect or Other
            echo "<script>window.location.href = '". $sslcz['GatewayPageURL'] ."';</script>";
            #echo "<meta http-equiv='refresh' content='0;url=" . $sslcz['GatewayPageURL'] . "'>";
            # header("Location: ". $sslcz['GatewayPageURL']);
            exit;
        } else {
            echo "JSON Data parsing error!";
        }

    }

    public function sslSuccess(Request $request)
    {
        $log = PaymentLog::find($request->value_a);
        $log->response = json_encode($request->all());
        $log->save();

        if($log->is_wallet == 1) {

            if ($log->user_type == "user") {
                $user = \App\User::find($log->user_id);
                $wallet = (new TripController)->userCreditDebit($log->amount, $user->id, 1);
                (new SendPushNotification)->WalletMoney($user->id, currency($log->amount));
            } else if ($log->user_type == "provider") {
                $user = \App\Provider::find($log->user_id);
                $wallet = (new TripController)->providerCreditDebit($log->amount, $user->id, 1);
                (new SendPushNotification)->ProviderWalletMoney($user->id, currency($log->amount));
            }

            $wallet_balance = $user->wallet_balance+$log->amount;

            if ($request->value_d == 'api') {
                return view('ssl.success');
            } else {
                if ($log->user_type == "provider") {
                    return redirect('/provider/wallet_transation')->with('flash_success', currency($log->amount) . trans('admin.payment_msgs.amount_added'));
                } else {
                    return redirect('wallet')->with('flash_success', currency($log->amount) . trans('admin.payment_msgs.amount_added'));
                }

            }

        }

        $payment_id = $request->has('pay') ? $request->pay : 'WALLET';

        $UserRequest = UserRequests::find($log->transaction_id);

        $RequestPayment = UserRequestPayment::where('request_id', $UserRequest->id)->first();
        $RequestPayment->payment_id = $payment_id;
        $RequestPayment->payment_mode = $UserRequest->payment_mode;
        $RequestPayment->card = $RequestPayment->payable;
        $RequestPayment->save();

        $UserRequest->paid = 1;
        $UserRequest->status = 'COMPLETED';
        $UserRequest->save();

        //for create the transaction
        (new TripController)->callTransaction($UserRequest->id);
        (new SendPushNotification)->SSLPayment($UserRequest->user_id, currency($log->amount));
        (new SendPushNotification)->ProviderSSLPayment($UserRequest->provider_id, currency($log->amount));

        if ($request->value_d == 'api') {
            return view('ssl.success');
        } else {
            return redirect('dashboard')->with('flash_success', trans('api.paid'));
        }
    }

    public function sslFail(Request $request)
    {
        $log = PaymentLog::find($request->value_a);
        $log->response = json_encode($request->all());
        $log->save();

        if($log->is_wallet == 1) {

            if ($request->value_d == 'api') {
                return view('ssl.fail');
            } else {
                if ($log->user_type == "provider") {
                    return redirect('/provider/wallet_transation')->with('flash_error', 'Transaction Failed');
                } else {
                    return redirect('wallet')->with('flash_error', 'Transaction Failed');
                }
            }

        }

        if ($request->value_d == 'api') {
            return view('ssl.fail');
        } else {
            if ($log->user_type == "provider") {
                return redirect('/')->with('flash_error', 'Transaction Failed');
            } else {
                return redirect('dashboard')->with('flash_error', 'Transaction Failed');
            }

        }
        
    }

}
