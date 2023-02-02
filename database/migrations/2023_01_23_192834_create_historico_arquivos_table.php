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
        Schema::create('historico_arquivos', function (Blueprint $table) {
            $table->id();
            $table->date('data_fim');
            $table->date('data_inicio');
            $table->enum('status', ['arquivado', 'expurgar'])->default('arquivado');
            $table->unsignedBigInteger('predio_id');
            $table->unsignedBigInteger('andar_id');
            $table->unsignedBigInteger('caixa_id');
            $table->unsignedBigInteger('documento_id');
            $table->timestamps();

            $table->foreign('predio_id')->references('id')->on('predios');
            $table->foreign('andar_id')->references('id')->on('andars');
            $table->foreign('caixa_id')->references('id')->on('caixas');
            $table->foreign('documento_id')->references('id')->on('documentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historico_arquivos');
    }
};
