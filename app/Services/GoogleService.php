<?php

declare(strict_types=1);

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

/**
 * Class GoogleService
 *
 * @package App\Services
 */
class GoogleService
{
    public function getSheetsService(): Sheets
    {
        return new Sheets($this->getClient());
    }

    private function getClient(): Client
    {
        $client = new Client();
        $client->setApplicationName('Google Sheets API PHP');
        $client->setScopes(Sheets::SPREADSHEETS_READONLY);
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
}
