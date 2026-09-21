<?php

namespace App\Console\Commands;

use App\Services\ClinicalAiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Diagnose the Clinical AI Advisor configuration and run direct probe calls
 * that surface the exact Anthropic API status/message.
 *
 *   php artisan ai:check
 */
class CheckClinicalAi extends Command
{
    protected $signature = 'ai:check {--prompt=Reply with the single word: OK}';
    protected $description = 'Check the Clinical AI Advisor configuration and run live probe calls';

    public function handle(ClinicalAiService $ai): int
    {
        $key = (string) config('services.anthropic.api_key');
        $model = (string) config('services.anthropic.model');
        $base = rtrim((string) config('services.anthropic.base_url'), '/');
        $version = (string) config('services.anthropic.version', '2023-06-01');

        $this->line('');
        $this->info('Clinical AI Advisor — configuration');
        $this->table(['Setting', 'Value'], [
            ['API key set', $key !== '' ? 'yes ('.substr($key, 0, 7).'…, '.strlen($key).' chars)' : 'NO'],
            ['Model', $model],
            ['Effort', (string) config('services.anthropic.effort')],
            ['Web search', config('services.anthropic.web_search') ? 'on' : 'off'],
            ['Max tokens', (string) config('services.anthropic.max_tokens')],
            ['Timeout (s)', (string) config('services.anthropic.timeout')],
            ['Base URL', $base],
        ]);

        if ($key === '') {
            $this->error('No ANTHROPIC_API_KEY loaded. Set it in .env, then run:  php artisan config:clear');
            return self::FAILURE;
        }

        $headers = [
            'x-api-key' => $key,
            'anthropic-version' => $version,
            'content-type' => 'application/json',
        ];

        // Probe A — minimal call: isolates authentication + model access.
        $this->line('');
        $this->info('Probe A — basic API call (auth + model access)…');
        $a = $this->probe($headers, $base, [
            'model' => $model,
            'max_tokens' => 32,
            'messages' => [['role' => 'user', 'content' => 'Reply with the single word: OK']],
        ]);

        // Probe B — with adaptive thinking + effort (what the advisor uses).
        $this->line('');
        $this->info('Probe B — with extended thinking + effort…');
        $b = $this->probe($headers, $base, [
            'model' => $model,
            'max_tokens' => 2000,
            'output_config' => ['effort' => (string) config('services.anthropic.effort', 'high')],
            'thinking' => ['type' => 'adaptive'],
            'messages' => [['role' => 'user', 'content' => 'Reply with the single word: OK']],
        ]);

        // Probe C — with the web search tool (only if enabled).
        $c = null;
        if (filter_var(config('services.anthropic.web_search', true), FILTER_VALIDATE_BOOLEAN)) {
            $this->line('');
            $this->info('Probe C — with web search tool…');
            $c = $this->probe($headers, $base, [
                'model' => $model,
                'max_tokens' => 1024,
                'tools' => [['type' => 'web_search_20260209', 'name' => 'web_search', 'max_uses' => 1]],
                'messages' => [['role' => 'user', 'content' => 'Reply with the single word: OK']],
            ]);
        }

        // Full service call (mirrors the real advisor path, incl. fallbacks).
        $this->line('');
        $this->info('Full advisor call (service path)…');
        $result = $ai->respond($this->option('prompt'));
        $this->line('Source: '.strtoupper($result['source'] ?? '?'));

        $this->line('');
        if ($a['ok'] && ($result['source'] ?? '') === 'live') {
            $this->info('✔ Working.'.(($c && !$c['ok']) ? ' (Web search unavailable — advisor runs without live citations.)' : ''));
            return self::SUCCESS;
        }

        $this->error('✘ The advisor is not fully live. Read the probe results above:');
        $this->line(' • Probe A failed  → key/model/network problem (see status below).');
        $this->line(' • A ok, B failed  → thinking/effort not supported by this model.');
        $this->line(' • A/B ok, C failed → web search not enabled on the account (set ANTHROPIC_WEB_SEARCH=false to silence).');
        return self::FAILURE;
    }

    /** Make one request and print HTTP status + a trimmed message. */
    private function probe(array $headers, string $base, array $payload): array
    {
        try {
            $res = Http::withHeaders($headers)->timeout(60)->post($base.'/messages', $payload);
            $status = $res->status();
            if ($res->successful()) {
                $this->line("  → HTTP {$status} OK");
                return ['ok' => true, 'status' => $status];
            }
            $err = $res->json('error.message') ?? mb_substr($res->body(), 0, 300);
            $type = $res->json('error.type') ?? '';
            $this->error("  → HTTP {$status} {$type}: {$err}");
            return ['ok' => false, 'status' => $status];
        } catch (\Throwable $e) {
            $this->error('  → connection error: '.$e->getMessage());
            return ['ok' => false, 'status' => 0];
        }
    }
}
