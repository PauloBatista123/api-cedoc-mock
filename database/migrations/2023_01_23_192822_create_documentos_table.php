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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_documento_id');
            $table->unsignedBigInteger('caixa_id');
            $table->decimal('espaco_ocupado', 8, 2);
            $table->enum('status', ['aguardando', 'arquivado', 'emprestimo'])->default('aguardando');
            $table->timestamps();

            $table->foreign('tipo_documento_id')->references('id')->on('tipo_documentos');
            $table->foreign('caixa_id')->references('id')->on('caixas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documentos');
    }
};
