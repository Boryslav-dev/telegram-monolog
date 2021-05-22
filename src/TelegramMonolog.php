<?php

namespace App\Telegram;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class TelegramService extends AbstractProcessingHandler
{
    protected $timeOut;
    protected $token;
    protected $channel;
    protected $dateFormat;
    protected $curlOptions;

    public const host = 'https://api.telegram.org/bot';

    public function __construct(
        $token,
        $channel,
        $timeZone = 'UTC',
        $dateFormat = 'Y-m-d H:i:s',
        $timeOut = 100,
        $curlOptions = []
    ) {
        $this->token   = $token;
        $this->channel = $channel;
        $this->dateFormat = $dateFormat;
        $this->timeOut = $timeOut;
        date_default_timezone_set($timeZone);
        $this->curlOptions = $curlOptions;
    }

    public function write(array $record): void
    {
        $date =  date($this->dateFormat);
        $message =  $date . ', ' . $record['message'];
        $this->send($message);
    }

    public function send($message)
    {
        $ch = curl_init();
        $url = self::host . $this->token . "/SendMessage";
        $timeOut = $this->timeOut;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'text' => $message,
            'chat_id' => $this->channel,
        )));
        foreach ($this->curlOptions as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        $result = curl_exec($ch);
    }
}
