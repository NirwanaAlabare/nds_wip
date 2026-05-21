<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSewingOutTables extends Migration
{
    public function up()
    {
        $connection = 'mysql_sb';

        Schema::connection($connection)->create('sewing_out_h', function (Blueprint $table) {
            $table->id();
            $table->string('no_bppb', 30)->unique();
            $table->date('tgl_bppb');
            $table->string('no_po', 50)->nullable();
            $table->string('id_supplier')->nullable();
            $table->string('jenis_pengeluaran', 100)->nullable();
            $table->string('jenis_dok', 100)->nullable();
            $table->decimal('berat_garment', 15, 2)->default(0);
            $table->decimal('berat_karton', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('created_by', 100)->nullable();
            $table->string('approved_by', 100)->nullable();
            $table->datetime('approved_date')->nullable();
            $table->timestamps();
        });

        Schema::connection($connection)->create('sewing_out_det', function (Blueprint $table) {
            $table->id();
            $table->string('no_bppb', 30)->nullable();
            $table->string('id_po')->nullable();
            $table->string('id_jo')->nullable();
            $table->string('id_item')->nullable();
            $table->string('color', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('status', 5)->default('Y');
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });

        Schema::connection($connection)->create('sewing_out_det_temp', function (Blueprint $table) {
            $table->id();
            $table->string('id_po')->nullable();
            $table->string('id_jo')->nullable();
            $table->string('id_item')->nullable();
            $table->string('color', 100)->nullable();
            $table->string('size', 50)->nullable();
            $table->string('unit', 20)->nullable();
            $table->decimal('qty', 15, 2)->default(0);
            $table->string('status', 5)->default('Y');
            $table->string('created_by', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $connection = 'mysql_sb';
        Schema::connection($connection)->dropIfExists('sewing_out_det_temp');
        Schema::connection($connection)->dropIfExists('sewing_out_det');
        Schema::connection($connection)->dropIfExists('sewing_out_h');
    }
}
