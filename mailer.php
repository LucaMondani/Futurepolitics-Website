<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: kontakt.html");
    exit;
}

$name      = htmlspecialchars(trim($_POST["name"] ?? ""));
$email     = htmlspecialchars(trim($_POST["email"] ?? ""));
$betreff   = htmlspecialchars(trim($_POST["betreff"] ?? "Kontaktanfrage"));
$nachricht = htmlspecialchars(trim($_POST["nachricht"] ?? ""));

if (!$name || !$email || !$nachricht || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: kontakt.html?error=1");
    exit;
}

$to      = "info@futurepolitics.de";
$subject = "Kontaktformular: $betreff";
$body    = "Von: $name <$email>\n\n$nachricht";
$headers = implode("\r\n", [
    "From: noreply@futurepolitics.de",
    "Reply-To: $email",
    "Content-Type: text/plain; charset=UTF-8",
]);

if (mail($to, $subject, $body, $headers)) {
    header("Location: kontakt.html?success=1");
} else {
    header("Location: kontakt.html?error=1");
}
exit;
