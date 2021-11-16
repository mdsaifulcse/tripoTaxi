<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRegOtp extends Model
{
    const VERIFIED='Verified';
    const NOT_VERIFIED='NotVerified';

    protected $table = 'user_reg_otps';
    protected $softDelete = true;

    protected $fillable =['mobile','otp','validity','status'];
}
