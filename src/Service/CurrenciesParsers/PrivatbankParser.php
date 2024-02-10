<?php

declare(strict_types=1);

namespace App\Service\CurrenciesParsers;

use App\Enums\BanksEnum;
use App\Models\MonoCurrencyModel;
use App\Models\PrivatCurrencyModel;
use App\Service\Clients\BankClient;
use App\Service\CurrenciesService;
use Symfony\Component\Serializer\Serializer;

class PrivatbankParser implements CurrenciesParserInterface
{
    private BankClient $client;
    private Serializer $serializer;
    private CurrenciesService $currenciesService;

    public function __construct(BankClient $client, Serializer $serializer, CurrenciesService $currenciesService) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->currenciesService = $currenciesService;
    }

    public function supports(string $bank): bool
    {
        return $bank === BanksEnum::privat->name;
    }

    public function parse(): array
    {
        $currencies = $this->client->get('https://api.privatbank.ua/p24api/pubinfo?exchange&coursid=5');
        $respose = $currencies->getBody()->getContents();

        return [
            'data' => $this->serializer->deserialize($respose, PrivatCurrencyModel::class . '[]', 'json')
        ];
    }

    public function isCurrencyGreaterThanOld(MonoCurrencyModel|PrivatCurrencyModel $currencyModel): bool
    {
        if (!$currencyModel->buy) {
            return false;
        }

        return $this->currenciesService->isCurrencyGreaterThanOld(
            $currencyModel->ccy,
            (float) $currencyModel->buy,
            BanksEnum::privat->name
        );
    }

    public function getCurrencyName(MonoCurrencyModel|PrivatCurrencyModel $currencyModel): string
    {
        return $currencyModel->ccy;
    }
}