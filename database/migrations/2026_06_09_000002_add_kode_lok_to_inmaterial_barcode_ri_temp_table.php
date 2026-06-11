<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeLokToInmaterialBarcodeRiTempTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->table('whs_inmaterial_barcode_ri_temp', function (Blueprint $table) {
            $table->string('kode_lok', 50)->nullable()->after('nama_supplier');
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->table('whs_inmaterial_barcode_ri_temp', function (Blueprint $table) {
            $table->dropColumn('kode_lok');
        });
    }
}
