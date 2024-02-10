<?php

declare(strict_types=1);

namespace App\Service\CurrenciesParsers;

use App\Enums\BanksEnum;
use App\Models\MonoCurrencyModel;
use App\Models\PrivatCurrencyModel;
use App\Service\Clients\BankClient;
use App\Service\CurrenciesService;
use Symfony\Component\Serializer\Serializer;

class MonobankParser implements CurrenciesParserInterface {
    private BankClient $bankClient;
    private Serializer $serializer;
    private CurrenciesService $currenciesService;

    public function __construct(BankClient $bankClient, Serializer $serializer, CurrenciesService $currenciesService)
    {
        $this->bankClient = $bankClient;
        $this->serializer = $serializer;
        $this->currenciesService = $currenciesService;
    }

    public function supports(string $bank): bool {
        return $bank === BanksEnum::mono->name;
    }

    public function parse(): array {
        $currencies = $this->bankClient->get('https://api.monobank.ua/bank/currency');

        $respose = $currencies->getBody()->getContents();

        return [
            'data' => $this->serializer->deserialize($respose, MonoCurrencyModel::class . '[]', 'json')
        ];
    }

    public function isCurrencyGreaterThanOld(MonoCurrencyModel|PrivatCurrencyModel $currencyModel): bool
    {
        if (!$currencyModel->rateBuy || in_array(840, [$currencyModel->currencyCodeB, $currencyModel->currencyCodeA], true)) {
            return false;
        }

        $currencyCode = $currencyModel->currencyCodeA === 840 ? $currencyModel->currencyCodeA : $currencyModel->currencyCodeB;

        return $this->currenciesService->isCurrencyGreaterThanOld(
            $this->getCurrencyNameByCode($currencyCode),
            (float) $currencyModel->rateBuy,
            BanksEnum::privat->name
        );
    }

    public function getCurrencyName(MonoCurrencyModel|PrivatCurrencyModel $currencyModel): string
    {
        return $this->getCurrencyNameByCode($currencyModel->currencyCodeA === 840 ? $currencyModel->currencyCodeA : $currencyModel->currencyCodeB);
    }

    private function getCurrencyNameByCode(int $currencyCode): string
    {
        $currencyNames = [
            980 => 'USD',
            978 => 'EUR'
        ];

        return $currencyNames[$currencyCode];
    }
}