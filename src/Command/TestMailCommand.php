<?php

namespace App\Command;

use App\Service\GmailApiMailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-mail',
    description: 'Send a test email to check mailer configuration',
)]
class TestMailCommand extends Command
{
    public function __construct(
        private GmailApiMailer $mailer,
        private string $mailFromAddress,
        private string $mailFromName
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Recipient email address')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailAddress = $input->getArgument('email');

        $email = (new Email())
            ->from($this->mailFromAddress)
            ->to($emailAddress)
            ->subject('Test Email from AgriC')
            ->text('This is a test email to verify the mailer configuration.');

        try {
            $this->mailer->sendEmail($email);
            $io->success('Test email sent successfully to ' . $emailAddress);
        } catch (\Throwable $e) {
            $io->error('Failed to send email: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
