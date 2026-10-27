<?php

namespace App\Http\Controllers\Dumps;

use App\Actions\Dumps\ValidarDestinoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Dump\DumpStoreRequest;
use App\Jobs\RefreshDatabaseJob;
use App\Models\Corporate;
use App\Models\Dump;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use RuntimeException;

class DumpsStoreController extends Controller
{
    public function __construct(private Dump $dumps, private Corporate $organizacoes, private ValidarDestinoAction $validarDestino) {}

    public function __invoke(DumpStoreRequest $request)
    {
        $organizacao = $this->organizacoes->findOrFail($request->corporate_id);

        try {
            ($this->validarDestino)($organizacao, $request->destino);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Um por vez para o mesma organização e destino. Ignora registros mais velhos que o
        // timeout do job: se o worker morreu, o registro ficou preso e não pode bloquear para sempre.
        $emAndamento = $this->dumps
            ->where('corporate_id', $organizacao->id)
            ->where('destino', $request->destino)
            ->whereIn('status', [Dump::PENDENTE, Dump::EXECUTANDO])
            ->where('created_at', '>=', now()->subSeconds(config('db_refresh.timeout') + 600))
            ->exists();

        if ($emAndamento) {
            return response()->json(['message' => 'Já existe uma atualização em andamento para este destino.'], 409);
        }

        $dump = $this->dumps->create([
            'corporate_id' => $organizacao->id,
            'user_id' => Auth::id(),
            'destino' => $request->destino,
            'status' => Dump::PENDENTE,
        ]);

        RefreshDatabaseJob::dispatch($dump);

        return response()->json($dump, 201);
    }
}
