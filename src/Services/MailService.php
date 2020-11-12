<?php


namespace Blog\Services;

use Blog\Entities\User;
use Core\Config;
use Core\Error;
use Core\HTTPResponse;
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

        try {
            return self::$mailer->send($message);
        } catch (\Exception $e) {
            Error::exceptionLogWriter($e);
            return false;
        }
    }

    public function sendPasswordResetEmail(User $user, string $token)
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . '/password/reset/' . $token;

        $text = HTTPResponse::getMailTemplate('Emails/reset-password.txt.twig', [
            'url' => $url
        ]);

        $html = HTTPResponse::getMailTemplate('Emails/reset-password.html.twig', [
            'url' => $url
        ]);

        return $this->send($user->getEmail(), 'Réinitialisation de votre mot de passe', $text, $html);
    }

    public function sendAccountActivationEmail(User $user, string $token)
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . '/account/activate/' . $token;

        $text = HTTPResponse::getMailTemplate('Emails/activate-account.txt.twig', [
            'url' => $url
        ]);

        $html = HTTPResponse::getMailTemplate('Emails/activate-account.html.twig', [
            'url' => $url
        ]);

        return $this->send($user->getEmail(), 'Activez votre compte', $text, $html);
    }

    public function sendMailChangeEmail(User $user, string $token)
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . '/account/change-email/' . $token;

        $text = HTTPResponse::getMailTemplate('Emails/change-mail.txt.twig', [
            'url' => $url
        ]);

        $html = HTTPResponse::getMailTemplate('Emails/change-mail.html.twig', [
            'url' => $url
        ]);

        return $this->send($user->getNewEmail(), 'Activez votre compte', $text, $html);
    }
}