<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('laravel-messages.tables.messages', 'messages'), function (Blueprint $table) {
            //
            $table->timestamp('marked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('laravel-messages.tables.messages', 'messages'), function (Blueprint $table) {
            //
            $table->dropColumn('marked_at');
        });
    }
}