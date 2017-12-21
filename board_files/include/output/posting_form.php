<?php

require_once INCLUDE_PATH . 'output/rules.php';

function nel_render_posting_form($dataforce, $render)
{
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'posting_form.html');
    $dotdot = isset($dataforce['dotdot']) ? $dataforce['dotdot'] : '';
    $response_id = (is_null($dataforce['response_id'])) ? '0' : $dataforce['response_id'];
    $post_form_return_link = $dom->getElementById('post-form-return-link');

    if (!nel_session_is_ignored('render'))
    {
        $page_ref1 = PHP_SELF . '?mode=display&page=0';
        $page_ref2 = PHP_SELF . '?page=';
    }
    else
    {
        $page_ref1 = PHP_SELF2 . PHP_EXT;
    }

    $posting_form = $dom->getElementById('posting-form');
    $posting_form->extSetAttribute('action', $dotdot . PHP_SELF);

    //$render->add_data('response_id', (is_null($dataforce['response_id'])) ? '0' : $dataforce['response_id']);

    if ($response_id)
    {
        $post_form_return_link->doXPathQuery(".//a")->item(0)->extSetAttribute('href', $dotdot . $page_ref1);
    }
    else
    {
        $post_form_return_link->removeSelf();
    }

    if ($dataforce['get_mode'] !== 'display')
    {
        $posting_form->doXPathQuery(".//input[@name='mode2']")->item(0)->removeSelf();
    }

    $new_post_element = $posting_form->doXPathQuery(".//input[@name='new_post[post_info][response_to]']", $posting_form)->item(0);
    $new_post_element->extSetAttribute('value', $response_id);
    $dom->getElementById('not-anonymous')->extSetAttribute('maxlength', BS_MAX_NAME_LENGTH);
    $dom->getElementById('spam-target')->extSetAttribute('maxlength', BS_MAX_EMAIL_LENGTH);
    $dom->getElementById('verb')->extSetAttribute('maxlength', BS_MAX_SUBJECT_LENGTH);

    if (BS_FORCE_ANONYMOUS)
    {
        $dom->getElementById('form-not-anonymous');
        $dom->getElementById('form-spam-target');
    }

    // File Block
    $file_block = $dom->getElementById('form-file-1');
    $source_block = $dom->getElementById('form-sauce-1');
    $license_block = $dom->getElementById('form-lol_drama-1');
    $posting_form_table = $dom->getElementById('posting-form-table');
    $source_block->getElementById('sauce-1')->extSetAttribute('maxlength', BS_MAX_SOURCE_LENGTH);
    $license_block->getElementById('lol_drama-1')->extSetAttribute('maxlength', BS_MAX_LICENSE_LENGTH);

    for ($i = 2, $j = 3; $i <= BS_MAX_POST_FILES; ++ $i, ++ $j)
    {
        $temp_file_block = $file_block->cloneNode(true);
        $temp_file_block->changeId('form-file-' . $i);
        $temp_file_block->modifyAttribute('class', ' none', 'after');
        $temp_source_block = $source_block->cloneNode(true);
        $temp_source_block->changeId('form-sauce-' . $i);
        $temp_license_block = $license_block->cloneNode(true);
        $temp_license_block->changeId('form-lol_drama-' . $i);
        $insert_before_point = $dom->getElementById('form-fgsfds');
        $posting_form_table->insertBefore($temp_file_block, $insert_before_point);
        $posting_form_table->insertBefore($temp_source_block, $insert_before_point);
        $posting_form_table->insertBefore($temp_license_block, $insert_before_point);

        $for_label_file = $temp_file_block->doXPathQuery(".//label[@for='up-file-1']")->item(0);
        $for_label_file->extSetAttribute('for', 'up-file-' . $i);
        $file_num = $temp_file_block->getElementById('file-num-1');
        $file_num->setContent($i);
        $file_num->changeId('file-num-' . $i);
        $up_file_element = $temp_file_block->getElementById('up-file-1');
        $up_file_element->extSetAttribute('name', 'up_file_' . $i);
        $up_file_element->extSetAttribute('onchange', 'addMoarInput(\'form-file-' . $j . '\',false)');
        $up_file_element->changeId('up-file-' . $i);
        $add_source_element = $temp_file_block->getElementById('add-sauce-1');
        $add_source_element->extSetAttribute('onclick', 'addMoarInput(\'form-sauce-' . $i . '\',true)');
        $add_source_element->changeId('add-sauce-' . $i);
        $add_license_element = $temp_file_block->getElementById('add-lol_drama-1');
        $add_license_element->extSetAttribute('onclick', 'addMoarInput(\'form-lol_drama-' . $i . '\',true)');
        $add_license_element->changeId('add-lol_drama-' . $i);

        $for_label_sauce = $temp_source_block->doXPathQuery(".//label[@for='sauce-1']")->item(0);
        $for_label_sauce->extSetAttribute('for', 'sauce-' . $i);
        $source_element = $temp_source_block->getElementById('sauce-1');
        $source_element->extSetAttribute('name', 'new_post[file_info][file_' . $i . '][sauce]');
        $source_element->extSetAttribute('maxlength', BS_MAX_SOURCE_LENGTH);
        $source_element->changeId('sauce-' . $i);

        $for_label_license = $temp_license_block->doXPathQuery(".//label[@for='lol_drama-1']")->item(0);
        $for_label_license->extSetAttribute('for', 'lol_drama-' . $i);
        $license_element = $temp_license_block->getElementById('lol_drama-1');
        $license_element->extSetAttribute('name', 'new_post[file_info][file_' . $i . '][lol_drama]');
        $license_element->extSetAttribute('maxlength', BS_MAX_LICENSE_LENGTH);
        $license_element->changeId('lol_drama-' . $i);
    }

    $fgsfds_form = $dom->getElementById('form-fgsfds');

    if(!BS_USE_FGSFDS)
    {
        $dom->removeChild($fgsfds_form);
    }
    else
    {
        $fgsfds_label = $fgsfds_form->doXPathQuery(".//label[@for='fgsfds']")->item(0);
        $fgsfds_label->setContent(BS_FGSFDS_NAME);
    }

    if($response_id)
    {
        $dom->getElementById('which-post-mode')->setContent(nel_stext('TEXT_REPLYMODE'));
    }

    $rl = nel_cache_filetype_settings();
    $rules = $dom->importNode(nel_render_rules_list($rl), true);
    $dom->getElementById('form-rules-list')->appendChild($rules);

    if(!BS_USE_SPAMBOT_TRAP)
    {
        $dom->removeChild($dom->getElementById('form-trap1'));
        $dom->removeChild($dom->getElementById('form-trap2'));
    }

    nel_process_i18n($dom);
    $render->appendHTMLFromDOM($dom);
}