<?php

namespace App\Http\Controllers;

use App\User;
use App\UserRegOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio;

use Twilio\Rest\Client;
use DB,Validator;

class SmsApiController extends Controller
{


     public function otpSent(Request $request)
   {


//        $sid = getenv("TWILIO_ACCOUNT_SID");
//        $token = getenv("TWILIO_AUTH_TOKEN");

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|max:20:min:10',
        ]);

        if ($validator->fails())
        {
            return response()->json($validator->getmessagebag()->all(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        try{



            $digits = 6;
            $newOtp = rand(pow(10, $digits-1), pow(10, $digits)-1);
            $otpValidity=Carbon::now()->addMinutes(10);

            $userOtpData=UserRegOtp::where(['mobile'=>$request->mobile])->first();


            $userData=User::where('mobile',$request->mobile)->first();

            if(!empty($userData))
            {
                return response()->json(['message' =>"You have already registered ",'status'=>0], Response::HTTP_CONFLICT);
            }



            if (empty($userOtpData))
            {

                $userOtpData=UserRegOtp::create([
                    'mobile'=>$request->mobile,
                    'otp'=>$newOtp,
                    'validity'=>$otpValidity
                ]);
                $otpValidity=date('d-M-Y h:i a',strtotime($userOtpData->validity));

            }else   // elseif($userOtpData->status==UserRegOtp::NOT_VERIFIED)
            {
                $userOtpData->update([
                    'otp'=>$newOtp,
                    'validity'=>$otpValidity,
                    'status'=>UserRegOtp::NOT_VERIFIED
                ]);

                $otpValidity=date('d-M-Y h:i a',strtotime($userOtpData->validity));
            }



            $mobile='+';
            $mobile=$mobile.$request->mobile;
            //$message="New OTP : $newOtp and Validity: $otpValidity";

            // Send OPT ---

            $sid = "AC975052cef1c213e5965b6a8ee57d9aba";
            $token = "8a3f100264665607156d451fecd90d83";


            $twilio = new Client($sid, $token);

            $optMessage="Your Account Binding Tripotaxi Verification Code is $newOtp. It will expire in 10 minutes. Please do NOT share your OTP or PIN with others.";

            $message = $twilio->messages
                ->create($mobile, // to
//                    ["body" => " New OTP: $newOtp ", "from" => "+17028309421"]
                    ["body" => "$optMessage", "from" => "+17028309421"]
                );

            return response()->json(['message' =>"Otp Send Successfully","status"=>1], Response::HTTP_CREATED);

        }catch (\Exception $e)
        {
            return response()->json(['message' =>"Something Went Wrong".$e->getMessage(),"status"=>0], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


    public function verifyOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'mobile' => 'required|string|max:20:min:10',
            'otp'=> "required",
        ]);

        if ($validator->fails())
        {
            return response()->json($validator->getmessagebag()->all(),Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        try{

            $userOtpData=UserRegOtp::where(['mobile'=>$request->mobile,'otp'=>$request->otp])->first();


            if (empty($userOtpData))
            {
                return response()->json( ['message'=>'Your given otp does not match',"status"=>0],Response::HTTP_NOT_FOUND);
            }


            $validData=$userOtpData->validity;
            $validData=New Carbon($validData);
            //return date('d-M-Y h:i:s',strtotime(Carbon::now()));

            if ($userOtpData->status==UserRegOtp::NOT_VERIFIED && $validData->gt(Carbon::now()))
            {
                $userOtpData->update(['otp'=>'','status'=>UserRegOtp::VERIFIED]);

                return response()->json(['message'=>'Given Otp Verified '.$userOtpData->mobile,"status"=>1],Response::HTTP_OK);

            }else{

                return response()->json(['message'=>'Your given otp has been expired ',"status"=>0],Response::HTTP_UNAUTHORIZED);
            }


        }catch (\Exception $e)
        {
            return response()->json(['message'=>'Something Went Wrong '.$e->getMessage(),"status"=>0],Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }




}
