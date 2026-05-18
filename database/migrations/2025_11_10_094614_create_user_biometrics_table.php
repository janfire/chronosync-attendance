<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('biometric_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('fingerprint_data')->nullable(); // WebAuthn credential data
            $table->text('facial_data')->nullable(); // Facial feature data
            $table->string('credential_id')->nullable()->unique(); // WebAuthn credential ID
            $table->string('fingerprint_status')->default('pending');
            $table->string('facial_status')->default('pending');
            $table->timestamp('fingerprint_captured_at')->nullable();
            $table->timestamp('facial_captured_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('credential_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('biometric_data');
    }
};