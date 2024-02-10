<?php

declare(strict_types=1);

namespace App\Service;

use App\Enums\BanksEnum;
use App\Enums\ParserEnum;
use App\Service\Clients\BankClient;
use Predis\Client;
use Symfony\Component\Serializer\Serializer;

class CurrenciesService
{
    private Client $redis;

    public function __construct(Client $redis) {
        $this->redis = $redis;
    }

    public function isCurrencyGreaterThanOld(string $currencyName, float $currencyRate, string $bankName): bool
    {
        $oldCurrencyRate = (float) $this->redis->get($bankName . '_' . $currencyName);
        $this->redis->set($bankName . '_' . $currencyName, $currencyRate);

        if (!$oldCurrencyRate) {
            return false;
        }

        $threshhold = $oldCurrencyRate - $currencyRate;

        return $threshhold > ParserEnum::THRESHHOLD->value;
    }
}