<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

require_once INCLUDE_PATH . 'output/rules.php';

function nel_render_posting_form(\Nelliel\Domain $domain, $response_to, $dotdot = null)
{
    $authorization = new \Nelliel\Auth\Authorization(nel_database());
    $translator = new \Nelliel\Language\Translator();
    $session = new \Nelliel\Session($authorization);
    $dom = $domain->renderInstance()->newDOMDocument();
    $site_domain = new \Nelliel\DomainSite(new \Nelliel\CacheHandler(), nel_database());
    $domain->renderInstance()->loadTemplateFromFile($dom, 'posting_form.html');
    $dotdot = (!empty($dotdot)) ? $dotdot : '';
    $url_constructor = new \Nelliel\URLConstructor();
    $posting_form = $dom->getElementById('posting-form');
    $posting_form->extSetAttribute('action',
            $dotdot . MAIN_SCRIPT . '?module=threads&action=new-post&board_id=' . $domain->id());
    $posting_form_input = $dom->getElementById('posting-form-input');
    $posting_form_nodes = $posting_form_input->getElementsByAttributeName('data-parse-id', true);

    if ($response_to)
    {
        if ($session->inModmode($domain) && !$domain->renderActive())
        {
            $return_url = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'render', 'action' => 'view-index', 'index' => '0', 'board_id' => $domain->id(),
                        'modmode' => 'true']);
        }
        else
        {
            $return_url = $dotdot . $domain->reference('board_directory') . '/' . MAIN_INDEX . PAGE_EXT;
        }

        $dom->getElementById('return-url')->extSetAttribute('href', $return_url);
    }
    else
    {
        $dom->getElementById('post-form-return-link')->remove();
    }

    $dom->getElementById('posting-form-responseto')->extSetAttribute('value', $response_to);

    if (!$session->inModmode($domain) || $domain->renderActive())
    {
        $posting_form_nodes['posting-form-staff']->remove();
    }

    $dom->getElementById('verb')->extSetAttribute('maxlength', $domain->setting('max_subject_length'));

    if ($domain->setting('force_anonymous'))
    {
        $posting_form_nodes['form-not-anonymous']->remove();
        $posting_form_nodes['form-spam-target']->remove();
    }
    else
    {
        $dom->getElementById('not-anonymous')->extSetAttribute('maxlength', $domain->setting('max_name_length'));
        $dom->getElementById('spam-target')->extSetAttribute('maxlength', $domain->setting('max_email_length'));
    }

    // File Block
    $posting_form_nodes['sauce']->extSetAttribute('maxlength', $domain->setting('max_source_length'));
    $posting_form_nodes['lol_drama']->extSetAttribute('maxlength', $domain->setting('max_license_length'));
    $posting_form_nodes['alt_text']->extSetAttribute('maxlength', '255');

    if ($domain->setting('allow_multifile') && $domain->setting('max_post_files') > 1)
    {
        for ($i = 2, $j = 3; $i <= $domain->setting('max_post_files'); ++ $i, ++ $j)
        {
            if (!$response_to && !$domain->setting('allow_op_multifile'))
            {
                break;
            }

            $temp_file_block = $posting_form_nodes['form-file']->cloneNode(true);
            $temp_file_block->modifyAttribute('class', ' hidden', 'after');
            $temp_file_block->changeId('form-file-' . $i);
            $temp_file_block_nodes = $temp_file_block->getElementsByAttributeName('data-parse-id', true);
            $temp_file_block_nodes['label-for-file']->extSetAttribute('for', 'up-file-' . $i);
            $temp_file_block_nodes['file-num']->setContent($i);
            $temp_file_block_nodes['up-file']->extSetAttribute('name', 'up_file_' . $i);
            $temp_file_block_nodes['up-file']->changeId('up-file-' . $i);
            $temp_file_block_nodes['add-sauce']->changeId('add-sauce-' . $i);
            $temp_file_block_nodes['add-lol_drama']->changeId('add-lol_drama-' . $i);
            $temp_file_block_nodes['add-alt_text']->changeId('add-alt_text-' . $i);
            $temp_source_block = $posting_form_nodes['form-sauce']->cloneNode(true);
            $temp_source_block->changeId('form-sauce-' . $i);
            $temp_source_block_nodes = $temp_source_block->getElementsByAttributeName('data-parse-id', true);
            $temp_source_block_nodes['sauce']->extSetAttribute('name', 'new_post[file_info][up_file_' . $i . '][sauce]');
            $temp_license_block = $posting_form_nodes['form-lol_drama']->cloneNode(true);
            $temp_license_block->changeId('form-lol_drama-' . $i);
            $temp_license_block_nodes = $temp_license_block->getElementsByAttributeName('data-parse-id', true);
            $temp_license_block_nodes['lol_drama']->extSetAttribute('name',
                    'new_post[file_info][up_file_' . $i . '][lol_drama]');
            $temp_alt_text_block = $posting_form_nodes['form-alt_text']->cloneNode(true);
            $temp_alt_text_block->changeId('form-alt_text-' . $i);
            $temp_alt_text_block_nodes = $temp_alt_text_block->getElementsByAttributeName('data-parse-id', true);
            $temp_alt_text_block_nodes['alt_text']->extSetAttribute('name',
                    'new_post[file_info][up_file_' . $i . '][alt_text]');
            $insert_before_point = $posting_form_nodes['form-fgsfds'];
            $posting_form_input->insertBefore($temp_file_block, $insert_before_point);
            $posting_form_input->insertBefore($temp_source_block, $insert_before_point);
            $posting_form_input->insertBefore($temp_license_block, $insert_before_point);
            $posting_form_input->insertBefore($temp_alt_text_block, $insert_before_point);
        }
    }

    if ($domain->setting('use_fgsfds'))
    {
        $posting_form_nodes['fgsfds-name']->setContent($domain->setting('fgsfds_name'));
    }
    else
    {
        $posting_form_nodes['form-fgsfds']->remove();
    }

    if (!$domain->setting('use_captcha'))
    {
        $posting_form_nodes['form-captcha']->remove();
    }
    else
    {
        $posting_form_nodes['captcha-image']->extSetAttribute('src', $dotdot . MAIN_SCRIPT . '?get-captcha');
    }

    if (!$domain->setting('use_recaptcha'))
    {
        $posting_form_nodes['form-recaptcha']->remove();
    }
    else
    {
        $posting_form_nodes['recaptcha-sitekey']->extSetAttribute('data-sitekey',
                $domain->setting('recaptcha_site_key'));
    }

    if ($domain->setting('use_honeypot'))
    {
        $posting_form_nodes['a-signature-box']->extSetAttribute('name', BASE_HONEYPOT_FIELD1 . '_' . $domain->id());
        $posting_form_nodes['a-signature-field']->extSetAttribute('name', BASE_HONEYPOT_FIELD2 . '_' . $domain->id());
        $posting_form_nodes['a-website-field']->extSetAttribute('name', BASE_HONEYPOT_FIELD3 . '_' . $domain->id());
    }
    else
    {
        $dom->getElementById('form-user-info-1')->remove();
        $dom->getElementById('form-user-info-2')->remove();
        $dom->getElementById('form-user-info-3')->remove();
    }

    if ($response_to)
    {
        $dom->getElementById('which-post-mode')->setContent('Posting mode: Reply');
    }

    $rules = $dom->importNode(nel_render_rules_list($domain), true);
    $posting_form->appendChild($rules);
    $translator->translateDom($dom, $domain->setting('language'));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
}