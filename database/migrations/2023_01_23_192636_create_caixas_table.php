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
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['disponivel', 'ocupado'])->default('disponivel');
            $table->string('numero')->nullable();
            $table->decimal('espaco_total', 8, 2);
            $table->decimal('espaco_ocupado', 8, 2)->nullable();
            $table->decimal('espaco_disponivel', 8, 2)->nullable();
            $table->unsignedBigInteger('unidade_id');
            $table->unsignedBigInteger('endereco_id');
            $table->timestamps();

            $table->foreign('unidade_id')->references('id')->on('unidades');
            $table->foreign('endereco_id')->references('id')->on('enderecos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caixas');
    }
};
