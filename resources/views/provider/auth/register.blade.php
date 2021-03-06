@extends('provider.layout.auth')

@section('content')
<div class="col-md-12">
    <a class="log-blk-btn" href="{{ url('/provider/login') }}">@lang('provider.signup.already_register')</a>
    <h3>@lang('provider.signup.sign_up')</h3>
</div>

<div class="col-md-12">
    <form class="form-horizontal" role="form" method="POST" action="{{ url('/provider/register') }}">

        <div id="first_step">
            <div class="col-md-4">
                <input value="+880" type="text" placeholder="+880" id="country_code" name="country_code" />
            </div> 
            
            <div class="col-md-8">
                <input type="text" autofocus id="phone_number" class="form-control" placeholder="@lang('provider.signup.enter_phone')" name="phone_number" value="{{ old('phone_number') }}" data-stripe="number" maxlength="10" onkeypress="return isNumberKey(event);" />
            </div>
            <div class="col-md-12" id="recaptcha-container"></div>
            <div class="col-md-8">
                @if ($errors->has('phone_number'))
                    <span class="help-block">
                        <strong>{{ $errors->first('phone_number') }}</strong>
                    </span>
                @endif
            </div>
            <div class="col-md-12" id="verifyCode" style="display:none">
                <input type="text" class="form-control" id="verCode" placeholder="Enter Verification Code" autocomplete="false" >
                <input type="button" class="log-teal-btn small" onclick="codeverify();" value="Verify Code"/>
            </div>
            <div class="col-md-12" style="padding-bottom: 10px;" id="mobile_verfication"></div>
            <div class="col-md-12" style="padding-bottom: 10px;">
                <input type="button" class="log-teal-btn small verify_btn" onclick="smsLogin();" value="Verify Phone Number"/>
            </div>
        </div>

        {{ csrf_field() }}

        <div id="second_step" style="display: none;">
            <div>
                <input id="fname" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" placeholder="@lang('provider.profile.first_name')" autofocus data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.first_name') can only contain alphanumeric characters and . - spaces">
                @if ($errors->has('first_name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('first_name') }}</strong>
                    </span>
                @endif
            </div>
            <div>
                <input id="lname" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" placeholder="@lang('provider.profile.last_name')"data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.last_name') can only contain alphanumeric characters and . - spaces">            
                @if ($errors->has('last_name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('last_name') }}</strong>
                    </span>
                @endif
            </div>
            <div>
                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="@lang('provider.signup.email_address')" data-validation="email">            
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
            </div>
            <div>
                <label class="checkbox"><input type="radio" name="gender" value="MALE" data-validation="required"  data-validation-error-msg="Please choose one gender">@lang('provider.signup.male')</label>
                <label class="checkbox"><input type="radio" name="gender" value="FEMALE" data-validation-error-msg="Please choose one gender">@lang('provider.signup.female')</label>
                @if ($errors->has('gender'))
                    <span class="help-block">
                        <strong>{{ $errors->first('gender') }}</strong>
                    </span>
                @endif
            </div>                        
            <div>
                <input id="password" type="password" class="form-control" name="password" placeholder="@lang('provider.signup.password')" data-validation="length" data-validation-length="min6" data-validation-error-msg="Password should not be less than 6 characters">

                @if ($errors->has('password'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>    
            <div>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="@lang('provider.signup.confirm_password')" data-validation="confirmation" data-validation-confirm="password" data-validation-error-msg="Confirm Passsword is not matched">

                @if ($errors->has('password_confirmation'))
                    <span class="help-block">
                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </span>
                @endif
            </div>  
            @if (config('constants.paypal_adaptive') == 1)
            <div>
                <input id="service-model" type="text" class="form-control" name="paypal_email" value="{{ old('paypal_email') }}" placeholder="@lang('provider.profile.paypal_email')" data-validation="email">
                
                @if ($errors->has('paypal_email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('paypal_email') }}</strong>
                    </span>
                @endif
            </div>
            <span class="help-block">
                        <strong style="color: red; font-size: 10spx;">Please add verified Paypal Email, otherwise you won't receive the payment.</strong>
                    </span>
            @endif
            <div>
                <select class="form-control" name="service_type" id="service_type" data-validation="required">
                    <option value="">Select Service</option>
                    @foreach(get_all_service_types() as $type)
                        <option value="{{$type->id}}">{{$type->name}}</option>
                    @endforeach
                </select>

                @if ($errors->has('service_type'))
                    <span class="help-block">
                        <strong>{{ $errors->first('service_type') }}</strong>
                    </span>
                @endif
            </div>
            <div>
                <input id="service-number" type="text" class="form-control" name="service_number" value="{{ old('service_number') }}" placeholder="@lang('provider.profile.car_number')" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.car_number') can only contain alphanumeric characters and - spaces">
                
                @if ($errors->has('service_number'))
                    <span class="help-block">
                        <strong>{{ $errors->first('service_number') }}</strong>
                    </span>
                @endif
            </div>
            <div>
                <input id="service-model" type="text" class="form-control" name="service_model" value="{{ old('service_model') }}" placeholder="@lang('provider.profile.car_model')" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="@lang('provider.profile.car_model') can only contain alphanumeric characters and - spaces">
                
                @if ($errors->has('service_model'))
                    <span class="help-block">
                        <strong>{{ $errors->first('service_model') }}</strong>
                    </span>
                @endif
            </div>
            @if(config('constants.referral') == 1)
                <div>
                    <input type="text" placeholder="Referral Code (Optional)" class="form-control" name="referral_code" >

                    @if ($errors->has('referral_code'))
                        <span class="help-block">
                            <strong>{{ $errors->first('referral_code') }}</strong>
                        </span>
                    @endif
                </div>
            @else
                <input type="hidden" name="referral_code" >
            @endif
            <button type="submit" class="log-teal-btn">
                @lang('provider.signup.register')
            </button>

        </div>
    </form>
</div>
@endsection


@section('scripts')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/6.0.2/firebase.js"></script>
<script type="text/javascript">
    @if (count($errors) > 0)
        $("#second_step").show();
    @endif
    $.validate({
        modules : 'security',
    });
    $('.checkbox-inline').on('change', function() {
        $('.checkbox-inline').not(this).prop('checked', false);  
    });
    function isNumberKey(evt)
    {
        var edValue = document.getElementById("phone_number");
        var s = edValue.value;
        if (event.keyCode == 13) {
            event.preventDefault();
            if(s.length>=10){
                smsLogin();
            }
        }
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode != 46 && charCode > 31 
        && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }
</script>
<script>
  // phone form submission handler
  function smsLogin() {
    var countryCode = document.getElementById("country_code").value;
    var phoneNumber = document.getElementById("phone_number").value;

    $.post("{{url('/provider/verify-credentials')}}",{ _token: '{{csrf_token()}}', mobile :phoneNumber, country_code : countryCode })
    .done(function(data){
        console.log(data);
        if(data.message == 'available'){
            $('#phone_number').attr('readonly', true);
            $('#country_code').attr('readonly', true);
            $('.select2-container').attr('readonly', true);
            $('.verify_btn').hide();
            phoneAuth();
        }else{
            alert('Mobile/Email Already exist in other account');
        }
        
    })
    .fail(function(xhr, status, error) {
        $('#mobile_verfication').html("<p class='helper'> "+xhr.responseJSON.message+" </p>");
    });
  }

</script>


<script>
    // Your web app's Firebase configuration
    var firebaseConfig = {
        apiKey: "AIzaSyBUh5U3D-zOQUAZkCnrBQAAhhvknuOnpsY",
        authDomain: "tripotaxi-1f158.firebaseapp.com",
        databaseURL: "https://tripotaxi-1f158.firebaseio.com",
        projectId: "tripotaxi-1f158",
        storageBucket: "tripotaxi-1f158.appspot.com",
        messagingSenderId: "694552807513",
        appId: "1:694552807513:web:c308d58cf48335c04c8948",
        measurementId: "G-7912C44SJQ"
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
</script>

<script>
    window.onload=function () {
        render();
    };
    function render() {
        window.recaptchaVerifier=new firebase.auth.RecaptchaVerifier('recaptcha-container',
        {
        size: "invisible",
            callback: function(response) {
                submitPhoneNumberAuth();
            }
        });
        recaptchaVerifier.render();
    }
    function phoneAuth() {
        //Show Verification Code Form
        $('#verifyCode').fadeIn(400);
        //get the number
        var country=document.getElementById('country_code').value;
        var phone=document.getElementById('phone_number').value;
        var number=country+phone;
        //phone number authentication function of firebase
        //it takes two parameter first one is number,,,second one is recaptcha
        firebase.auth().signInWithPhoneNumber(number,window.recaptchaVerifier).then(function (confirmationResult) {
            //s is in lowercase
            window.confirmationResult=confirmationResult;
            coderesult=confirmationResult;
            console.log(coderesult);
        }).catch(function (error) {
            $('#mobile_verfication').html("<p class='helper'> "+error.message+" </p>");
        });
    }
    function codeverify() {
        var code=document.getElementById('verCode').value;
        coderesult.confirm(code).then(function (result) {
            $('#verifyCode').fadeOut(400);
            $('#second_step').fadeIn(400);
        }).catch(function (error) {
            $('#mobile_verfication').html("<p class='helper'> "+error.message+" </p>");
        });
    }
</script>
@endsection
