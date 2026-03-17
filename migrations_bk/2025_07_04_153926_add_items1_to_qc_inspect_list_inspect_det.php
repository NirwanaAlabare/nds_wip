<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItems1ToQcInspectListInspectDet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qc_inspect_list_inspect_det', function (Blueprint $table) {
            $table->decimal('percentage', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qc_inspect_list_inspect_det', function (Blueprint $table) {
            //
        });
    }
}
