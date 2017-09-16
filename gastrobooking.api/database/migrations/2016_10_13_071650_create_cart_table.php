<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("cart", function(Blueprint $table){
            $table->increments("id");
            $table->integer("ID_client");
            $table->integer("ID_grouped_client");
            $table->integer("ID_restaurant");
            $table->integer("ID_menu_list");
            $table->integer("quantity");
            $table->boolean("is_child");
            $table->text("comment");
            $table->tinyInteger("status");
            $table->boolean("delivery");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("cart");
    }
}
