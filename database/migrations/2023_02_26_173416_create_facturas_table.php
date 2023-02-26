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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string("codigo")->nullable();
            $table->string("serie",10)->nullable();
            $table->integer("correlativo")->nullable();
            $table->foreignId("user_id");
            $table->tinyInteger("tipo");
            $table->date("fecha_pago");
            $table->foreignId("id_usuario")->references('id')->on('users');
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
        Schema::dropIfExists('facturas');
    }
};
