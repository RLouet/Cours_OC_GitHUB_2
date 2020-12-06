<?php


namespace Core;


class Flash
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'danger';

    public static function addMessage(string $message, string $type = 'success'): void
    {
        if (!isset($_SESSION['flash_notifications'])) {
            $_SESSION['flash_notifications'] = [];
        }
        $_SESSION['flash_notifications'][] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public static function getMessages(): ?array
    {
        if (isset($_SESSION['flash_notifications'])) {
            $messages = $_SESSION['flash_notifications'];
            unset($_SESSION['flash_notifications']);
            return  $messages;
        }
        return null;
    }
}