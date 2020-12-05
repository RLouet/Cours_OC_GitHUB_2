<?php


namespace Blog\Services;

use Blog\Entities\ContactMessage;
use Blog\Entities\User;
use Blog\Models\BlogManagerPDO;
use Core\Config;
use Core\Error;
use Core\HTTPResponse;
use Core\PDOFactory;
use Swift_SmtpTransport;
use Swift_Message;
use Swift_Mailer;


class MailService
{
    private static ?self $instance = null;

    private static Swift_Mailer $mailer;
    private static array $from;
    private Config $config;
    private HTTPResponse $httpResponse;

    public function __construct()
    {
        $this->httpResponse = new HTTPResponse();

        $this->config = Config::getInstance();
        $transport = (new Swift_SmtpTransport($this->config->get('mailer_host'), $this->config->get('mailer_port')))
            ->setUsername($this->config->get('mailer_username'))
            ->setPassword($this->config->get('mailer_password'))
            ;
        self::$mailer = new Swift_Mailer($transport);
        $from = [$this->config->get('mailer_from_mail') => $this->config->get('mailer_from_name')];
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

    public function send(string $to, string $subject, string $text, string $html, ?array $replyTo = null)
    {
        $message = new Swift_Message($subject);
        $message->setFrom(self::$from);
        $message->setTo($to);
        $message->setBody($html, 'text/html');
        $message->addPart($text, 'text/plain');

        if ($replyTo) {
            $message->setReplyTo($replyTo);
        }

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

        $text = $this->httpResponse->getMailTemplate('Emails/reset-password.txt.twig', [
            'url' => $url
        ]);

        $html = $this->httpResponse->getMailTemplate('Emails/reset-password.html.twig', [
            'url' => $url
        ]);

        return $this->send($user->getEmail(), 'Réinitialisation de votre mot de passe', $text, $html);
    }

    public function sendAccountActivationEmail(User $user, string $token)
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . '/account/activate/' . $token;

        $text = $this->httpResponse->getMailTemplate('Emails/activate-account.txt.twig', [
            'url' => $url
        ]);

        $html = $this->httpResponse->getMailTemplate('Emails/activate-account.html.twig', [
            'url' => $url
        ]);

        return $this->send($user->getEmail(), 'Activez votre compte', $text, $html);
    }

    public function sendMailChangeEmail(User $user, string $token)
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . '/account/change-email/' . $token;

        $text = $this->httpResponse->getMailTemplate('Emails/change-mail.txt.twig', [
            'url' => $url
        ]);

        $html = $this->httpResponse->getMailTemplate('Emails/change-mail.html.twig', [
            'url' => $url
        ]);

        return $this->send($user->getNewEmail(), 'Modification de votre adresse Email', $text, $html);
    }

    public function sendContactEmail(ContactMessage $contactMessage)
    {
        $blogId = $this->config->get('blog_id') ? $this->config->get('blog_id') : 1;
        $blogManager = new BlogManagerPDO(PDOFactory::getPDOConnexion());
        $destination = $blogManager->getData($blogId)->getContactMail();

        $text = $this->httpResponse->getMailTemplate('Emails/contact.txt.twig', [
            'contact_message' => $contactMessage
        ]);

        $html = $this->httpResponse->getMailTemplate('Emails/contact.html.twig', [
            'contact_message' => $contactMessage
        ]);
        $subject = "[contact] ";
        $subject .= empty($contactMessage->getSubject()) ? "Nouveau message" : $contactMessage->getSubject();

        $replyTo = [
            $contactMessage->getEmail() => $contactMessage->getFirstname() . " " . $contactMessage->getLastname()
        ];

        return $this->send($destination, $subject, $text, $html,$replyTo );
    }
}