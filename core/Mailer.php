<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';

class Mailer {

    /** @var Config */
    private $config;

    /** @var Logger */
    private $logger;

    /** @var View */
    private $view;

    private $addresses = [];
    private $vars = [];

    public function __construct(Framework $framework) {
        $this->config = $framework->get('config');
        $this->view = $framework->get('view');
        $this->logger = $framework->get('logger');
    }
    
    public function init() {
        $this->addresses = [];
    }

    public function addAddress($email, $name=null) {
        $this->addresses[] = [
            'email' => $email,
            'name' => $name
        ];
    }

    public function set($name, $value) {
        $this->vars[$name] = $value;
    }

    public function send($subject, $templatePath, $vars=[]) {
        $body = $this->view->fetchWithLayout($templatePath, array_merge($this->vars, $vars));
        $result = true;
        if ($this->config->get('mailer.fake')) {
            $this->fakeSend($subject, $body);
        } else {
            $result = $this->realSend($subject, $body);
        }
        return $result;
    }

    private function fakeSend($subject, $body) {
        $to = [];
        foreach ($this->addresses as $address) {
            $to[] = $address['name'].' <'.$address['email'].'>';
        }        
        $message = "Fake mail sent\n";
        $message .= 'To: '.join('; ', $to)."\n";
        $message .= 'Subject: '.$subject."\n";
        $message .= "Message:\n".$body."\n";
        $this->logger->info($message);
        return true;
    }

    private function realSend($subject, $body) {
        $result = true;
        $mail = new PHPMailer(true);
        $mail->SMTPAuth = $this->config->get('mailer.smtp_auth', true);
        $mail->SMTPDebug = (int)$this->config->get('mailer.debug_level', 0);
        $mail->Debugoutput = function($str, $level) {
            $this->logger->info("debug level $level; message: $str");
        };
        if (!$this->config->get('mailer.verify_ssl')) {
            $this->disableVerify($mail);
        }
        try {
            $this->setDefaults($mail);
            $this->addAddresses($mail);
            $mail->Subject = '=?utf-8?Q?'.quoted_printable_encode($subject).'?=';
            $mail->Body = $body;
            $mail->send();
        } catch (PHPMailerException $e) {
            $this->logger->error($e->getMessage());
            $result = false;
        }
        return $result;
    }

    private function disableVerify(PHPMailer $mail) {
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }

    /**
     * @param PHPMailer $mail
     * @throws PHPMailerException
     */
    private function setDefaults(PHPMailer $mail) {
        $mail->isHTML(true);
        $mail->isSMTP();
        $mail->Host = $this->config->get('mailer.host');
        $mail->SMTPAuth = true;
        $mail->Username = $this->config->get('mailer.username');
        $mail->Password = $this->config->get('mailer.password');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = $this->config->get('mailer.port');
        $mail->Encoding = 'quoted-printable';
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($this->config->get('mailer.from.email'), $this->config->get('mailer.from.name'));
    }

    private function addAddresses(PHPMailer $mail) {
        foreach ($this->addresses as $address) {
            $mail->addAddress($address['email'], $address['name']);
        }
    }

}