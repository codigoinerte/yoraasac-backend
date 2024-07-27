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
        DB::unprepared('
            CREATE TRIGGER after_fecha_apertura_update
            AFTER UPDATE ON nota_heladeros
            FOR EACH ROW
            BEGIN

                DECLARE existing_record INT;
                SET existing_record = 0;

                SELECT COUNT(*) INTO existing_record 
                FROM asistencias 
                WHERE nota_id = OLD.id 
                    AND user_id  = OLD.user_id
                    AND DAY(fecha) = DAY(NOW())
                    AND MONTH(fecha) = MONTH(NOW())
                    AND YEAR(fecha) = YEAR(NOW());

                -- Check if fecha_apertura has changed
                IF OLD.fecha_apertura <> NEW.fecha_apertura THEN
                    INSERT INTO asistencias (user_id, nota_id, fecha)
                    VALUES (NEW.user_id, NEW.id, NOW());
                
                -- Check if fecha_cierre has changed
                ELSEIF (OLD.fecha_cierre <> NEW.fecha_cierre OR (OLD.fecha_cierre IS NULL AND NEW.fecha_cierre IS NOT NULL)) AND existing_record = 0 THEN
                    INSERT INTO asistencias (user_id, nota_id, fecha)
                    VALUES (NEW.user_id, NEW.id, NOW());
                END IF;
                
            END;');
       
        DB::unprepared('
            CREATE TRIGGER after_fecha_apertura_insert
            AFTER INSERT ON nota_heladeros
            FOR EACH ROW
            BEGIN
                IF NEW.estado = 2 THEN
                    INSERT INTO asistencias (user_id, nota_id, fecha)
                    VALUES (NEW.user_id, NEW.id, NOW());
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_fecha_apertura_update');

        DB::unprepared('DROP TRIGGER IF EXISTS after_fecha_apertura_insert');
    }
};
