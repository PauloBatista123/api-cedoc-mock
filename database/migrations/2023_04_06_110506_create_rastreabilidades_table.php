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
        Schema::create('rastreabilidades', function (Blueprint $table) {
            $table->id();
            $table->enum('operacao', ['cadastrar', 'excluir', 'transferir', 'alterar', 'arquivar', 'emprestar', 'devolver', 'expurgar']);
            $table->unsignedBigInteger('documento_id');
            $table->unsignedBigInteger('user_id');
            $table->string('descricao');
            $table->timestamps();

            $table->foreign('documento_id')->references('id')->on('documentos');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rastreabilidades');
    }
};
