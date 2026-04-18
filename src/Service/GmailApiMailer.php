<?php

namespace App\Service;

use Google\Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Symfony\Component\Mime\Email;

class GmailApiMailer
{
    public function __construct(
        private string $googleClientId,
        private string $googleClientSecret,
        private string $googleOauthRefreshToken,
        private string $mailFromAddress,
        private string $mailFromName
    ) {}

    public function sendEmail(Email $email): void
    {
        $client = $this->createClient();
        $gmail = new Google_Service_Gmail($client);

        $raw = $this->encodeMessage($email);
        $message = new Google_Service_Gmail_Message();
        $message->setRaw($raw);

        $gmail->users_messages->send('me', $message);
    }

    private function createClient(): Client
    {
        $client = new Client();
        $client->setClientId($this->googleClientId);
        $client->setClientSecret($this->googleClientSecret);
        $client->setAccessType('offline');
        $client->setScopes([Google_Service_Gmail::GMAIL_SEND]);
        $client->setPrompt('consent');
        $client->setAccessToken([
            'refresh_token' => $this->googleOauthRefreshToken,
            'created' => time(),
        ]);

        $token = $client->fetchAccessTokenWithRefreshToken($this->googleOauthRefreshToken);

        if (isset($token['error'])) {
            throw new \RuntimeException('Google OAuth token error: ' . $token['error_description'] ?? $token['error']);
        }

        $client->setAccessToken($token);

        return $client;
    }

    private function encodeMessage(Email $email): string
    {
        $raw = $email->toString();
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
