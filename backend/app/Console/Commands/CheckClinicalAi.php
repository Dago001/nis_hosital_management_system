<?php

namespace App\Console\Commands;

use App\Services\ClinicalAiService;
use Illuminate\Console\Command;

/**
 * Diagnose the Clinical AI Advisor configuration and (optionally) do a live ping.
 *
 *   php artisan ai:check
 */
class CheckClinicalAi extends Command
{
    protected $signature = 'ai:check {--prompt=Reply with the single word: OK}';
    protected $description = 'Check the Clinical AI Advisor configuration and run a live test call';

    public function handle(ClinicalAiService $ai): int
    {
        $key = (string) config('services.anthropic.api_key');
        $this->line('');
        $this->info('Clinical AI Advisor — configuration');
        $this->table(['Setting', 'Value'], [
            ['API key set', $key !== '' ? 'yes ('.substr($key, 0, 7).'…, '.strlen($key).' chars)' : 'NO'],
            ['Model', (string) config('services.anthropic.model')],
            ['Effort', (string) config('services.anthropic.effort')],
            ['Web search', config('services.anthropic.web_search') ? 'on' : 'off'],
            ['Max tokens', (string) config('services.anthropic.max_tokens')],
            ['Timeout (s)', (string) config('services.anthropic.timeout')],
            ['Base URL', (string) config('services.anthropic.base_url')],
        ]);

        if ($key === '') {
            $this->error('No ANTHROPIC_API_KEY loaded. Set it in .env, then run:  php artisan config:clear');
            $this->line('(If you ran `php artisan config:cache`, the key is cached — clear it and try again.)');
            return self::FAILURE;
        }

        $this->line('');
        $this->info('Running a live test call…');
        $result = $ai->respond($this->option('prompt'));

        if (($result['source'] ?? '') === 'live') {
            $this->info('✔ LIVE — the advisor reached Claude successfully.');
            $this->line('Reply preview: '.mb_substr(trim($result['reply']), 0, 200));
            return self::SUCCESS;
        }

        $this->error('✘ Fell back to OFFLINE — the live call failed.');
        $this->line('Check storage/logs/laravel.log for "Clinical AI live call failed" for the API status/body.');
        $this->line('Common causes: wrong/blocked key, no network egress to api.anthropic.com, or web search not enabled on the account.');
        return self::FAILURE;
    }
}
