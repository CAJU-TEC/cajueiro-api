<?php
namespace App\Console\Commands;


use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ServeLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serve:local {--port=8000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia o servidor Laravel em 0.0.0.0 e mostra os IPs locais disponíveis';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $port = $this->option('port');

        $this->info("▶️  Iniciando em http://0.0.0.0:{$port}");

        $ips = $this->detectLocalIps();

        if (empty($ips)) {
            $ips = ['127.0.0.1'];
        }

        $this->line('🌐 Acesse em:');
        foreach ($ips as $ip) {
            $this->line("   ➜ http://{$ip}:{$port}");
        }

        $this->newLine();

        $process = new Process([
            PHP_BINARY,
            'artisan',
            'serve',
            '--host=0.0.0.0',
            "--port={$port}",
        ]);

        // Desativa o timeout para permitir que o servidor rode indefinidamente
        $process->setTimeout(null);

        // Se estiver em um terminal interativo, use TTY para melhor experiência
        if (function_exists('posix_isatty') && posix_isatty(STDIN)) {
            $process->setTty(true);
        }

        return $process->run();
    }

    /**
     * Detecta IPs locais disponíveis para acesso na rede.
     *
     * @return array
     */
    protected function detectLocalIps(): array
    {
        if (stripos(PHP_OS_FAMILY, 'Windows') !== false) {
            $output = shell_exec('ipconfig');
            preg_match_all('/IPv4\s+Address[^\:]*:\s*([0-9.]+)/i', $output ?? '', $matches);
            return array_values(array_unique(array_filter($matches[1] ?? [], fn ($ip) => $ip !== '127.0.0.1')));
        }

        $output = trim(shell_exec('hostname -I') ?? '');

        if ($output === '' && stripos(PHP_OS_FAMILY, 'Darwin') !== false) {
            $output = trim(shell_exec('ipconfig getifaddr en0') ?? '');
            $output .= ' ' . trim(shell_exec('ipconfig getifaddr en1') ?? '');
        }

        $ips = preg_split('/\s+/', $output);
        return array_values(array_unique(array_filter($ips, function ($ip) {
            return $ip && $ip !== '127.0.0.1' && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        })));
    }
}
