<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToGameCollectionsTable extends Migration
{
    public function up()
    {
        Schema::table('game_collections', function (Blueprint $table) {
            $table->string('type')->default('tabular')->after('name_field');
            // 'tabular' = normal spreadsheet data (entries with fields)
            // 'static'  = raw nested JSON (stored as single blob, rendered as tree)
        });
    }

    public function down()
    {
        Schema::table('game_collections', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
