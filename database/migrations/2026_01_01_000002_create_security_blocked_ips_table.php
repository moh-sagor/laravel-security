<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityBlockedIpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shield_blocked_ips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip_address', 45)->index();
            $table->string('ip_hash', 64)->unique();
            $table->string('reason', 255)->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('created_by', 64)->default('system');
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
        Schema::dropIfExists('shield_blocked_ips');
    }
}
