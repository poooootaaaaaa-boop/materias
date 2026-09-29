<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use UnexpectedValueException;

class PaymentController extends Controller
{
    private const PRODUCTS = [
        'points_20' => ['type' => 'points', 'name' => '20 puntos', 'amount' => 1900, 'quantity' => 20],
        'points_50' => ['type' => 'points', 'name' => '50 puntos', 'amount' => 4900, 'quantity' => 50],
        'points_100' => ['type' => 'points', 'name' => '100 puntos', 'amount' => 8900, 'quantity' => 100],
        'accessory_amarillo' => ['type' => 'accessory', 'name' => 'Ropa amarilla', 'amount' => 3900],
        'accessory_gorra' => ['type' => 'accessory', 'name' => 'Gorra', 'amount' => 2900],
        'accessory_sol' => ['type' => 'accessory', 'name' => 'Lentes de sol', 'amount' => 2500],
        'accessory_corazon' => ['type' => 'accessory', 'name' => 'Collar de corazón', 'amount' => 2500],
        'subject_ruso' => ['type' => 'subject', 'name' => 'Materia Ruso', 'amount' => 9900],
        'subject_musica' => ['type' => 'subject', 'name' => 'Materia Música', 'amount' => 12900],
    ];

    public function config(): JsonResponse
    {
        return response()->json(['publishable_key' => config('services.stripe.key')]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $data = $request->validate(['product_key' => ['required', 'string']]);
        $product = self::PRODUCTS[$data['product_key']] ?? null;
        abort_unless($product, 422, 'Producto no válido.');

        $purchase = Purchase::create([
            'user_id' => $request->user()?->id,
            'product_key' => $data['product_key'],
            'product_type' => $product['type'],
            'amount' => $product['amount'],
            'currency' => 'mxn',
        ]);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'mxn',
                    'product_data' => ['name' => $product['name']],
                    'unit_amount' => $product['amount'],
                ],
                'quantity' => 1,
            ]],
            'success_url' => rtrim(config('app.frontend_url'), '/') . '/?payment=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => rtrim(config('app.frontend_url'), '/') . '/?payment=cancelled',
            'metadata' => [
                'purchase_id' => (string) $purchase->id,
                'user_id' => (string) ($request->user()?->id ?? ''),
            ],
        ]);

        $purchase->update(['stripe_session_id' => $session->id]);

        return response()->json(['url' => $session->url]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Stripe webhook rechazado: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook inválido'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            /** @var Purchase|null $purchase */
            $purchase = Purchase::where('stripe_session_id', $session->id)->first();

            if ($purchase && $purchase->status !== 'paid') {
                $purchase->update([
                    'status' => 'paid',
                    'fulfilled_at' => now(),
                ]);

                $this->fulfill($purchase);
            }
        }

        return response()->json(['received' => true]);
    }

    /**
     * Entrega lo comprado: acredita puntos, desbloquea accesorio o materia especial.
     * Ajusta esto a la forma real de tus modelos (User, Accessory, Subject, etc).
     */
    private function fulfill(Purchase $purchase): void
    {
        if (!$purchase->user_id) {
            return; // compra de invitado sin cuenta; decide cómo manejarlo si aplica
        }

        $user = $purchase->user()->first();
        if (!$user) {
            return;
        }

        $product = self::PRODUCTS[$purchase->product_key] ?? null;
        if (!$product) {
            return;
        }

        match ($purchase->product_type) {
            'points' => $user->increment('game_points', $product['quantity'] ?? 0),
            'accessory' => $user->accessories()->syncWithoutDetaching([$purchase->product_key]),
            'subject' => $user->specialSubjects()->syncWithoutDetaching([$purchase->product_key]),
            default => null,
        };
    }
}