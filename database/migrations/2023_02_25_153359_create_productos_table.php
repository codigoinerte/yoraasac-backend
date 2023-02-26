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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string("codigo");
            $table->string("nombre");
            $table->integer("orden")->nullable()->default(0);
            $table->tinyInteger("estado");
            $table->integer("stock_alerta");
            $table->decimal("precio_venta", 7, 2);
            $table->decimal("descuento", 7, 2);
            $table->tinyInteger("idtipo_igv");
            $table->tinyInteger("destacado");            
            $table->foreignId("estados_id");
            $table->foreignId("unspsc_id");
            $table->foreignId("marcas_id");
            $table->foreignId("unidad_id");
            $table->foreignId("moneda_id");
            $table->foreignId("igv_id");
            $table->timestamps();

            // ->references('id')->on('users')
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
