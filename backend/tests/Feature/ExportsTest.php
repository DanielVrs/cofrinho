<?php

use App\Enums\ExportStatus;
use App\Jobs\ExportExpensesCsv;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Storage::fake('local');
});

// roda o job do jeito que o worker faria, sem fila
function runExportJob($export): void
{
    (new ExportExpensesCsv($export))->handle(app(\App\Actions\ExportExpensesCsv::class));
}

test('pedir uma exportação responde 202 na hora e deixa o job na fila', function () {
    Queue::fake();

    $this->postJson('/api/exports', ['month' => '2026-11'])
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.download_url', null);

    $this->assertDatabaseHas('exports', ['user_id' => $this->user->id, 'month' => '2026-11', 'status' => 'pending']);
    Queue::assertPushed(ExportExpensesCsv::class, 1);
});

test('o worker gera o CSV, marca como pronta e libera o download', function () {
    $category = Category::factory()->for($this->user)->create(['name' => 'Mercado']);
    Expense::factory()->create(['category_id' => $category->id, 'description' => 'Feira', 'amount_cents' => 1250, 'spent_on' => '2026-11-05']);
    Expense::factory()->create(['category_id' => $category->id, 'description' => 'De outubro', 'spent_on' => '2026-10-05']);
    $export = $this->user->exports()->create(['month' => '2026-11']);

    runExportJob($export);

    $export->refresh();
    expect($export->status)->toBe(ExportStatus::Done);
    Storage::disk('local')->assertExists($export->path);

    $this->getJson("/api/exports/{$export->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'done')
        ->assertJsonPath('data.download_url', route('exports.download', $export));

    $response = $this->get("/api/exports/{$export->id}/download");
    $response->assertOk()->assertDownload('lancamentos-2026-11.csv');

    expect($response->streamedContent())
        ->toContain('05/11/2026;Feira;Mercado;12,50')
        ->not->toContain('De outubro');
});

test('download antes de ficar pronta responde 404', function () {
    $export = $this->user->exports()->create(['month' => '2026-11']);

    $this->getJson("/api/exports/{$export->id}/download")->assertNotFound();
});

test('quando todas as tentativas falham, a exportação fica como failed', function () {
    $export = $this->user->exports()->create(['month' => '2026-11']);

    (new ExportExpensesCsv($export))->failed(new RuntimeException('disco cheio'));

    expect($export->fresh()->status)->toBe(ExportStatus::Failed)
        ->and($export->fresh()->error)->toBe('disco cheio');

    $this->getJson("/api/exports/{$export->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'failed')
        ->assertJsonPath('data.error', 'disco cheio');
});

test('exportação exige o mês', function () {
    $this->postJson('/api/exports', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('month');
});

test('não vê nem baixa a exportação de outro usuário', function () {
    $export = User::factory()->create()->exports()->create(['month' => '2026-11']);

    $this->getJson("/api/exports/{$export->id}")->assertForbidden();
    $this->getJson("/api/exports/{$export->id}/download")->assertForbidden();
});
