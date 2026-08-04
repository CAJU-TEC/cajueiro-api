<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use App\Models\TrailLevel;
use App\Services\Trails\TrailProgressService;
use Illuminate\Support\Facades\Auth;

/**
 * Entrega o certificado que o colaborador anexou no nível.
 *
 * Antes o front montava a URL pública `/storage/certificates/...`, que só existe
 * depois de `php artisan storage:link` e, pior, deixaria o arquivo de qualquer
 * colaborador aberto para quem descobrisse o nome. Aqui o download passa pelo
 * `auth:sanctum` como o certificado da etapa.
 */
class TrailLevelCertificateController extends Controller
{
    public function __construct(private TrailProgressService $progress) {}

    public function __invoke($levelId, $collaboratorId)
    {
        $level = TrailLevel::findOrFail($levelId);
        $collaborator = Collaborator::findOrFail($collaboratorId);

        // A rota aceita trails.mine, que todo mundo tem: ou é o dono do
        // certificado, ou é alguém que acompanha trilha (mesma lógica do envio).
        $eu = Auth::user()?->collaborator;
        $souODono = $eu && (string) $eu->id === (string) $collaborator->id;

        if (!$souODono && !Auth::user()?->can('trails.index')) {
            return response()->json('Você só pode abrir certificados da sua própria trilha.', 403);
        }

        $uri = $this->progress->levelCertificateUri($level, $collaborator);

        if (!$uri) {
            return response()->json('Nenhum certificado anexado neste nível.', 404);
        }

        $path = storage_path("app/public/certificates/{$uri}");

        if (!is_file($path)) {
            return response()->json('Arquivo do certificado não encontrado.', 404);
        }

        // file() manda inline com o content-type do arquivo: PDF e imagem abrem
        // no navegador, e o front ainda pode oferecer o download do blob.
        return response()->file($path);
    }
}
