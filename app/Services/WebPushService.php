<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected function ensureOpenSslEnv(): void
    {
        if (!getenv('OPENSSL_CONF')) {
            $phpDir = dirname(PHP_BINARY);
            $candidate = $phpDir . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';
            if (is_file($candidate)) {
                putenv('OPENSSL_CONF=' . $candidate);
            }
        }

        if (!getenv('RANDFILE')) {
            $randFile = storage_path('app' . DIRECTORY_SEPARATOR . 'openssl_rand.rnd');
            if (!is_file($randFile)) {
                @file_put_contents($randFile, '');
            }
            putenv('RANDFILE=' . $randFile);
        }
    }

    protected function makeWebPush(): WebPush
    {
        $this->ensureOpenSslEnv();

        $publicKey = config('services.webpush.vapid_public_key');
        $privateKey = config('services.webpush.vapid_private_key');
        $subject = config('services.webpush.vapid_subject', config('app.url'));

        $auth = [
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        return new WebPush($auth);
    }

    public function canSend(): bool
    {
        return (bool) (config('services.webpush.vapid_public_key') && config('services.webpush.vapid_private_key'));
    }

    /**
     * @param array{title?:string,body?:string,url?:string,tag?:string} $payload
     */
    public function sendToSubscription(PushSubscription $sub, array $payload): array
    {
        $webPush = $this->makeWebPush();

        $subscription = Subscription::create([
            'endpoint' => $sub->endpoint,
            'publicKey' => $sub->p256dh,
            'authToken' => $sub->auth,
        ]);

        $report = $webPush->sendOneNotification($subscription, json_encode($payload, JSON_UNESCAPED_UNICODE));

        return [
            'is_success' => $report->isSuccess(),
            'status_code' => $report->getResponse()?->getStatusCode(),
            'reason' => $report->getReason(),
        ];
    }
}


