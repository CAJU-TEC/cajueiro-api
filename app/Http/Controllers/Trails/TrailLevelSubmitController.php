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

        $anterior = $this->progress->levelCertificateUri($level, $collaborator);

        try {
            $novo = $this->storeCertificate($request->input('certificate'));
        } catch (DomainException $e) {
            return response()->json($e->getMessage(), 422);
        }

        try {
            $stage = $this->progress->submitLevel($level, $collaborator, Auth::id(), $novo);
        } catch (DomainException $e) {
            // O arquivo já está no disco, mas o envio não valeu: não deixa órfão.
            $this->removeCertificate($novo);

            return response()->json($e->getMessage(), 422);
        }

        // Reenvio com arquivo novo: o anterior não é mais referenciado por
        // ninguém e ficaria no disco para sempre. Apagado só depois de gravar,
        // para uma falha no meio não levar o certificado que ainda valia.
        if ($novo && $anterior !== $novo) {
            $this->removeCertificate($anterior);
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
        if (!$dataUri) {
            return null;
        }

        if (!Str::contains($dataUri, ';base64,')) {
            throw new DomainException('Não foi possível ler o arquivo do certificado.');
        }

        [$header, $content] = explode(';base64,', $dataUri, 2);
        $mime = Str::after($header, 'data:');
        $extension = self::EXTENSIONS[$mime] ?? null;

        // Recusar em voz alta: devolvendo null, o formato não suportado virava
        // "reenvio sem arquivo novo" e o certificado antigo ficava no lugar,
        // com resposta 200 — parecia que o reenvio não tinha substituído nada.
        if (!$extension) {
            throw new DomainException('Envie o certificado em PDF ou imagem (JPG, PNG ou WebP).');
        }

        $name = Str::ulid() . '.' . $extension;
        $directory = storage_path('app/public/certificates');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents("{$directory}/{$name}", base64_decode($content));

        return $name;
    }

    private function removeCertificate(?string $name): void
    {
        if (!$name) {
            return;
        }

        $path = storage_path("app/public/certificates/{$name}");

        if (is_file($path)) {
            unlink($path);
        }
    }
}
