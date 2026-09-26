<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityRouteMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shield_route_maps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('original_route', 255)->unique();
            $table->string('alias_path', 255)->unique();
            $table->string('hash', 64)->index();
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
        Schema::dropIfExists('shield_route_maps');
    }
}
