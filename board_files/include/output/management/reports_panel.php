<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_reports_panel()
{
    $dbh = nel_database();
    $language = new \Nelliel\language\Language();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Reports')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/reports_panel.html');

    $report_list = $dbh->executeFetchAll('SELECT * FROM "' . REPORTS_TABLE . '" ORDER BY "report_id" DESC',
            PDO::FETCH_ASSOC);
    $report_info_table = $dom->getElementById('report-info-table');
    $report_info_row = $dom->getElementById('report-info-row');
    $bgclass = 'row1';

    foreach ($report_list as $report_info)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $temp_report_info_row = $report_info_row->cloneNode(true);
        $temp_report_info_row->extSetAttribute('class', $bgclass);
        $report_nodes = $temp_report_info_row->getElementsByAttributeName('data-parse-id', true);
        $references = nel_parameters_and_data()->boardReferences($report_info['board_id']);
        $content_id = new \Nelliel\ContentID($report_info['content_id']);
        $base_domain = $_SERVER['SERVER_NAME'] . pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
        $board_web_path = '//' . $base_domain . '/' . rawurlencode($references['board_directory']) . '/';
        $content_link = '';

        if ($content_id->isThread())
        {
            $pages_web_path = $board_web_path . rawurlencode($references['page_dir']) . '/';
            $content_link = $pages_web_path . $content_id->thread_id . '/' . $content_id->thread_id . '.html';
        }
        else if ($content_id->isPost())
        {
            $pages_web_path = $board_web_path . rawurlencode($references['page_dir']) . '/';
            $thread_link = $pages_web_path . $content_id->thread_id . '/' . $content_id->thread_id . '.html';
            $post_anchor = '#p' . $content_id->thread_id . '_' . $content_id->post_id;
            $content_link = $thread_link . $post_anchor;
        }
        else if ($content_id->isFile())
        {
            $prepared = $dbh->prepare(
                    'SELECT "filename" FROM "' . $references['file_table'] .
                    '" WHERE "parent_thread" = ? AND post_ref = ? AND "file_order" = ? LIMIT 1');
            $filename = $dbh->executePreparedFetch($prepared,
                    [$content_id->thread_id, $content_id->post_id, $content_id->order_id], PDO::FETCH_COLUMN);
            $src_web_path = $board_web_path . rawurlencode($references['src_dir']) . '/';
            $content_link = $src_web_path . $content_id->thread_id . '/' . $content_id->post_id . '/' .
                    rawurlencode($filename);
        }

        $report_nodes['report-id']->setContent($report_info['report_id']);
        $report_nodes['board-id']->setContent($report_info['board_id']);
        $report_nodes['link-content-url']->setContent($report_info['content_id']);
        $report_nodes['link-content-url']->extSetAttribute('href', $content_link);
        $report_nodes['report-reason']->setContent($report_info['reason']);
        $report_nodes['reporter-ip']->setContent(@inet_ntop($report_info['reporter_ip']));
        $report_nodes['link-report-dismiss']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=reports&action=dismiss&report_id=' . $report_info['report_id']);
        $report_nodes['link-report-ban']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=reports&action=ban&report_id=' . $report_info['report_id']);
        $report_nodes['link-report-delete']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=reports&action=delete&report_id=' . $report_info['report_id']);
        $report_nodes['link-report-ban-delete']->extSetAttribute('href',
                PHP_SELF . '?manage=general&module=reports&action=ban-delete&report_id=' . $report_info['report_id']);
        $report_info_table->appendChild($temp_report_info_row);
    }

    $report_info_row->remove();

    $language->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}
