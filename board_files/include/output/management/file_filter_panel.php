<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_file_filter_panel()
{
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null, array('header' => 'Board Management',
        'sub_header' => 'Bans'));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/file_filter_panel.html');
    $dom->getElementById('add-file-filter-form')->extSetAttribute('action', PHP_SELF .
         '?manage=general&module=file-filter');

    $result = $dbh->query('SELECT * FROM "' . FILE_FILTER_TABLE . '" ORDER BY "entry" DESC');
    $file_filter_table = $dom->getElementById('file-filter-table');
    $file_filter_row = $dom->getElementById('file-filter-row');
    $i = 0;

    while ($result && $filter_info = $result->fetch(PDO::FETCH_ASSOC))
    {
        if ($i & 1)
        {
            $bgclass = 'row1';
        }
        else
        {
            $bgclass = 'row2';
        }

        $temp_filter_info_row = $file_filter_row->cloneNode(true);
        $temp_filter_info_row->extSetAttribute('class', $bgclass);
        $file_filter_td_list = $temp_filter_info_row->doXPathQuery(".//td");
        $file_filter_td_list->item(0)->setContent($filter_info['entry']);
        $file_filter_td_list->item(1)->setContent($filter_info['hash_type']);
        $file_filter_td_list->item(2)->setContent(bin2hex($filter_info['file_hash']));
        $file_filter_td_list->item(3)->setContent($filter_info['file_notes']);

        $form_remove_filter = $temp_filter_info_row->getElementById('form-remove-filter-');
        $form_remove_filter->extSetAttribute('action', PHP_SELF . '?manage=general&module=file-filter');
        $form_remove_filter->changeId('form-remove-filter-' . $filter_info['entry']);
        $form_remove_filter->doXPathQuery(".//input[@name='filter_id']")->item(0)->extSetAttribute('value', $filter_info['entry']);

        $file_filter_table->appendChild($temp_filter_info_row);
        $i ++;
    }

    unset($result);
    $file_filter_row->removeSelf();
    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}