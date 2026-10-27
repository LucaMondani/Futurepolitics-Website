<?php
// --- SMTP-Konfiguration ---
// Zugangsdaten NICHT im Repo: mailer-config.php liegt OBERHALB des Doc-Roots
// (Vorlage: mailer-config.example.php).
$smtpConfigPath = dirname(__DIR__) . '/mailer-config.php';
if (!is_readable($smtpConfigPath)) {
    error_log('mailer: Konfiguration fehlt unter ' . $smtpConfigPath);
    http_response_code(500);
    exit('Kontaktformular ist derzeit nicht verfügbar.');
}
$smtpConfig = require $smtpConfigPath;
define('SMTP_HOST', $smtpConfig['host']);
define('SMTP_USER', $smtpConfig['user']);
define('SMTP_PASS', $smtpConfig['pass']);
define('SMTP_PORT', (int)($smtpConfig['port'] ?? 587));
define('MAIL_TO', $smtpConfig['mail_to'] ?? $smtpConfig['user']);
// --------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Composer autoload oder manueller Pfad
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    require __DIR__ . '/phpmailer/src/Exception.php';
    require __DIR__ . '/phpmailer/src/PHPMailer.php';
    require __DIR__ . '/phpmailer/src/SMTP.php';
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: kontakt.html");
    exit;
}

$name = htmlspecialchars(trim($_POST["name"] ?? ""));
$email = htmlspecialchars(trim($_POST["email"] ?? ""));
$betreff = htmlspecialchars(trim($_POST["betreff"] ?? "Kontaktanfrage"));
$nachricht = htmlspecialchars(trim($_POST["nachricht"] ?? ""));

if (!$name || !$email || !$nachricht || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: kontakt.html?error=1");
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(SMTP_USER, 'Future Politics Website');
    $mail->addAddress(MAIL_TO);
    $mail->addReplyTo($email, $name);

    $mail->Subject = "Kontaktformular: $betreff";
    $mail->Body = "Von: $name <$email>\n\n$nachricht";

    $mail->send();
    header("Location: kontakt.html?success=1");
} catch (Exception $e) {
    header("Location: kontakt.html?error=1");
}
exit;
