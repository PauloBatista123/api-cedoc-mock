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
        Schema::table('rastreabilidades', function (Blueprint $table) {
            DB::statement("ALTER TABLE `rastreabilidades` MODIFY column `operacao` ENUM('cadastrar','excluir','transferir','alterar','arquivar','emprestar','devolver','expurgar', 'repactuar')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
