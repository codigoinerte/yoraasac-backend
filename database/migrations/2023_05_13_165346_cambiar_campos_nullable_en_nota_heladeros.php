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
            Schema::table('nota_heladeros', function (Blueprint $table) {
                $table->decimal("monto")->default(0)->change();
                $table->decimal("pago")->default(0)->change();
                $table->decimal("debe")->default(0)->change();
                $table->decimal("ahorro")->default(0)->change();

                $table->integer("cucharas")->default(0)->change();
                $table->integer("conos")->default(0)->change();

                $table->integer("placas_entregas")->default(0)->change();
                $table->integer("placas_devueltas")->default(0)->change();

                $table->date('fecha_guardado')->nullable()->change();
                $table->date('fecha_apertura')->nullable()->change();
                $table->date('fecha_cierre')->nullable()->change();

                $table->integer("cucharas_devueltas")->default(0);
                $table->integer("conos_devueltas")->default(0);
            });
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

            $table->decimal("ahorro")->change();

            $table->integer("cucharas")->change();
            $table->integer("conos")->change();

            $table->integer("placas_entregas")->change();
            $table->integer("placas_devueltas")->change();

            $table->date('fecha_guardado')->change();
            $table->date('fecha_apertura')->change();
            $table->date('fecha_cierre')->change();

            $table->dropColumn("cucharas_devueltas");
            $table->dropColumn("conos_devueltas");            
        });
    }
};
