<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoPoToWhsRoBarcodeTempTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->table('whs_ro_barcode_temp', function (Blueprint $table) {
            $table->string('no_po', 100)->nullable()->after('no_ws');
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->table('whs_ro_barcode_temp', function (Blueprint $table) {
            $table->dropColumn('no_po');
        });
    }
}
