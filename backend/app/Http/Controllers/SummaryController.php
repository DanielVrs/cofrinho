<?php

namespace App\Http\Controllers;

use App\Actions\SummarizeMonth;
use App\Http\Requests\SummaryRequest;
use App\Http\Resources\SummaryResource;

class SummaryController extends Controller
{
    public function __invoke(SummaryRequest $request, SummarizeMonth $summarizeMonth)
    {
        return new SummaryResource($summarizeMonth->handle($request->user(), $request->validated('month')));
    }
}
