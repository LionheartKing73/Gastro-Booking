<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddClientUserRelationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function ($table) {
            $table->string('profile_type');
        });
        Schema::table('client',function ($table){
            $table->integer('ID_user')->unsigned();

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
        Schema::table('user', function ($table) {
            $table->dropColumn('profile_type');
        });
        Schema::table('client',function ($table) {
            $table->dropForeign(['ID_user']);
        });
    }
}
