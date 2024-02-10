<?php

declare(strict_types=1);

namespace App\Command;

use App\Enums\BanksEnum;
use App\Exceptions\ApplicationException;
use App\Service\BankCurrenciesParserStrategy;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:parse-bank',
    description: 'Create or update settlements',
    hidden: false,
    aliases: ['app:parse-bank']
)]
class ParseBank extends Command
{
    public function __construct(
        private BankCurrenciesParserStrategy $parserStrategy,
        private MailerInterface $mailer,
        private string $recipientEmail,
        private string $senderEmail,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addOption('bank', null, InputOption::VALUE_REQUIRED, 'Enter bank: privat or mono');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bank = $input->getOption('bank');

        if (!in_array($bank, array_column(BanksEnum::cases(), "name"), true)) {
            echo 'Unsupported bank';
        }

        $parser = $this->parserStrategy->getParser($bank);
        $currencies = $parser->parse();
        foreach ($currencies['data'] as $currency) {
            if ($parser->isCurrencyGreaterThanOld($currency)) {
                try {
                    $this->mailer->send((new Email())
                        ->from($this->senderEmail)
                        ->to($this->recipientEmail)
                        ->subject('Course of the ' . $parser->getCurrencyName($currency) . ' now lower in the ' . $bank . 'bank')
                        ->text('Test'));
                } catch (ApplicationException $exception) {
                    echo $exception->getMessage();
                }
            }
        }

        return 0;
    }
}