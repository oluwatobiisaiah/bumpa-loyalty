<?php
namespace App\Contracts;
use App\Models\User;

interface PaymentProviderInterface
{
    public function getName(): string;
    public function transferCashback(User $user, float $amount, string $currency, array $metadata): array;
}
