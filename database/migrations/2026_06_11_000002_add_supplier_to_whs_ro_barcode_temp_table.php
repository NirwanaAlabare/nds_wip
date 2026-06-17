<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierToWhsRoBarcodeTempTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->table('whs_ro_barcode_temp', function (Blueprint $table) {
            $table->unsignedBigInteger('id_supplier')->nullable()->after('no_po');
            $table->string('supplier', 255)->nullable()->after('id_supplier');
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->table('whs_ro_barcode_temp', function (Blueprint $table) {
            $table->dropColumn(['id_supplier', 'supplier']);
        });
    }
}
