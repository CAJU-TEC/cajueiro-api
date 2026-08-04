<?php

namespace App\Http\Controllers\Trails;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trail\TrailLevelSubmitRequest;
use App\Models\Collaborator;
use App\Models\TrailLevel;
use App\Services\Trails\TrailProgressService;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TrailLevelSubmitController extends Controller
{
    /**
     * Anexo do certificado: aceita imagem e PDF, que é como os certificados de
     * curso costumam vir.
     */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    public function __construct(private TrailProgressService $progress) {}

    public function __invoke(TrailLevelSubmitRequest $request, $levelId)
    {
        $level = TrailLevel::findOrFail($levelId);
        $collaborator = Collaborator::findOrFail($request->input('collaborator_id'));

        // A permissão da rota é trails.mine, que todo mundo tem. O que amarra é
        // isto: ou é o próprio colaborador enviando, ou é alguém que avança
        // trilha. Sem esta checagem um liderado enviaria nível na trilha de
        // outro, e não existe permissão granular que resolva isso.
        $eu = Auth::user()?->collaborator;
        $souODono = $eu && (string) $eu->id === (string) $collaborator->id;

        if (!$souODono && !Auth::user()?->can('trails.advance')) {
            return response()->json('Você só pode enviar níveis da sua própria trilha.', 403);
        }

        try {
            $stage = $this->progress->submitLevel(
                $level,
                $collaborator,
                Auth::id(),
                $this->storeCertificate($request->input('certificate'))
            );
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }

        return response()->json(
            $this->progress->progressFor($stage->trail, $collaborator),
            200
        );
    }

    /**
     * Grava o certificado e devolve o nome do arquivo.
     *
     * O arquivo chega como data URI em JSON, o mesmo formato que os anexos de
     * protocolo já usam (TicketsStoreController) — evita multipart e mantém um
     * padrão só no projeto.
     */
    private function storeCertificate(?string $dataUri): ?string
    {
        if (!$dataUri || !Str::contains($dataUri, ';base64,')) {
            return null;
        }

        [$header, $content] = explode(';base64,', $dataUri, 2);
        $mime = Str::after($header, 'data:');
        $extension = self::EXTENSIONS[$mime] ?? null;

        if (!$extension) {
            return null;
        }

        $name = Str::ulid() . '.' . $extension;
        $directory = storage_path('app/public/certificates');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents("{$directory}/{$name}", base64_decode($content));

        return $name;
    }
}
