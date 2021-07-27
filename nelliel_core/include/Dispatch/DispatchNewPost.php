<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\FGSFDS;
use Nelliel\Redirect;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use Nelliel\NewPost\NewPost;
use Nelliel\Output\OutputPost;

class DispatchNewPost extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
    }

    public function dispatch(array $inputs)
    {
        //switch ($inputs['section'])
        //{
        $new_post = new NewPost($this->domain, $this->session);
        $new_post->processPost();

        $redirect = new Redirect();
        $redirect->doRedirect(true);
        $fgsfds = new FGSFDS();

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
        $output_post = new OutputPost($this->domain, true);
        echo $output_post->postSuccess(['forward_url' => $url], false);
        nel_clean_exit();

        //break;
        //}
    }
}