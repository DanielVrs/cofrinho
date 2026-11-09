<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExportRequest;
use App\Http\Resources\ExportResource;
use App\Jobs\ExportExpensesCsv;
use App\Models\Export;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    // registra o pedido, manda o job pra fila e responde 202 sem esperar o CSV
    public function store(StoreExportRequest $request)
    {
        $export = $request->user()->exports()->create(['month' => $request->validated('month')]);

        ExportExpensesCsv::dispatch($export);

        return (new ExportResource($export))->response()->setStatusCode(202);
    }

    // o React consulta aqui até o status deixar de ser pending
    public function show(Export $export)
    {
        Gate::authorize('view', $export);

        return new ExportResource($export);
    }

    public function download(Export $export)
    {
        Gate::authorize('view', $export);

        abort_unless($export->isDone(), 404, 'A exportação ainda não está pronta.');

        return Storage::disk('local')->download($export->path, "lancamentos-{$export->month}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
