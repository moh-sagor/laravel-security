<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shield_security_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_id', 64)->unique();
            $table->string('type', 64)->index();
            $table->string('severity', 20)->default('medium')->index();
            $table->float('confidence')->default(1.0);
            $table->integer('risk_score')->default(0)->index();
            $table->string('ip_hash', 64)->index();
            $table->text('ip_address_encrypted')->nullable();
            $table->string('user_id', 64)->nullable()->index();
            $table->string('route', 255)->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->string('country', 10)->nullable();
            $table->string('payload_hash', 64)->nullable();
            $table->string('action', 30)->default('allow')->index();
            $table->longText('metadata')->nullable();
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
        Schema::dropIfExists('shield_security_events');
    }
}
