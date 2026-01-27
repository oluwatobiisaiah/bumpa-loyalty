<?php

namespace App\Contracts;

use App\Models\User;

namespace App\Services\Payment\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class PaystackProvider implements PaymentProviderInterface
{
    protected string $secretKey;
    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function getName(): string
    {
        return 'paystack';
    }

    public function transferCashback(User $user, float $amount, string $currency, array $metadata): array
    {
        try {
            // Convert to kobo (smallest currency unit)
            $amountInKobo = (int)($amount * 100);

            // Create transfer recipient first
            $recipient = $this->createRecipient($user, $currency);

            if (!$recipient['success']) {
                return $recipient;
            }

            // Initiate transfer
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->secretKey}",
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/transfer", [
                'source' => 'balance',
                'amount' => $amountInKobo,
                'recipient' => $recipient['recipient_code'],
                'reason' => $metadata['description'] ?? 'Loyalty cashback',
                'reference' => $this->generateReference($metadata['transaction_id']),
                'currency' => $currency,
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'reference' => $data['data']['reference'],
                    'transfer_code' => $data['data']['transfer_code'],
                    'response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Transfer failed',
                'response' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('Paystack transfer error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function createRecipient(User $user, string $currency): array
    {
        // In production, this would use user's bank details
        // For demo, using mock data
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->secretKey}",
            ])->post("{$this->baseUrl}/transferrecipient", [
                'type' => 'nuban',
                'name' => $user->name,
                'account_number' => $user->bank_account ?? '0123456789',
                'bank_code' => $user->bank_code ?? '058', // GTBank code
                'currency' => $currency,
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status']) {
                return [
                    'success' => true,
                    'recipient_code' => $data['data']['recipient_code'],
                ];
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to create recipient',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function generateReference(int $transactionId): string
    {
        return 'CASHBACK_' . $transactionId . '_' . time();
    }
}


