<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 100);
            $table->string('key', 120);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['group_name', 'key']);
            $table->index('group_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
