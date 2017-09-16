<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant', function(Blueprint $table)
        {
            $table->string('id', 40)->primary()->unique();
            $table->string('ID_user', 40);
            $table->string('ID_restaurant_type', 40);
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('street');
            $table->string('city');
            $table->string('post_code', 10);
            $table->string('address_note');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('accept_payment');
            $table->string('company_number');
            $table->string('company_tax_number');
            $table->string('account_number');
            $table->string('short_descr');
            $table->string('long_descr');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ID_user')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('restaurant');
    }
}
