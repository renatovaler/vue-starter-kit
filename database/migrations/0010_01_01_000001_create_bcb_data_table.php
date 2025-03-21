<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBcbDataTable extends Migration
{
    public function up()
    {
        Schema::create('bcb_data', function (Blueprint $table) {
            $table->id();
            $table->integer('bcb_code');
            $table->date('data');
            $table->double('valor', 15, 8);
            $table->timestamps();

            $table->unique(['bcb_code', 'data']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bcb_data');
    }
}
