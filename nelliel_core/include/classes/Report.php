<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;

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

        if (nel_site_domain()->setting('store_unhashed_ip'))
        {
            $report_data['reporter_ip'] = nel_request_ip_address();
        }
        else
        {
            $report_data['reporter_ip'] = '';
        }

        $report_data['hashed_reporter_ip'] = nel_request_ip_address(true);
        $base_content_id = new ContentID();

        foreach ($_POST as $name => $value)
        {
            if ($base_content_id->isContentID($name))
            {
                $content_id = new ContentID($name);
            }
            else
            {
                continue;
            }

            if ($value == 'action')
            {
                $report_data['content_id'] = $content_id->getIDString();
                $query = 'INSERT INTO "' . NEL_REPORTS_TABLE .
                        '" ("board_id", "content_id", "reporter_ip", "hashed_reporter_ip", "reason") VALUES (?, ?, ?, ?, ?)';
                $prepared = $this->database->prepare($query);
                $prepared->bindValue(1, $this->domain->id(), PDO::PARAM_STR);
                $prepared->bindValue(2, $report_data['content_id'], PDO::PARAM_STR);
                $prepared->bindValue(3, nel_prepare_ip_for_storage($report_data['reporter_ip']), PDO::PARAM_LOB);
                $prepared->bindValue(4, nel_prepare_hash_for_storage($report_data['hashed_reporter_ip']), PDO::PARAM_LOB);
                $prepared->bindValue(5, $report_data['reason'], PDO::PARAM_STR);
                $this->database->executePrepared($prepared);
            }
        }
    }
}