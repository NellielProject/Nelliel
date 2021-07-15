<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Threads;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\Modules\Account\Session;

class Dispatch
{
    private $domain;
    private $session;

    function __construct(Domain $domain, Authorization $authorization, Session $session)
    {
        $this->domain = $domain;
        $this->session = $session;
    }

    public function dispatch(array $inputs)
    {
        //switch ($inputs['section'])
        //{
        $content_id = new \Nelliel\Content\ContentID($inputs['content_id']);

        if ($this->session->modmodeRequested())
        {
            $this->session->init(true);
        }

        $redirect = new \Nelliel\Redirect();
        $redirect->doRedirect(true);

        if (isset($_POST['form_submit_report']))
        {
            $report = new \Nelliel\Report($this->domain);
            $report->submit();

            if ($this->session->inModmode($this->domain))
            {
                $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=render&actions=view-index&index=0&board-id=' .
                        $inputs['board_id'] . '&modmode=true';
            }
            else
            {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $redirect->changeURL($url);
        }

        if (isset($_POST['form_submit_delete']))
        {
            $thread_handler = new \Nelliel\ThreadHandler($this->domain);
            $thread_handler->processContentDeletes();

            if ($this->session->inModmode($this->domain))
            {
                $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=render&actions=view-index&index=0&board-id=' .
                        $inputs['board_id'] . '&modmode=true';
            }
            else
            {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }

            $redirect->changeURL($url);
            $output_post = new \Nelliel\Render\OutputPost($this->domain, true);
            echo $output_post->contentDeleted(['forward_url' => $url], false);
        }

        //break;
        //}
    }
}