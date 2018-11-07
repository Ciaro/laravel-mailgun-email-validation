<?php

namespace Kouz\LaravelMailgunValidation;

use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class EmailRule
{
    protected $client;
    protected $key;
    protected $log;
    protected $url;
    protected $verifySsl;

    public function __construct(Client $client, LoggerInterface $log, $options = [])
    {
        $defaults = [
            'key' => '',
            'endpoint' => 'https://api.mailgun.net/v3/address/private/validate',
            'verifySsl' => 'true'
        ];

        $options += $defaults;

        $this->client = $client;
        $this->key = $options['key'];
        $this->log = $log;
        $this->url = $options['endpoint'];
        $this->verifySsl = $options['verifySsl'];
    }

    public function validate($attribute, $value, $parameters)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        try {
            $mailgun = $this->getMailgunValidation($value, in_array('mailbox', $parameters));
        } catch (Exception $e) {
            $this->log->warning($e->getMessage(), [$e]);

            return !in_array('strict', $parameters);
        }

        if (!$mailgun->is_valid) {
            return false;
        }

        if (in_array('role', $parameters) && $mailgun->is_role_address) {
            return false;
        }

        if (in_array('disposable', $parameters) && $mailgun->is_disposable_address) {
            return false;
        }

        if (in_array('mailbox', $parameters) && $mailgun->mailbox_verification == "false") {
            return false;
        }

        if (in_array('strict', $parameters) && $mailgun->mailbox_verification == "unknown") {
            return false;
        }

        return true;
    }

    protected function getMailgunValidation($email, $mailboxVerification = false)
    {
        $options = [
            'query' => [
                'address'              => $email,
                'mailbox_verification' => $mailboxVerification,
            ],
            'auth' => [
                'api',
                $this->key
            ]
        ];

        if (!$this->verifySsl) {
            $options += ['verify' => false];
        }

        $response = $this->client->get($this->url, $options);

        if (!$mailgun = json_decode($response->getBody())) {
            throw new Exception('Failed to decode Mailgun response');
        }

        return $mailgun;
    }
}
