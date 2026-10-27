<?php

namespace App\Http\Controllers\Dumps;

use App\Http\Controllers\Controller;
use App\Models\Dump;

class DumpsCancelController extends Controller
{
    public function __construct(private Dump $dumps) {}

    /**
     * Marca a atualização como falha. Se a cópia já estiver rodando, o worker percebe em até 5 s
     * e derruba o processo (RodarComandoAction); o banco de destino fica incompleto.
     */
    public function __invoke($id)
    {
        $dump = $this->dumps->findOrFail($id);

        if (!in_array($dump->status, [Dump::PENDENTE, Dump::EXECUTANDO], true)) {
            return response()->json(['message' => 'Esta atualização já terminou.'], 409);
        }

        $dump->update([
            'status' => Dump::FALHA,
            'progresso' => null,
            'finished_at' => now(),
            'erro' => 'Cancelada pelo usuário.',
        ]);

        return response()->json($dump, 200);
    }
}
