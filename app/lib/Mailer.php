<?php
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../config/mail.php';
    }

    public function enviarAlerta($destino, $asunto, $cuerpo) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['smtp_user'];
            $mail->Password   = $this->config['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->config['smtp_port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($destino);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpo;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar alerta: {$e->getMessage()}");
            return false;
        }
    }
}
