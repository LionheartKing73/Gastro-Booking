<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantOpenTabl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('restaurant_order', function(Blueprint $table)
        {
            $table->string('id', 40)->primary()->unique();
            $table->string('ID_restaurant');
            $table->string('date');
            $table->string('m_starting_time');
            $table->string('m_ending_time');
            $table->string('a_starting_time');
            $table->string('a_ending_time');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::drop('restaurant_order');
    }
}
