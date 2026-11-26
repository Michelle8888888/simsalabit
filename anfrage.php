<?php
/*
  Simsalabit contact form, server side (Infomaniak web hosting, PHP 8).
  index.html sends the form here; this script emails it to Michelle and answers with JSON. Nothing is stored on the server.

  Settings: copy anfrage-config.example.php to anfrage-config.php and enter the password of the mailbox that sends the mails.
  With the password, the mail goes through Infomaniak's authenticated mail server (mail.infomaniak.com, port 465),
  which is what Infomaniak recommends. Without a password the script falls back to PHP mail(), which Infomaniak switches
  off by default: it must then be switched on in the Infomaniak Manager (the site > PHP settings), with a sender address.
*/
declare(strict_types=1);
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Zurich');

$cfg = [
  'to'          => 'magic@simsalabit.ch',   // where the requests go
  'from'        => 'magic@simsalabit.ch',   // sender of the mails; must be an address of a domain on the Infomaniak account
  'from_name'   => 'Simsalabit Webseite',
  'smtp_host'   => 'mail.infomaniak.com',
  'smtp_port'   => 465,                     // 465 = SSL/TLS (recommended by Infomaniak), 587 = STARTTLS
  'smtp_user'   => 'magic@simsalabit.ch',   // the full address of the mailbox that logs in to send
  'smtp_pass'   => '',                      // its password; empty = PHP mail() instead
  'smtp_verify' => true,                    // check the mail server's certificate (false only for local tests)
];
$cfgFile = __DIR__ . '/anfrage-config.php';
if (is_file($cfgFile)) { $own = require $cfgFile; if (is_array($own)) $cfg = array_replace($cfg, $own); }

header('X-Robots-Tag: noindex');
$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function answer(bool $ok, string $error = '', int $status = 200): never {
  global $wantsJson;
  http_response_code($status);
  header('Cache-Control: no-store');
  if ($wantsJson) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => $ok, 'error' => $error], JSON_UNESCAPED_UNICODE);
  } else {
    // only without JavaScript: a plain thank-you page
    header('Content-Type: text/html; charset=UTF-8');
    $msg = $ok ? 'Danke! Ihre Anfrage ist zu mir geflogen. Ich melde mich persönlich bei Ihnen.'
               : 'Das hat leider nicht geklappt. Schreiben Sie mir bitte direkt an magic@simsalabit.ch.';
    echo '<!DOCTYPE html><html lang="de"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>Simsalabit</title><body style="font-family:system-ui,sans-serif;background:#e7e3fb;color:#0d0b1a;padding:40px 20px;text-align:center">'
       . '<p style="font-size:20px;max-width:32em;margin:0 auto 24px">' . htmlspecialchars($msg) . '</p><p><a href="./" style="color:#5b3df5">Zurück zur Webseite</a></p></body></html>';
  }
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Location: ./'); exit; }

// a field of the form, as clean text: valid UTF-8, no control characters (no line breaks unless the field allows them), trimmed, limited
function field(string $key, int $max, bool $multiline = false): string {
  $v = $_POST[$key] ?? '';
  if (!is_string($v) || !mb_check_encoding($v, 'UTF-8')) return '';
  $v = $multiline ? preg_replace('/[^\P{C}\n]+/u', ' ', str_replace("\r\n", "\n", $v)) : preg_replace('/\p{C}+/u', ' ', $v);
  return mb_substr(trim((string) $v), 0, $max);
}

// bots fill the hidden field that people never see: pretend it worked and send nothing
if (field('bot-field', 50) !== '') answer(true);
// the page's script writes the opening time and a key made from it into the form. A program that posts straight to this file,
// without ever opening the page, has neither (this is how SEO spam arrived): pretend it worked and send nothing
$zeit = (int) field('zeit', 20);
if ($zeit <= 0 || field('schluessel', 20) !== (string) (($zeit % 100000) * 37 % 9973)) answer(true);

$name  = field('name', 120);
$email = field('email', 200);
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\s<>,"]/', $email)) {
  answer(false, 'Bitte Name und eine gültige E-Mail-Adresse angeben.', 400);
}
$telefon  = field('telefon', 60);
$adresse  = field('adresse', 200);
$paket    = field('paket', 40);
if (!in_array($paket, ['Spark', 'Spell', 'Grand illusion', 'Weiss ich noch nicht'], true)) $paket = '';
$geschaeft = field('geschaeft', 3000, true);
$webseite  = field('webseite', 3000, true);
$sprache   = field('sprache', 2) === 'en' ? 'Englisch' : 'Deutsch';
// still delivered, but marked "[Bitte prüfen]": a form sent within three seconds of being opened, text with several links,
// typical SEO spam wording, or a sender address that carries the site's own name (a favourite of spam programs)
$text = $geschaeft . "\n" . $webseite;
$links = preg_match_all('~https?://|www\.|\b[a-z0-9-]+\.(?:com|net|org|pro|live|xyz|info|top|site|online|shop|biz|ru|cn)\b~i', $text);
$spamWorte = (bool) preg_match('~search index|search results|search engine|backlinks?|rank(?:ing)? your|list your|feature your|register your|crypto|bitcoin|casino|viagra~i', $text);
$selbstBezug = stripos($email, 'simsalabit') !== false;
$verdacht = time() - $zeit < 3 || $links >= 2 || $spamWorte || $selbstBezug;

