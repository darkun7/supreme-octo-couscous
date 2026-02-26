<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameCollectionsTable extends Migration
{
    public function up()
    {
        Schema::create('game_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('name');         // e.g. "enemies", "skills", "items"
            $table->string('slug');          // URL-friendly version
            $table->string('display_name');            // Human-readable name
            $table->string('icon')->default('📦');     // Emoji or icon class
            $table->text('description')->nullable();
            $table->string('id_field')->default('ID'); // Which JSON field is the primary identifier
            $table->string('name_field')->default('Name'); // Which JSON field is used as display name
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'slug']);
            $table->unique(['game_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_collections');
    }
}
