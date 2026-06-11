<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoPoToWhsBppbRoTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->table('whs_bppb_ro', function (Blueprint $table) {
            $table->string('no_po', 255)->nullable()->after('no_bppb');
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->table('whs_bppb_ro', function (Blueprint $table) {
            $table->dropColumn('no_po');
        });
    }
}
