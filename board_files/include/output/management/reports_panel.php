<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_reports_panel($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_reports_access'))
    {
        nel_derp(380, _gettext('You are not allowed to access the reports panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Reports')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/reports_panel.html');

    if ($domain->id() !== '')
    {
        $prepared = $database->prepare(
                'SELECT * FROM "' . REPORTS_TABLE . '" WHERE "board_id" = ? ORDER BY "report_id" DESC');
        $report_list = $database->executePreparedFetchAll($prepared, [$domain->id()], PDO::FETCH_ASSOC);
    }
    else
    {
        $report_list = $database->executeFetchAll('SELECT * FROM "' . REPORTS_TABLE . '" ORDER BY "report_id" DESC',
                PDO::FETCH_ASSOC);
    }

    $report_info_table = $dom->getElementById('report-info-table');
    $report_info_row = $dom->getElementById('report-info-row');
    $bgclass = 'row1';
    $domains = array();

    foreach ($report_list as $report_info)
    {
        if (!isset($domains[$report_info['board_id']]))
        {
            $domains[$report_info['board_id']] = new \Nelliel\DomainBoard($report_info['board_id'],
                    new \Nelliel\CacheHandler(), $database);
        }

        $current_domain = $domains[$report_info['board_id']];
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $temp_report_info_row = $report_info_row->cloneNode(true);
        $temp_report_info_row->extSetAttribute('class', $bgclass);
        $report_nodes = $temp_report_info_row->getElementsByAttributeName('data-parse-id', true);
        $content_id = new \Nelliel\ContentID($report_info['content_id']);
        $base_domain = BASE_DOMAIN . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
        $board_web_path = '//' . $base_domain . '/' . rawurlencode($current_domain->reference('board_directory')) . '/';
        $content_link = '';

        if ($content_id->isThread())
        {
            $content_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                        'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                        'modmode' => 'true']);
            $report_nodes['link-file-url']->remove();
        }
        else if ($content_id->isPost())
        {
            $content_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                        'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                        'modmode' => 'true']);
            $content_link .= '#t' . $content_id->thread_id . 'p' . $content_id->post_id;
            $report_nodes['link-file-url']->remove();
        }
        else if ($content_id->isFile())
        {
            $prepared = $database->prepare(
                    'SELECT "filename" FROM "' . $current_domain->reference('content_table') .
                    '" WHERE "parent_thread" = ? AND post_ref = ? AND "content_order" = ?');
            $filename = $database->executePreparedFetch($prepared,
                    [$content_id->thread_id, $content_id->post_id, $content_id->order_id], PDO::FETCH_COLUMN);
            $src_web_path = $board_web_path . rawurlencode($current_domain->reference('src_dir')) . '/';
            $file_link = $src_web_path . $content_id->thread_id . '/' . $content_id->post_id . '/' .
                    rawurlencode($filename);
            $report_nodes['link-file-url']->extSetAttribute('href', $file_link);

            $content_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'render', 'action' => 'view-thread', 'thread' => $content_id->thread_id,
                        'content-id' => $content_id->getIDString(), 'board_id' => $report_info['board_id'],
                        'modmode' => 'true']);
            $content_link .= '#t' . $content_id->thread_id . 'p' . $content_id->post_id;
        }

        $report_nodes['report-id']->setContent($report_info['report_id']);
        $report_nodes['board-id']->setContent($report_info['board_id']);
        $report_nodes['link-content-url']->setContent($report_info['content_id']);
        $report_nodes['link-content-url']->extSetAttribute('href', $content_link);
        $report_nodes['report-reason']->setContent($report_info['reason']);
        $report_nodes['reporter-ip']->setContent(@inet_ntop($report_info['reporter_ip']));
        $report_nodes['link-report-dismiss']->extSetAttribute('href',
                MAIN_SCRIPT . '?module=reports&board_id=' . $report_info['board_id'] . '&action=dismiss&report_id=' .
                $report_info['report_id']);
        $report_info_table->appendChild($temp_report_info_row);
    }

    $report_info_row->remove();

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}
