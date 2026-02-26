<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameCollectionFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('game_collection_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_collection_id')->constrained()->onDelete('cascade');
            $table->string('key');                          // JSON key name, e.g. "SkillIDs", "Stats.ATK"
            $table->string('label');                        // Display label
            $table->string('type');                         // string, number, boolean, array, object, relation, array_of_objects
            $table->string('input_type')->default('text');  // text, textarea, number, select, checkbox, color, etc.
            $table->json('options')->nullable();             // For select dropdowns, enum values, validation rules
            $table->json('default_value')->nullable();      // Default value for new entries
            $table->boolean('required')->default(false);
            $table->boolean('is_array')->default(false);    // If true, this field holds an array of the specified type
            $table->string('parent_key')->nullable();       // For nested fields: "Stats" means this is inside Stats object
            $table->unsignedBigInteger('relation_collection_id')->nullable(); // FK to game_collections for relation type
            $table->string('relation_display_field')->nullable(); // Which field to show from related collection (e.g. "Name")
            $table->string('relation_value_field')->nullable();   // Which field value to store (e.g. "ID")
            $table->boolean('relation_multiple')->default(false); // Can select multiple related items?
            $table->integer('sort_order')->default(0);
            $table->text('help_text')->nullable();          // Tooltip/help text for the field
            $table->timestamps();

            $table->foreign('relation_collection_id')
                  ->references('id')
                  ->on('game_collections')
                  ->onDelete('set null');

            $table->unique(['game_collection_id', 'key', 'parent_key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_collection_fields');
    }
}
