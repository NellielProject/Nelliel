<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class CAPTCHA
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getCaptcha()
    {
        nel_plugins()->processHook('nel-get-captcha', array());
    }

    public function verifyCaptcha()
    {
        nel_plugins()->processHook('nel-verify-captcha', array());
    }

    public function verifyReCaptcha()
    {
        if (!isset($_POST['g-recaptcha-response']))
        {
            return false;
        }

        $site_domain = new \Nelliel\Domain('', new \Nelliel\CacheHandler(), $this->database);
        $response = $_POST['g-recaptcha-response'];
        nel_plugins()->processHook('nel-verify-recaptcha', [$reponse]);
        $result = file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' . $site_domain->setting('recaptcha_sekrit_key') .
                '&response=' . $response);
        $verification = json_decode($result);
        return $verification->success;
    }
}