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
        Schema::create('stock_helados', function (Blueprint $table) {
            $table->id();
            $table->string("codigo_movimiento");
            $table->integer("movimientos_id");
            $table->integer("tipo_documento_id");
            $table->string("numero_documento")->nullable();
            $table->date("fecha_movimiento")->nullable();
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
        Schema::dropIfExists('stock_helados');
    }
};
