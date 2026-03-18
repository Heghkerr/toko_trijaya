<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string',
            'subscription.keys' => 'required|array',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
            'user_agent' => 'nullable|string|max:1024',
        ]);

        $subscription = $data['subscription'];

        $model = PushSubscription::updateOrCreate(
            ['endpoint' => $subscription['endpoint']],
            [
                'user_id' => $request->user()->id,
                'p256dh' => $subscription['keys']['p256dh'],
                'auth' => $subscription['keys']['auth'],
                'user_agent' => $data['user_agent'] ?? $request->userAgent(),
            ]
        );

        return response()->json([
            'ok' => true,
            'id' => $model->id,
        ]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}


