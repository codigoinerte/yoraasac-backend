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
        Schema::create('nota_heladeros', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id");
            $table->foreignId("moneda_id");
            $table->foreignId("id_sucursal");
            $table->decimal("monto");
            $table->decimal("pago");
            $table->decimal("debe");
            $table->decimal("ahorro");
            $table->integer("estado");
            $table->integer("cucharas");
            $table->integer("conos");
            $table->integer("placas_entregas");
            $table->integer("placas_devueltas");
            $table->date("fecha_guardado");
            $table->date("fecha_apertura");
            $table->date("fecha_cierre");            
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
        Schema::dropIfExists('nota_heladeros');
    }
};
