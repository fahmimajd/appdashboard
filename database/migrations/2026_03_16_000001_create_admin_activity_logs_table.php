<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->string('action', 50); // login, logout, create, update, delete, approve, reject, export
            $table->string('module', 100); // kinerja, belum-rekam, belum-akte, pendamping, user-management, etc.
            $table->text('description')->nullable();
            $table->string('route_name')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('request_data')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
