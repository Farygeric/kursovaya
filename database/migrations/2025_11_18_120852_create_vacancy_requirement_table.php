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
        Schema::create('vacancy_requirement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained()->onDelete('cascade');
            $table->foreignId('requirement_item_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->unique(['vacancy_id', 'requirement_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancy_requirement');
    }
};
