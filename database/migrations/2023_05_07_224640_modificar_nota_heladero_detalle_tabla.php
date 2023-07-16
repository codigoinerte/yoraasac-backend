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
        Schema::table('nota_heladero_detalle', function (Blueprint $table) {
            $table->dropColumn(['porcentaje_devuelto', 'porcentaje_entregado']);
            $table->string('codigo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nota_heladero_detalle', function (Blueprint $table) {
            $table->integer('porcentaje_devuelto');
            $table->integer('porcentaje_entregado');
            $table->dropColumn('codigo');
        });
    }
};
