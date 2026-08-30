<?php
function sendMail($to, $subject, $body, $from = ADMIN_EMAIL) {
    $headers = "From: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    return mail($to, $subject, $body, $headers);
}
