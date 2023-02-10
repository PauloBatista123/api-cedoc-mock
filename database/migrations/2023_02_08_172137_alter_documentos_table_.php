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
        Schema::table('documentos', function (Blueprint $table) {
           $table->string('nome_cooperado');
           $table->string('cpf_cooperado')->index();
           $table->decimal('valor_operacao')->nullable();
           $table->date('vencimento_operacao')->nullable();
           $table->date('data_liquidacao')->nullable();
           $table->date('data_expurgo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropColumn(['nome_cooperado', 'cpf_cooperado', 'valor_operacao', 'vencimento_operacao', 'data_liquidacao', 'data_expurgo']);
            $table->dropIndex('cpf_cooperado');
        });
    }
};
