<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_reports_panel()
{
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Reports')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/reports_panel.html');

    $report_list = $dbh->executeFetchAll('SELECT * FROM "' . REPORTS_TABLE . '" ORDER BY "report_id" DESC', PDO::FETCH_ASSOC);
    $report_info_table = $dom->getElementById('report-info-table');
    $report_info_row = $dom->getElementById('report-info-row');
    $bgclass = 'row1';

    foreach ($report_list as $report_info)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $temp_report_info_row = $report_info_row->cloneNode(true);
        $temp_report_info_row->extSetAttribute('class', $bgclass);
        $report_nodes = $temp_report_info_row->getElementsByAttributeName('data-parse-id', true);
        $report_nodes['report-id']->setContent($report_info['report_id']);
        $report_nodes['board-id']->setContent($report_info['board_id']);
        $report_nodes['content-id']->setContent($report_info['content_id']);
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

    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}