$subject = ($verdacht ? '[Bitte prüfen] ' : '') . 'Anfrage über simsalabit.ch von ' . $name . ($paket !== '' ? ' (' . $paket . ')' : '');
$body = "Neue Anfrage über simsalabit.ch\n\n"
      . "Name: $name\n"
      . "E-Mail: $email\n"
      . "Telefon: " . ($telefon !== '' ? $telefon : '-') . "\n"
      . "Adresse: " . ($adresse !== '' ? $adresse : '-') . "\n"
      . "Paket: " . ($paket !== '' ? $paket : '-') . "\n"
      . "Sprache der Webseite: $sprache\n\n"
      . "Geschäft:\n" . ($geschaeft !== '' ? $geschaeft : '-') . "\n\n"
      . "Webseiten-Idee:\n" . ($webseite !== '' ? $webseite : '-') . "\n\n"
      . "Gesendet am " . date('d.m.Y') . " um " . date('H:i') . " Uhr"
      . ($verdacht ? "\nHinweis: Diese Anfrage sieht nach Spam aus (" . implode(', ', array_filter([time() - $zeit < 3 ? 'sehr schnell abgeschickt' : '', $links >= 2 ? 'mehrere Links' : '', $spamWorte ? 'typische Spam-Wörter' : '', $selbstBezug ? 'Absenderadresse mit simsalabit' : ''])) . ")." : '') . "\n";

$from = (string) $cfg['from'];
$to = (string) $cfg['to'];
$encName = '=?UTF-8?B?' . base64_encode(mb_substr($name, 0, 40)) . '?=';   // the visitor's name, safe inside an address header
$headers = [
  'From'                      => '=?UTF-8?B?' . base64_encode((string) $cfg['from_name']) . '?= <' . $from . '>',
  'Reply-To'                  => $encName . ' <' . $email . '>',
  'MIME-Version'              => '1.0',
  'Content-Type'              => 'text/plain; charset=UTF-8',
  'Content-Transfer-Encoding' => 'quoted-printable',
  'X-Mailer'                  => 'simsalabit.ch anfrage.php',
];
$viaSmtp = (string) $cfg['smtp_pass'] !== '';

try {
  if ($viaSmtp) {
    $nl = "\r\n";
    $data = 'Date: ' . date(DATE_RFC2822) . $nl
          . 'To: <' . $to . '>' . $nl
          . 'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8', 'B', $nl) . $nl
          . 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', $from)[1] ?? 'simsalabit.ch') . '>' . $nl;
    foreach ($headers as $k => $v) $data .= $k . ': ' . $v . $nl;
    $data .= $nl . str_replace("\n", $nl, str_replace("\r\n", "\n", quoted_printable_encode($body)));
    smtp_send($cfg, $from, $to, $data);
  } else {
    $ok = mail($to, mb_encode_mimeheader($subject, 'UTF-8', 'B', "\n"), str_replace("\r\n", "\n", quoted_printable_encode($body)), $headers, '-f' . $from);
    if (!$ok) throw new RuntimeException('PHP mail() hat die Nachricht nicht angenommen (ist die Funktion im Infomaniak Manager eingeschaltet?)');
  }
} catch (Throwable $e) {
  error_log('anfrage.php: ' . $e->getMessage());
  answer(false, 'Die Nachricht konnte nicht gesendet werden.', 500);
}
answer(true);

// Sends one mail through Infomaniak's mail server, logged in with the mailbox's address and password.
// Port 465 speaks TLS from the start; any other port starts plain and switches to TLS with STARTTLS.
function smtp_send(array $c, string $from, string $to, string $data): void {
  $host = (string) $c['smtp_host']; $port = (int) $c['smtp_port']; $tlsFirst = $port === 465; $verify = (bool) $c['smtp_verify'];
  $ctx = stream_context_create(['ssl' => ['verify_peer' => $verify, 'verify_peer_name' => $verify, 'allow_self_signed' => !$verify, 'SNI_enabled' => true, 'peer_name' => $host]]);
  $fp = @stream_socket_client(($tlsFirst ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $ctx);
  if (!$fp) throw new RuntimeException("Verbindung zu $host:$port fehlgeschlagen: " . ($errstr !== '' ? $errstr : (error_get_last()['message'] ?? 'unbekannter Fehler')));
  stream_set_timeout($fp, 20);
  $read = function () use ($fp): string {
    $all = '';
    do {
      $line = fgets($fp, 2048);
      if ($line === false) throw new RuntimeException('Der Mailserver antwortet nicht' . ($all !== '' ? ': ' . trim($all) : ''));
      $all .= $line;
    } while (isset($line[3]) && $line[3] === '-');   // "250-..." lines continue, "250 ..." ends the reply
    return $all;
  };
  $say = function (string $cmd, int ...$ok) use ($fp, $read): string {
    if ($cmd !== '') fwrite($fp, $cmd . "\r\n");
    $r = $read();
    if (!in_array((int) substr($r, 0, 3), $ok, true)) throw new RuntimeException('Mailserver: ' . trim($r));
    return $r;
  };
  $me = preg_replace('/[^A-Za-z0-9.-]/', '', $_SERVER['SERVER_NAME'] ?? '') ?: 'localhost';
  $say('', 220);
  $say('EHLO ' . $me, 250);
  if (!$tlsFirst) {
    $say('STARTTLS', 220);
    if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('TLS-Verschlüsselung fehlgeschlagen');
    $say('EHLO ' . $me, 250);
  }
  $say('AUTH LOGIN', 334);
  $say(base64_encode((string) $c['smtp_user']), 334);
  $say(base64_encode((string) $c['smtp_pass']), 235);
  $say('MAIL FROM:<' . $from . '>', 250);
  $say('RCPT TO:<' . $to . '>', 250, 251);
  $say('DATA', 354);
  // a line that starts with a dot gets a second dot (SMTP rule); a lone dot ends the message
  $say(preg_replace('/^\./m', '..', rtrim($data, "\r\n")) . "\r\n.", 250);
  $say('QUIT', 221);
  fclose($fp);
}
