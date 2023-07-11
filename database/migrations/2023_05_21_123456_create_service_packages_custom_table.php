<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicePackagesCustomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_packages_custom', function (Blueprint $table) {
            $table->id();
            $table->integer('status'); // 0 default, 1 = acceppted, 2 = declined, 3 is withdrawn, 4 = expired
            $table->integer('requirements_status')->default(0); // 0 default (inherit from service), 1 = No
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->text('description');
            $table->integer('price');
            $table->integer('revisions');
            $table->integer('delivery_time');
            $table->integer('expiration_time');
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
        Schema::dropIfExists('service_packages_custom');
    }
}
