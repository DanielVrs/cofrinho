<?php

namespace App\Jobs;

use App\Actions\ExportExpensesCsv as BuildCsv;
use App\Enums\ExportStatus;
use App\Models\Export;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

// O job: a tarefa que vai pra fila. ShouldQueue é o que faz o dispatch não rodar na hora.
class ExportExpensesCsv implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;     // tentativas antes de desistir e ir pra failed_jobs
    public int $backoff = 5;   // segundos de espera entre uma tentativa e outra
    public int $lockWait = 15; // segundos esperando o lock antes de desistir desta tentativa

    // o model vai pra fila só como id; o worker busca de novo no banco
    public function __construct(public Export $export) {}

    public function handle(BuildCsv $buildCsv): void
    {
        // Lock no Redis por usuário e mês: com dois workers, duas exportações do mesmo mês
        // nunca rodam ao mesmo tempo. Fica no Redis porque cada worker é um processo separado;
        // uma variável do PHP só existiria dentro de um deles. Os 60 s são a validade do lock,
        // pra ele sumir sozinho se o worker morrer no meio.
        $lock = Cache::lock("export:{$this->export->user_id}:{$this->export->month}", 60);

        // block(): espera o lock ficar livre, roda, e solta no fim (mesmo com erro).
        // Se não conseguir em $lockWait segundos, lança LockTimeoutException e o job tenta de novo.
        $lock->block($this->lockWait, function () use ($buildCsv) {
            Log::info('Exportação começou', ['export' => $this->export->id, 'month' => $this->export->month, 'worker' => gethostname()]);

            $csv = $buildCsv->handle($this->export->user, $this->export->month);

            $path = "exports/{$this->export->user_id}/lancamentos-{$this->export->month}-{$this->export->id}.csv";
            Storage::disk('local')->put($path, $csv);

            $this->export->update(['status' => ExportStatus::Done, 'path' => $path]);

            Log::info('Exportação terminou', ['export' => $this->export->id]);
        });
    }

    // chamado uma vez, só depois que todas as tentativas falharam
    public function failed(?Throwable $exception): void
    {
        $this->export->update(['status' => ExportStatus::Failed, 'error' => $exception?->getMessage()]);
    }
}
