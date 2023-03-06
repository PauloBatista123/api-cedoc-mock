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
            $table->string('documento');
            $table->string('observacao')->nullable();
            $table->unsignedBigInteger('tipo_documento_id')->nullable();
            $table->unsignedBigInteger('caixa_id')->nullable();
            $table->unsignedBigInteger('predio_id')->nullable();
            $table->decimal('espaco_ocupado', 8, 2);
            $table->enum('status', ['aguardando', 'arquivado', 'emprestimo'])->default('aguardando');
            $table->timestamps();

            $table->foreign('tipo_documento_id')->references('id')->on('tipo_documentos');
            $table->foreign('caixa_id')->references('id')->on('caixas');
            $table->foreign('predio_id')->references('id')->on('predios');
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
