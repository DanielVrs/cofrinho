<?php

use App\Enums\ExportStatus;
use App\Jobs\ExportExpensesCsv;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    Storage::fake('local');
});

// roda o job como o worker faria, esperando no máximo 1 s pelo lock
function runExportJobWithLock($export): void
{
    $job = new ExportExpensesCsv($export);
    $job->lockWait = 1;
    $job->handle(app(\App\Actions\ExportExpensesCsv::class));
}

test('duas exportações do mesmo mês não rodam ao mesmo tempo', function () {
    $export = $this->user->exports()->create(['month' => '2026-11']);

    // outro worker está exportando este mês: segura o lock
    $lock = Cache::lock("export:{$this->user->id}:2026-11", 10);
    expect($lock->get())->toBeTrue();

    expect(fn () => runExportJobWithLock($export))->toThrow(LockTimeoutException::class);
    expect($export->refresh()->status)->toBe(ExportStatus::Pending);

    // o outro worker terminou: solta o lock e o job passa
    $lock->release();
    runExportJobWithLock($export);
    expect($export->refresh()->status)->toBe(ExportStatus::Done);
});

test('exportações de meses diferentes não se bloqueiam', function () {
    $export = $this->user->exports()->create(['month' => '2026-11']);

    Cache::lock("export:{$this->user->id}:2026-10", 10)->get();

    runExportJobWithLock($export);
    expect($export->refresh()->status)->toBe(ExportStatus::Done);
});

test('o lock é solto no fim, mesmo quando o job falha', function () {
    $export = $this->user->exports()->create(['month' => '2026-11']);
    $this->mock(\App\Actions\ExportExpensesCsv::class)->shouldReceive('handle')->andThrow(new RuntimeException('quebrou'));

    expect(fn () => runExportJobWithLock($export))->toThrow(RuntimeException::class);

    // se o lock tivesse ficado preso, este get() devolveria false
    expect(Cache::lock("export:{$this->user->id}:2026-11", 10)->get())->toBeTrue();
});
