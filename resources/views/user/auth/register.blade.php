@extends('user.layout.auth')

@section('content')

<?php $login_user = asset('asset/img/login-user-bg.jpg'); ?>
<div class="full-page-bg" style="background-image: url({{$login_user}});">
<div class="log-overlay"></div>
    <div class="full-page-bg-inner">
        <div class="row no-margin">
            <div class="col-md-6 log-left">
                <span class="login-logo"><img src="{{ config('constants.site_logo', asset('logo-black.png'))}}"></span>
                <h2>Create your account and get moving in minutes</h2>
                <p>Welcome to {{config('constants.site_title','Tranxit')}}, the easiest way to get around at the tap of a button.</p>
            </div>
            <div class="col-md-6 log-right">
                <div class="login-box-outer">
                <div class="login-box row no-margin">
                    <div class="col-md-12">
                        <a class="log-blk-btn" href="{{url('login')}}">ALREADY HAVE AN ACCOUNT?</a>
                        <h3>Create a New Account</h3>
                    </div>
                    <form role="form" method="POST" action="{{ url('/register') }}">

                        <div id="first_step">
                            <div class="col-md-5">
                                {{-- <input value="+880" type="text" placeholder="+880" id="country_code" name="country_code" /> --}}
                                @include('countryCodeSelect')
                            </div> 
                            
                            <div class="col-md-7">
                                <input type="text" autofocus id="phone_number" class="form-control" placeholder="Enter Phone Number" name="phone_number" value="{{ old('phone_number') }}" data-stripe="number" maxlength="10" onkeypress="return isNumberKey(event);" />
                            </div>
                            <div class="col-md-12" id="recaptcha-container"></div>
                            <div class="col-md-7">
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
                            <div class="col-md-12" style="padding-bottom: 10px;color:red;" id="mobile_verfication"></div>
                            <div class="col-md-12" style="padding-bottom: 10px;">
                                <input type="button" class="log-teal-btn small" id="verify_btn" onclick="smsLogin();" value="Verify Phone Number"/>
                            </div>

                        </div>

                        {{ csrf_field() }}

                        <div id="second_step" style="display:none;">

                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="First Name" name="first_name" value="{{ old('first_name') }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="First Name can only contain alphanumeric characters and . - spaces">

                                @if ($errors->has('first_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Last Name" name="last_name" value="{{ old('last_name') }}" data-validation="alphanumeric" data-validation-allowing=" -" data-validation-error-msg="Last Name can only contain alphanumeric characters and . - spaces">

                                @if ($errors->has('last_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <input type="email" class="form-control" name="email" placeholder="Email Address" value="{{ old('email') }}" data-validation="email">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif                        
                            </div>

                            <div class="col-md-12">
                                <label class="radio-inline"><input type="radio" name="gender" value="MALE" data-validation="required" data-validation-error-msg="Please choose one gender">&nbsp;&nbsp;&nbsp; Male</label>
                                <label class="radio-inline"><input type="radio" name="gender"  value="FEMALE">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Female</label>
                                @if ($errors->has('gender'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('gender') }}</strong>
                                    </span>
                                @endif                        
                            </div>

                            <div class="col-md-12">
                                <input type="password" class="form-control" name="password" placeholder="Password" data-validation="length" data-validation-length="min6" data-validation-error-msg="Password should not be less than 6 characters">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-12">
                                <input type="password" placeholder="Re-type Password" class="form-control" name="password_confirmation" data-validation="confirmation" data-validation-confirm="password" data-validation-error-msg="Confirm Passsword is not matched">

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                            @if(config('constants.referral') == 1)
                            <div class="col-md-12">
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
                            
                            <div class="col-md-12">
                                <button class="log-teal-btn" type="submit">REGISTER</button>
                            </div>

                        </div>

                    </form>     

                    <div class="col-md-12">
                        <p class="helper">Or <a href="{{route('login')}}">Sign in</a> with your user account.</p>   
                    </div>

                </div>


                <div class="log-copy"><p class="no-margin">{{ config('constants.site_copyright', '&copy; '.date('Y').' Appoets') }}</p></div>
                </div>
            </div>
        </div>
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
    $('#mobile_verfication').html("");
    $('#phone_number').fadeOut(400);
    $('#country_code').fadeOut(400);
    $('.select2-container').fadeOut(400);
    $('#verify_btn').hide();
    $.post("{{url('/user/verify-credentials')}}",{ _token: '{{csrf_token()}}', mobile :phoneNumber, country_code : countryCode })
    .done(function(data){
        console.log(data);
        if(data.message=="Mobile Number Already Exists"){
            console.log("exist");
             $('#phone_number').fadeIn(400);
             $('#country_code').fadeIn(400);
             $('.select2-container').fadeIn(400);
             $('#verify_btn').show();
             $('#mobile_verfication').html("<p class='helper' style=\"color:red;\"> "+" The given phone number is taken by another user. Please try using another number. "+" </p>");
        }
        else{
             $('#mobile_verfication').html("");
             phoneAuth();
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
            $('#mobile_verfication').html("");
            $('#phone_number').fadeOut(400);
            $('#country_code').fadeOut(400);
            $('.select2-container').fadeOut(400);
            $('#verify_btn').hide();
            $('#verifyCode').fadeOut(400);
            $('#second_step').fadeIn(400);
        }).catch(function (error) {
            $('#mobile_verfication').html("<p class='helper'> "+error.message+" </p>");
        });
    }
</script>
@endsection
