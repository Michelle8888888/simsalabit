<?php
// Settings of the contact form (anfrage.php). Copy this file to anfrage-config.php and fill in the password.
// Keep anfrage-config.php private: it is not in Git, and .htaccess blocks it on the web server.
return [
  'to'        => 'magic@simsalabit.ch',   // the requests go here
  'from'      => 'magic@simsalabit.ch',   // the mails come from here (an address of the domain on the Infomaniak account)
  'smtp_user' => 'magic@simsalabit.ch',   // the mailbox that logs in at mail.infomaniak.com to send
  'smtp_pass' => '',                      // its password (Infomaniak Manager > Mail service). Empty = PHP mail() instead.
];
