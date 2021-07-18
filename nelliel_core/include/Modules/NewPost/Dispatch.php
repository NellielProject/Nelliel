<?php
declare(strict_types = 1);

namespace Nelliel\Modules\NewPost;

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
        $new_post = new \Nelliel\Modules\NewPost\NewPost($this->domain, $this->session);
        $new_post->processPost();

        $redirect = new \Nelliel\Redirect();
        $redirect->doRedirect(true);
        $fgsfds = new \Nelliel\FGSFDS();

        if ($fgsfds->commandIsSet('noko') || $this->domain->setting('always_noko'))
        {
            if ($this->session->inModmode($this->domain))
            {
                $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                        http_build_query(
                                ['module' => 'output', 'section' => 'thread', 'actions' => 'view',
                                    'thread' => $fgsfds->getCommandData('noko', 'topic'),
                                    'board-id' => $inputs['board_id'], 'modmode' => 'true']);
            }
            else
            {
                $url = $this->domain->reference('board_directory') . '/' . $this->domain->reference('page_dir') . '/' .
                        $fgsfds->getCommandData('noko', 'topic') . '/' .
                        sprintf(nel_site_domain()->setting('thread_filename_format'),
                                $fgsfds->getCommandData('noko', 'topic')) . NEL_PAGE_EXT;
            }
        }
        else
        {
            if ($this->session->inModmode($this->domain))
            {
                $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=output&section=index&actions=view&index=0&board-id=' .
                        $inputs['board_id'] . '&modmode=true';
            }
            else
            {
                $url = $this->domain->reference('board_directory') . '/' . NEL_MAIN_INDEX . NEL_PAGE_EXT;
            }
        }

        $redirect->changeURL($url);
        $output_post = new \Nelliel\Modules\Output\OutputPost($this->domain, true);
        echo $output_post->postSuccess(['forward_url' => $url], false);
        nel_clean_exit();

        //break;
        //}
    }
}