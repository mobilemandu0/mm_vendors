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

        Process::run("cd {$path} && git fetch origin");
        Process::run("cd {$path} && git reset --hard origin/main");
        Process::run("cd {$path} && git clean -fd");

        Process::run("cd {$path} && php artisan optimize:clear");

        Process::run("cd {$path} && composer install --no-dev --optimize-autoloader")->throw();
    }
}