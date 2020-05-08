<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class Report
{
    private $domain;
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
    }

    public function submit()
    {
        $captcha = new \Nelliel\CAPTCHA($this->domain);

        if ($this->domain->setting('use_report_captcha'))
        {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha_result = $captcha->verify($captcha_key, $captcha_answer);
        }

        if ($this->domain->setting('use_report_recaptcha'))
        {
            $captcha->verifyReCAPTCHA();
        }

        $report_data = array();
        $report_data['reason'] = $_POST['report_reason'] ?? null;
        $report_data['reporter_ip'] = $_SERVER['REMOTE_ADDR'];
        $base_content_id = new \Nelliel\ContentID();

        foreach ($_POST as $name => $value)
        {
            if ($base_content_id->isContentID($name))
            {
                $content_id = new \Nelliel\ContentID($name);
            }
            else
            {
                continue;
            }

            if ($value == 'action')
            {
                $report_data['content_id'] = $content_id->getIDString();
                $query = 'INSERT INTO "' . REPORTS_TABLE .
                '" ("board_id", "content_id", "reason", "reporter_ip") VALUES (?, ?, ?, ?)';
                $prepared = $this->database->prepare($query);
                $this->database->executePrepared($prepared,
                        [$this->domain->id(), $report_data['content_id'], $report_data['reason'],
                        @inet_pton($report_data['reporter_ip'])]);
            }
        }
    }
}