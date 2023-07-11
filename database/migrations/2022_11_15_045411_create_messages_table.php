<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('conversation_id')->nullable();
            $table->text('message')->nullable();
            $table->integer('is_seen')->nullable()->default(0);
            $table->integer('is_archived')->nullable()->default(0);
            $table->integer('is_notified_notification')->nullable()->default(0);
            $table->integer('is_notified_email')->nullable()->default(0);
            $table->text('is_message_deleted')->nullable();
            $table->integer('is_deleted')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
