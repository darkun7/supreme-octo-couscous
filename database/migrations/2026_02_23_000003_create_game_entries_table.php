<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('game_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_collection_id')->constrained()->onDelete('cascade');
            $table->json('data');                          // The actual JSON data
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('game_collection_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_entries');
    }
}
