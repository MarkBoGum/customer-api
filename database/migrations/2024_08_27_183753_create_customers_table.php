<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary key
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('username');
            $table->string('gender');
            $table->date('dob');
            $table->string('phone');
            $table->string('cell')->nullable();
            $table->string('country');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('street_name')->nullable();
            $table->integer('street_number')->nullable();
            $table->string('postcode')->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->string('timezone_offset')->nullable();
            $table->string('timezone_description')->nullable();
            $table->string('password', 32); // MD5 hash for password
            $table->string('picture_large')->nullable();
            $table->string('picture_medium')->nullable();
            $table->string('picture_thumbnail')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
