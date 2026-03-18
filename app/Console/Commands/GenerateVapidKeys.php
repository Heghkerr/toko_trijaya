<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'push:vapid';
    protected $description = 'Generate VAPID keys for PWA Web Push';

    public function handle(): int
    {
        // Ensure OpenSSL can generate EC keys on Windows by providing config + randfile.
        $this->ensureOpenSslEnv();

        $keys = VAPID::createVapidKeys();

        $this->info('VAPID keys generated:');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('VAPID_SUBJECT=' . (config('app.url') ?: 'mailto:admin@example.com'));

        $this->newLine();
        $this->warn('Copy these into your .env, then deploy/restart.');

        return self::SUCCESS;
    }

    protected function ensureOpenSslEnv(): void
    {
        // If OPENSSL_CONF is not set, try PHP bundled config (Laragon / Windows builds often have this).
        if (!getenv('OPENSSL_CONF')) {
            $phpDir = dirname(PHP_BINARY);
            $candidate = $phpDir . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';
            if (is_file($candidate)) {
                putenv('OPENSSL_CONF=' . $candidate);
            }
        }

        // Ensure RANDFILE points to a writable file to avoid RAND_load_file errors.
        if (!getenv('RANDFILE')) {
            $randFile = storage_path('app' . DIRECTORY_SEPARATOR . 'openssl_rand.rnd');
            if (!is_file($randFile)) {
                @file_put_contents($randFile, '');
            }
            putenv('RANDFILE=' . $randFile);
        }
    }
}


