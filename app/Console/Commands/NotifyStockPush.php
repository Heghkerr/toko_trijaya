<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NotifyStockPush extends Command
{
    protected $signature = 'stock:push-alert {--force : Kirim walau tidak ada perubahan}';
    protected $description = 'Send PWA push notification for understock/overstock products';

    public function handle(WebPushService $webPush): int
    {
        if (!$webPush->canSend()) {
            $this->warn('WebPush is not configured (missing VAPID keys).');
            return self::SUCCESS;
        }

        $under = Product::withGlobalStock()
            ->whereNotNull('min_stock')
            ->whereRaw('(SELECT COALESCE(SUM(stock * conversion_value), 0) FROM product_units WHERE product_units.product_id = products.id) <= products.min_stock')
            ->orderBy('current_global_stock', 'asc')
            ->limit(10)
            ->get(['id', 'name', 'color_id', 'min_stock']);

        $over = Product::withGlobalStock()
            ->whereNotNull('max_stock')
            ->whereRaw('(SELECT COALESCE(SUM(stock * conversion_value), 0) FROM product_units WHERE product_units.product_id = products.id) >= products.max_stock')
            ->orderBy('current_global_stock', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'color_id', 'max_stock']);

        if ($under->isEmpty() && $over->isEmpty()) {
            $this->info('No understock/overstock detected.');
            return self::SUCCESS;
        }

        $hashPayload = [
            'under' => $under->map(fn ($p) => [$p->id, (int) $p->current_global_stock])->values()->all(),
            'over' => $over->map(fn ($p) => [$p->id, (int) $p->current_global_stock])->values()->all(),
        ];
        $hash = md5(json_encode($hashPayload));

        $cacheKey = 'stock_push_last_hash';
        $lastHash = Cache::get($cacheKey);
        $force = (bool) $this->option('force');
        if (!$force && $lastHash === $hash) {
            $this->info('No changes since last notification.');
            return self::SUCCESS;
        }

        Cache::put($cacheKey, $hash, now()->addMinutes(30));

        $title = 'Peringatan Stok';
        $parts = [];
        if ($under->isNotEmpty()) {
            $parts[] = 'Understock: ' . $under->count();
        }
        if ($over->isNotEmpty()) {
            $parts[] = 'Overstock: ' . $over->count();
        }
        $body = implode(' | ', $parts) . "\n";

        if ($under->isNotEmpty()) {
            $top = $under->take(3)->map(fn ($p) => "{$p->name} ({$p->current_global_stock} pcs)")->implode(', ');
            $body .= "Top under: {$top}\n";
        }
        if ($over->isNotEmpty()) {
            $top = $over->take(3)->map(fn ($p) => "{$p->name} ({$p->current_global_stock} pcs)")->implode(', ');
            $body .= "Top over: {$top}\n";
        }

        $payload = [
            'title' => $title,
            'body' => trim($body),
            'url' => url('/inventories'),
            'tag' => 'stock-alert',
        ];

        $recipients = User::whereIn('role', ['owner', 'admin'])->pluck('id');
        $subs = PushSubscription::whereIn('user_id', $recipients)->get();

        if ($subs->isEmpty()) {
            $this->warn('No push subscriptions found.');
            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($subs as $sub) {
            $result = $webPush->sendToSubscription($sub, $payload);
            if ($result['is_success']) {
                $sent++;
                $sub->forceFill(['last_notified_at' => now()])->save();
            } else {
                $failed++;
                // remove invalid subscriptions
                if (in_array((int) $result['status_code'], [404, 410], true)) {
                    $sub->delete();
                }
            }
        }

        $this->info("Push sent: {$sent}, failed: {$failed}");

        return self::SUCCESS;
    }
}


