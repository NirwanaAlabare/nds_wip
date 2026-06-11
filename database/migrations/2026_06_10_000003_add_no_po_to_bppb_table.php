<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoPoToBppbTable extends Migration
{
    public function up()
    {
        Schema::connection('mysql_sb')->table('bppb', function (Blueprint $table) {
            $table->string('no_po', 255)->nullable()->after('id_po');
        });
    }

    public function down()
    {
        Schema::connection('mysql_sb')->table('bppb', function (Blueprint $table) {
            $table->dropColumn('no_po');
        });
    }
}
