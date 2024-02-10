<?php

declare(strict_types=1);

namespace App\Service\CurrenciesParsers;

use App\Models\MonoCurrencyModel;
use App\Models\PrivatCurrencyModel;

interface CurrenciesParserInterface
{
    public function supports(string $bank): bool;
    public function parse(): array;
    public function isCurrencyGreaterThanOld(MonoCurrencyModel|PrivatCurrencyModel $currencyModel): bool;
    public function getCurrencyName(MonoCurrencyModel|PrivatCurrencyModel $currencyModel): string;
}