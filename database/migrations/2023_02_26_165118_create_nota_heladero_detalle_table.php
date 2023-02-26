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
        Schema::create('nota_heladero_detalle', function (Blueprint $table) {
            $table->id();
            $table->integer("devolucion")->default(0);
            $table->decimal("porcentaje_devuelto")->default(0);
            $table->integer("pedido")->default(0);
            $table->decimal("porcentaje_entregado")->default(0);
            $table->string("codigo",50);
            $table->integer("vendido")->default(0);            
            $table->decimal("importe")->default(0);
            $table->foreignId("nota_heladeros_id")->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('nota_heladero_detalle');
    }
};
