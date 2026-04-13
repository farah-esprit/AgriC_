<?php

namespace App\Service;

use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SmsVerificationService
{
    public function __construct(
        private TexterInterface  $texter,
        private RequestStack     $requestStack
    ) {}

    public function sendCode(string $phoneNumber): void
    {
        $code = (string) random_int(100000, 999999);
        $session = $this->requestStack->getSession();

        $session->set('sms_code',        $code);
        $session->set('sms_code_phone',   $phoneNumber);
        $session->set('sms_code_expiry',  time() + 300);

        $sms = new SmsMessage(
            $phoneNumber,
            'Votre code de vérification AgriConnect : ' . $code
        );

        $this->texter->send($sms);
    }

    public function verifyCode(string $phone, string $inputCode): bool
    {
        $session = $this->requestStack->getSession();
        $storedCode  = $session->get('sms_code');
        $storedPhone = $session->get('sms_code_phone');
        $expiry      = $session->get('sms_code_expiry');

        if (!$storedCode || !$expiry)     return false;
        if (time() > $expiry)            { $this->clearCode(); return false; }
        if ($storedPhone !== $phone)      return false;
        if ($storedCode  !== $inputCode)  return false;

        $this->clearCode();
        return true;
    }

    private function clearCode(): void
    {
        $session = $this->requestStack->getSession();
        $session->remove('sms_code');
        $session->remove('sms_code_phone');
        $session->remove('sms_code_expiry');
    }
}