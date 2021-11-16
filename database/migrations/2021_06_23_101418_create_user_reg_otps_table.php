<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRegOtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_reg_otps', function (Blueprint $table) {
            $table->increments('id');

            $table->string('mobile');
            $table->string('otp')->nullable();
            $table->timestamp('validity')->nullable();
            $table->string('status')->default(\App\UserRegOtp::NOT_VERIFIED);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_reg_otps');
    }
}
