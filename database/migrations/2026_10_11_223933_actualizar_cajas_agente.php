<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->decimal('monto_inicial_yape', 10, 2)->default(0)->after('monto_inicial');
        });

        Schema::table('movimiento_cajas', function (Blueprint $table) {
            $table->string('metodo')->default('efectivo')->after('monto'); // efectivo o yape
        });
    }

    public function down()
    {
        Schema::table('cajas', function (Blueprint $table) { $table->dropColumn('monto_inicial_yape'); });
        Schema::table('movimiento_cajas', function (Blueprint $table) { $table->dropColumn('metodo'); });
    }
};
