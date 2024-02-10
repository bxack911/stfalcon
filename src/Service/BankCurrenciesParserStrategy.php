<?php

declare(strict_types=1);

namespace App\Service;

use App\Exceptions\ApplicationException;
use App\Service\CurrenciesParsers\CurrenciesParserInterface;

class BankCurrenciesParserStrategy
{
    /** @var CurrenciesParserInterface[] */
    private array $strategies;

    public function addBankCurrenciesParserStrategy(CurrenciesParserInterface $currenciesParser): void
    {
        $this->strategies[] = $currenciesParser;
    }

    public function getParser(string $bank): CurrenciesParserInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($bank)) {
                return $strategy;
            }
        }

        throw new ApplicationException('Unsupported bank ' . $bank);
    }
}