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
        Schema::create('reajustes', function (Blueprint $table) {
            $table->id();
            $table->string("codigo")->nullable();
            $table->string("codigo_ingreso")->nullable();
            $table->string("codigo_salida")->nullable();
            $table->integer("user_id");
            $table->date("fecha_reajuste");
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
        Schema::dropIfExists('reajustes');
    }
};
