<?php

namespace App\Contracts;

use App\Models\User;


namespace App\Services\Payment\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\User;


class MockPaymentProvider implements PaymentProviderInterface
{
    public function getName(): string
    {
        return 'mock';
    }

    public function transferCashback(User $user, float $amount, string $currency, array $metadata): array
    {
        // Simulate processing delay
        usleep(500000); // 0.5 seconds

        // Simulate 100% success rate for testing
        $isSuccess = true;

        if ($isSuccess) {
            return [
                'success' => true,
                'reference' => 'MOCK_' . uniqid(),
                'response' => [
                    'status' => true,
                    'message' => 'Mock transfer successful',
                    'data' => [
                        'amount' => $amount,
                        'currency' => $currency,
                        'recipient' => $user->email,
                        'timestamp' => now()->toIso8601String(),
                    ],
                ],
            ];
        }

        return [
            'success' => false,
            'error' => 'Mock transfer failed - simulated failure',
            'response' => [
                'status' => false,
                'message' => 'Insufficient funds in test account',
            ],
        ];
    }
}
