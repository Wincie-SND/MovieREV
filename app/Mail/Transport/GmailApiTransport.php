<?php

namespace App\Mail\Transport;

use App\Services\GmailApiClient;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class GmailApiTransport extends AbstractTransport
{
    public function __construct(private readonly GmailApiClient $gmail)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $this->gmail->sendRawMessage($message->toString());
    }

    public function __toString(): string
    {
        return 'gmail-api';
    }
}
