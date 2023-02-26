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
        Schema::create('factura_detalle', function (Blueprint $table) {
            $table->id();
            $table->string("codigo",50);
            $table->decimal("precio")->default(0);
            $table->integer("descuento")->default(0);
            $table->integer("cantidad")->default(0);
            $table->foreignId("facturas_id")->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('factura_detalle');
    }
};
