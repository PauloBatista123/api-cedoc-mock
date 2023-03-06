<?php

namespace App\Jobs;

use App\Imports\NewDocumentosImport;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ProcessImportNewDossie implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $arquivo;
    /**
     * Create a new job instance.
     *
     * @return void
     * @param string $arquivo
     */
    public function __construct(string $arquivo)
    {
        $this->arquivo = $arquivo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new NewDocumentosImport)->import($this->arquivo, 'public', \Maatwebsite\Excel\Excel::XLSX);
    }
}
