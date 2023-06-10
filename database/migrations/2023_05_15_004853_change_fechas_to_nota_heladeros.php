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
        Schema::table('nota_heladeros', function (Blueprint $table) {
            $table->datetime('fecha_guardado')->nullable()->change();
            $table->datetime('fecha_apertura')->nullable()->change();
            $table->datetime('fecha_cierre')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nota_heladeros', function (Blueprint $table) {
            $table->date('fecha_guardado')->change();
            $table->date('fecha_apertura')->change();
            $table->date('fecha_cierre')->change();
        });
    }
};
