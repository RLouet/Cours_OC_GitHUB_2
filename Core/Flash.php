<?php


namespace Core;


class Flash
{
    private static ?Flash $instance = null;

    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'danger';

    private array $session;
    private HTTPRequest $httpRequest;

    private function __construct()
    {
        $this->session = &$_SESSION;
        $this->httpRequest = HTTPRequest::getInstance();
    }

    public static function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function addMessage(string $message, string $type = 'success'): void
    {
        if (!isset($this->session['flash_notifications'])) {
            $this->session['flash_notifications'] = [];
        }
        $this->session['flash_notifications'][] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public function getMessages(): ?array
    {
        if (isset($this->session['flash_notifications'])) {
            $messages = $this->session['flash_notifications'];
            unset($this->session['flash_notifications']);
            return  $messages;
        }
        return null;
    }
}