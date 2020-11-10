<?php


namespace Blog\Services;

use Core\Config;
use Swift_SmtpTransport;
use Swift_Message;
use Swift_Mailer;


class MailService
{
    private static ?self $instance = null;

    private static Swift_Mailer $mailer;
    private static array $from;

    public function __construct()
    {
        $config = Config::getInstance();
        $transport = (new Swift_SmtpTransport($config->get('mailer_host'), $config->get('mailer_port')))
            ->setUsername($config->get('mailer_username'))
            ->setPassword($config->get('mailer_password'))
            ;
        self::$mailer = new Swift_Mailer($transport);
        $from = [$config->get('mailer_from_mail') => $config->get('mailer_from_name')];
        self::$from = $from;
    }

    public static function getInstance(): self
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function send(string $to, string $subject, string $text, string $html)
    {
        $message = new Swift_Message($subject);
        $message->setFrom(self::$from);
        $message->setTo($to);
        $message->setBody($html, 'text/html');
        $message->addPart($text, 'text/plain');

        return self::$mailer->send($message);
    }
}