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
            $table->timestamps();
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
