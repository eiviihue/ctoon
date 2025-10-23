<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('covers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained()->onDelete('cascade');
            $table->string('path'); // storage path (relative to disk)
            $table->string('filename')->nullable();
            $table->integer('size')->nullable(); // bytes
            $table->string('disk')->default('public');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('covers');
    }
};
