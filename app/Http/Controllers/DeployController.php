<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class DeployController extends Controller
{
    public function handle(Request $request)
    {
        $this->verifySignature($request);

        $this->runDeploy();

        Log::info('Secure deploy executed successfully');

        return response()->json(['success' => true]);
    }

    private function verifySignature(Request $request): void
    {
        $secret = config('app.deploy_token'); // cleaner than env()
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        if (!$signature || !hash_equals($expected, $signature)) {
            Log::warning('Deploy blocked - Invalid signature');
            abort(403, 'Unauthorized');
        }
    }

    private function runDeploy(): void
    {
        $path = '/home/mobilemandu/mm_vendors';
        $composerHome = '/home/mobilemandu/.composer';

        // Ensure composer home exists
        if (!is_dir($composerHome)) {
            mkdir($composerHome, 0755, true);
        }

        // Git commands
        Process::run("cd {$path} && git fetch origin")->throw();
        Process::run("cd {$path} && git reset --hard origin/main")->throw();
        Process::run("cd {$path} && git clean -fd")->throw();

        // Clear caches
        Process::run("cd {$path} && php artisan optimize:clear")->throw();

        // Composer install with COMPOSER_HOME set
        Process::run("cd {$path} && COMPOSER_HOME={$composerHome} composer install --no-dev --optimize-autoloader")->throw();
    }
}
