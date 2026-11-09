<?php

namespace App\Http\Controllers;

use App\Actions\DepositToVault;
use App\Actions\WithdrawFromVault;
use App\Http\Requests\StoreVaultRequest;
use App\Http\Requests\VaultMovementRequest;
use App\Http\Resources\VaultResource;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VaultController extends Controller
{
    public function index()
    {
        return VaultResource::collection($this->withTotals(request()->user()->vaults())->get());
    }

    public function store(StoreVaultRequest $request)
    {
        $vault = $request->user()->vaults()->create($request->validated());

        return (new VaultResource($vault))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Vault $vault)
    {
        Gate::authorize('view', $vault);

        return new VaultResource($this->withTotals(Vault::whereKey($vault->id))->firstOrFail());
    }

    public function deposit(VaultMovementRequest $request, Vault $vault, DepositToVault $deposit)
    {
        return new VaultResource($deposit->handle($vault, $request->validated('amount_cents')));
    }

    public function withdraw(VaultMovementRequest $request, Vault $vault, WithdrawFromVault $withdraw)
    {
        return new VaultResource($withdraw->handle($vault, $request->validated('amount_cents')));
    }

    // somas de depósitos e saques na mesma consulta, sem N+1
    private function withTotals(Builder|\Illuminate\Database\Eloquent\Relations\HasMany $query)
    {
        return $query
            ->withSum(['movements as deposits_cents' => fn ($q) => $q->where('type', 'deposit')], 'amount_cents')
            ->withSum(['movements as withdrawals_cents' => fn ($q) => $q->where('type', 'withdraw')], 'amount_cents');
    }
}
