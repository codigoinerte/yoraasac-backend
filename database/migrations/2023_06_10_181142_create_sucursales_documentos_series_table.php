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
        Schema::create('sucursales_documentos_series', function (Blueprint $table) {
            $table->id();
            $table->integer("idsucursal");
            $table->integer("tipo");
            $table->string("serie");
            $table->string("correlativo");
            $table->tinyInteger("estado");
            $table->tinyInteger("principal");
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
        Schema::dropIfExists('sucursales_documentos_series');
    }
};
