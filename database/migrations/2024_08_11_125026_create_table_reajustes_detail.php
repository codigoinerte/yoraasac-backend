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
        Schema::create('reajustes_detail', function (Blueprint $table) {
            $table->id();
            $table->string("codigo");
            $table->integer("cantidad_ingreso")->default(0);
            $table->integer("cantidad_salida")->default(0);
            $table->integer("reajuste_id");
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
        Schema::dropIfExists('reajustes_detail');
    }
};
