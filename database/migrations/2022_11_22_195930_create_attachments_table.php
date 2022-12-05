<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-messages.tables.attachments','attachments'), function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('message_id')->unsigned()->index();
            $table->string('title');
            $table->string('file');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on(config('laravel-messages.tables.messages', 'messages'))->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-messages.tables.attachments','attachments'));
    }
}