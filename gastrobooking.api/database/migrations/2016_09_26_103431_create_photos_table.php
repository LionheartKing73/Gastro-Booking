<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('photo', function(Blueprint $table)
        {
            $table->integer("id")->primary();
            $table->integer("item_id");
            $table->string("item_type", 50);
            $table->string("extension", 10);
            $table->string("original_photo_name", 100);
            $table->string("minified_image_name", 100);

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
        Schema::drop('photo');
    }
}
