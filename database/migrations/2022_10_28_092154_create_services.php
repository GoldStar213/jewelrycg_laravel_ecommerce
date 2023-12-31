<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->integer('status'); // 0 Draft, 1 Active, 2 Pending, 3 Paused, 4 Requires Changes, 5 Deleted
            $table->integer('user_id');
            $table->string('name');
            $table->string('slug');
            $table->text('content')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('gallery')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->text('reviewer_notes_private')->nullable();
            $table->integer('option_custompricing')->nullable(); //Custom pricing only 0 = no, 1 = yes
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
};
