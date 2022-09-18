<?php
declare(strict_types = 1);

namespace Nelliel;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Content\ContentID;
use Nelliel\Domains\Domain;
use PDO;
use Nelliel\AntiSpam\CAPTCHA;

class Report
{
    private $domain;
    private $database;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
    }

    public function submit(): void
    {
        $captcha = new CAPTCHA($this->domain);

        if ($this->domain->setting('use_report_captcha')) {
            $captcha_key = $_COOKIE['captcha-key'] ?? '';
            $captcha_answer = $_POST['new_post']['captcha_answer'] ?? '';
            $captcha_result = $captcha->verify($captcha_key, $captcha_answer);
        }

        if ($this->domain->setting('use_report_recaptcha')) {
            $captcha->verifyReCAPTCHA();
        }

        $reports = array();

        foreach ($_POST as $name => $value) {
            $report_data = array();

            if (ContentID::isContentID($name)) {
                if ($value == 'action') {
                    $report_data['content_id'] = $name;
                    $report_data['reason'] = $_POST['report_reason'] ?? null;

                    if (nel_site_domain()->setting('store_unhashed_ip')) {
                        $report_data['reporter_ip'] = nel_request_ip_address();
                    } else {
                        $report_data['reporter_ip'] = '';
                    }

                    $report_data['hashed_reporter_ip'] = nel_request_ip_address(true);
                    $report_data['visitor_id'] = nel_visitor_id();
                    $reports[] = $report_data;
                }
            }
        }

        $report_count = count($reports);

        if ($report_count > nel_site_domain()->setting('max_report_items')) {
            nel_derp(130,
                sprintf(_gettext('You are trying to report too many items at once. Limit is %d.'),
                    nel_site_domain()->setting('max_report_items')));
        }

        foreach ($reports as $report_data) {
            $query = 'INSERT INTO "' . NEL_REPORTS_TABLE .
                '" ("board_id", "content_id", "reporter_ip", "hashed_reporter_ip", "visitor_id", "reason") VALUES (?, ?, ?, ?, ?, ?)';
            $prepared = $this->database->prepare($query);
            $prepared->bindValue(1, $this->domain->id(), PDO::PARAM_STR);
            $prepared->bindValue(2, $report_data['content_id'], PDO::PARAM_STR);
            $prepared->bindValue(3, nel_prepare_ip_for_storage($report_data['reporter_ip']), PDO::PARAM_LOB);
            $prepared->bindValue(4, $report_data['hashed_reporter_ip'], PDO::PARAM_STR);
            $prepared->bindValue(5, $report_data['visitor_id'], PDO::PARAM_STR);
            $prepared->bindValue(6, $report_data['reason'], PDO::PARAM_STR);
            $this->database->executePrepared($prepared);
        }
    }
}