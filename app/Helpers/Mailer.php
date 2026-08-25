<?php
namespace App\Helpers;

class Mailer {
    public static function send($to, $subject, $body) {
        $config = require __DIR__ . '/../Config/config.php';
        $mailConfig = $config['mail'];

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $mailConfig['from_name'] . " <" . $mailConfig['from_email'] . ">" . "\r\n";

        self::logEmail($to, $subject, $body);

        try {
            return @mail($to, $subject, $body, $headers);
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function logEmail($to, $subject, $body) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/emails.log';
        $date = date('Y-m-d H:i:s');
        $content = "========================================\n";
        $content .= "Date: {$date}\nTo: {$to}\nSubject: {$subject}\nContent:\n{$body}\n";
        $content .= "========================================\n\n";
        file_put_contents($logFile, $content, FILE_APPEND);
    }
}
