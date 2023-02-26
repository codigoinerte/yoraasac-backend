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
        Schema::create('moneda', function (Blueprint $table) {
            $table->id();
            $table->string("moneda");
            $table->string("simbolo", 10);
            $table->string("codigo", 5)->nullable();
            $table->tinyInteger("digitos")->default(2);
            $table->string("sep_decimales",10)->nullable();
            $table->string("sep_miles", 10)->nullable();
            $table->tinyInteger("principal")->default(0);
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
        Schema::dropIfExists('moneda');
    }
};
