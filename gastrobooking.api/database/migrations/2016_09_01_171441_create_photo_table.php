<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePhotoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photos', function(Blueprint $table)
        {
            $table->string('id', 40)->primary()->unique();
            $table->string('item_id', 40);
            $table->string('item_type');
            $table->string('original_photo');
            $table->string('minimised_photo');
            $table->string('directory_path');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')
                ->references('id')->on('restaurants')
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
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign('photos_item_id_foreign');
        });
        Schema::drop('photos');
    }
}
