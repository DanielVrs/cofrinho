<?php

namespace App\Jobs;

use App\Actions\ExportExpensesCsv as BuildCsv;
use App\Enums\ExportStatus;
use App\Models\Export;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

// O job: a tarefa que vai pra fila. ShouldQueue é o que faz o dispatch não rodar na hora.
class ExportExpensesCsv implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;    // tentativas antes de desistir e ir pra failed_jobs
    public int $backoff = 5;  // segundos de espera entre uma tentativa e outra

    // o model vai pra fila só como id; o worker busca de novo no banco
    public function __construct(public Export $export) {}

    public function handle(BuildCsv $buildCsv): void
    {
        $csv = $buildCsv->handle($this->export->user, $this->export->month);

        $path = "exports/{$this->export->user_id}/lancamentos-{$this->export->month}-{$this->export->id}.csv";
        Storage::disk('local')->put($path, $csv);

        $this->export->update(['status' => ExportStatus::Done, 'path' => $path]);
    }

    // chamado uma vez, só depois que todas as tentativas falharam
    public function failed(?Throwable $exception): void
    {
        $this->export->update(['status' => ExportStatus::Failed, 'error' => $exception?->getMessage()]);
    }
}
