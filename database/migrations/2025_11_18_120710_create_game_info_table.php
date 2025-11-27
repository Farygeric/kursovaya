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
        Schema::create('game_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('genre_id')->constrained()->onDelete('cascade');
            $table->unique(['game_id', 'genre_id']);
        });

        Schema::create('game_platform', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('platform_id')->constrained()->onDelete('cascade');
            $table->unique(['game_id', 'platform_id']);
        });

        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->string('label')->nullable(); 
            $table->timestamps();
        });

        Schema::create('game_link', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('link_id')->constrained()->onDelete('cascade');
            $table->unique(['game_id', 'link_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('game_link');
        Schema::dropIfExists('links');
        Schema::dropIfExists('game_platform');
        Schema::dropIfExists('game_genre');
    }
};
