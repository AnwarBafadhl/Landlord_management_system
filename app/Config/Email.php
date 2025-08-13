<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'mail';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Configuration
     */
    public string $SMTPHost = 'smtp.gmail.com';
    public string $SMTPUser = 'AnwarBafadel370@gmail.com';  // Your Gmail address
    public string $SMTPPass = 'nftv dnut elch pwlp';     // You need to set this!
    public int $SMTPPort = 587;
    public string $SMTPCrypto = 'tls';

    /**
     * Email From Configuration
     */
    public string $fromEmail = 'AnwarBafadel370@gmail.com';
    public string $fromName = 'Property Management System';

    public bool $SMTPKeepAlive = false;
    public int $SMTPTimeout = 30;
    public bool $wordWrap = true;
    public int $wrapChars = 76;
    public string $mailType = 'html';
    public string $charset = 'UTF-8';
    public bool $validate = false; // Changed to false to avoid validation issues
    public int $priority = 3;
    public string $CRLF = "\r\n";
    public string $newline = "\r\n";
    public bool $BCCBatchMode = false;
    public int $BCCBatchSize = 200;
    public bool $DSN = false;
}