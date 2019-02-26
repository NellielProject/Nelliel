<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;
use Nelliel\FileHandler;
use PDO;
use Nelliel\OutputFilter;

class OutputPostingForm extends OutputCore
{

    function __construct(Domain $domain, FileHandler $file_handler, OutputFilter $output_filter)
    {
        $this->domain = $domain;
        $this->file_handler = $file_handler;
        $this->output_filter = $output_filter;
    }

    public function render(array $parameters = array())
    {
        $this->prepare('posting_form.html');
        $authorization = new \Nelliel\Auth\Authorization(nel_database());
        nel_render_general_header($this->domain);
        $this->domain->renderActive(true);
        $session = new \Nelliel\Session($authorization);
        $dotdot = $parameters['dotdot'];
        $response_to = $parameters['response_to'];
        $url_constructor = new \Nelliel\URLConstructor();
        $posting_form = $this->dom->getElementById('posting-form');
        $posting_form->extSetAttribute('action',
                $dotdot . MAIN_SCRIPT . '?module=threads&action=new-post&board_id=' . $this->domain->id());
        $posting_form_input = $this->dom->getElementById('posting-form-input');
        $posting_form_nodes = $posting_form_input->getElementsByAttributeName('data-parse-id', true);

        if ($response_to)
        {
            if ($session->inModmode($this->domain) && !$this->domain->renderActive())
            {
                $return_url = $url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-index', 'index' => '0', 'board_id' => $this->domain->id(),
                        'modmode' => 'true']);
            }
            else
            {
                $return_url = $dotdot . $this->domain->reference('board_directory') . '/' . MAIN_INDEX . PAGE_EXT;
            }

            $this->dom->getElementById('return-url')->extSetAttribute('href', $return_url);
        }
        else
        {
            $this->dom->getElementById('post-form-return-link')->remove();
        }

        $this->dom->getElementById('posting-form-responseto')->extSetAttribute('value', $response_to);

        if (!$session->inModmode($this->domain) || $this->domain->renderActive())
        {
            $posting_form_nodes['posting-form-staff']->remove();
        }

        $this->dom->getElementById('verb')->extSetAttribute('maxlength', $this->domain->setting('max_subject_length'));

        if ($this->domain->setting('force_anonymous'))
        {
            $posting_form_nodes['form-not-anonymous']->remove();
            $posting_form_nodes['form-spam-target']->remove();
        }
        else
        {
            $this->dom->getElementById('not-anonymous')->extSetAttribute('maxlength', $this->domain->setting('max_name_length'));
            $this->dom->getElementById('spam-target')->extSetAttribute('maxlength', $this->domain->setting('max_email_length'));
        }

        // File Block
        $posting_form_nodes['sauce']->extSetAttribute('maxlength', $this->domain->setting('max_source_length'));
        $posting_form_nodes['lol_drama']->extSetAttribute('maxlength', $this->domain->setting('max_license_length'));
        $posting_form_nodes['alt_text']->extSetAttribute('maxlength', '255');

        if (!$this->domain->setting('enable_spoilers'))
        {
            $posting_form_nodes['form-spoiler']->remove();
        }

        if ($this->domain->setting('allow_multifile') && $this->domain->setting('max_post_files') > 1)
        {
            for ($i = 2, $j = 3; $i <= $this->domain->setting('max_post_files'); ++ $i, ++ $j)
            {
                if (!$response_to && !$this->domain->setting('allow_op_multifile'))
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

                if ($this->domain->setting('enable_spoilers'))
                {
                    $temp_spoiler_block = $posting_form_nodes['form-spoiler']->cloneNode(true);
                    $temp_spoiler_block->changeId('form-spoiler-' . $i);
                    $temp_spoiler_block_nodes = $temp_spoiler_block->getElementsByAttributeName('data-parse-id', true);
                    $temp_spoiler_block_nodes['spoiler-hidden']->extSetAttribute('name',
                            'new_post[file_info][up_file_' . $i . '][spoiler]');
                    $temp_spoiler_block_nodes['spoiler-checkbox']->extSetAttribute('name',
                            'new_post[file_info][up_file_' . $i . '][spoiler]');
                }

                $insert_before_point = $posting_form_nodes['form-fgsfds'];
                $posting_form_input->insertBefore($temp_file_block, $insert_before_point);
                $posting_form_input->insertBefore($temp_source_block, $insert_before_point);
                $posting_form_input->insertBefore($temp_license_block, $insert_before_point);
                $posting_form_input->insertBefore($temp_alt_text_block, $insert_before_point);
            }
        }

        if ($this->domain->setting('use_fgsfds'))
        {
            $posting_form_nodes['fgsfds-name']->setContent($this->domain->setting('fgsfds_name'));
        }
        else
        {
            $posting_form_nodes['form-fgsfds']->remove();
        }

        if (!$this->domain->setting('use_captcha'))
        {
            $posting_form_nodes['form-captcha']->remove();
        }
        else
        {
            $posting_form_nodes['captcha-image']->extSetAttribute('src', $dotdot . MAIN_SCRIPT . '?get-captcha');
        }

        if (!$this->domain->setting('use_recaptcha'))
        {
            $posting_form_nodes['form-recaptcha']->remove();
        }
        else
        {
            $posting_form_nodes['recaptcha-sitekey']->extSetAttribute('data-sitekey', $this->domain->setting(
                    'recaptcha_site_key'));
        }

        if ($this->domain->setting('use_honeypot'))
        {
            $posting_form_nodes['a-signature-box']->extSetAttribute('name', BASE_HONEYPOT_FIELD1 . '_' . $this->domain->id());
            $posting_form_nodes['a-signature-field']->extSetAttribute('name', BASE_HONEYPOT_FIELD2 . '_' . $this->domain->id());
            $posting_form_nodes['a-website-field']->extSetAttribute('name', BASE_HONEYPOT_FIELD3 . '_' . $this->domain->id());
        }
        else
        {
            $this->dom->getElementById('form-user-info-1')->remove();
            $this->dom->getElementById('form-user-info-2')->remove();
            $this->dom->getElementById('form-user-info-3')->remove();
        }

        if ($response_to)
        {
            $this->dom->getElementById('which-post-mode')->setContent('Posting mode: Reply');
        }

        $this->postingRules($posting_form);
        //$rules = $this->dom->importNode(nel_render_rules_list($this->domain), true);
        //$posting_form->appendChild($rules);
        $this->domain->translator()->translateDom($this->dom, $this->domain->setting('language'));
        $this->domain->renderInstance()->appendHTMLFromDOM($this->dom);
    }

    private function postingRules($posting_form)
    {
        $filetypes = new \Nelliel\FileTypes(nel_database());
        $form_rules_list = $this->dom->getElementById('form-rules-list');
        $rules_nodes = $form_rules_list->getElementsByAttributeName('data-parse-id', true);
        $base_list_item = $this->dom->createElement('li');
        $base_list_item->setAttributeNode($this->dom->createFullAttribute('class', 'rules-item'));
        $filetype_rules = $this->dom->copyNode($rules_nodes['rules-list'], $form_rules_list, 'append');

        foreach ($filetypes->settings($this->domain->id()) as $type => $formats)
        {
            if(!$filetypes->typeIsEnabled($this->domain->id(), $type))
            {
                continue;
            }

            $list_set = '';

            foreach ($formats as $name => $setting)
            {
                if ($name == $type || $setting === false)
                {
                    continue;
                }

                $list_set .= utf8_strtoupper($name) . ', ';
            }

            if ($list_set !== '')
            {
                $current_list_item = $this->dom->copyNode($base_list_item, $filetype_rules, 'append');
                $current_list_item->setContent(
                        sprintf(_gettext('Supported %s file types: '), $type) . substr($list_set, 0, -2));
                $filetype_rules->appendChild($current_list_item);
            }
        }

        $post_limits = $this->dom->copyNode($rules_nodes['rules-list'], $form_rules_list, 'append');
        $size_limit = $this->dom->copyNode($base_list_item, $post_limits, 'append');
        $size_limit->setContent(sprintf(_gettext('Maximum file size allowed is %dKB'), $this->domain->setting('max_filesize')));
        $thumbnail_limit = $this->dom->copyNode($base_list_item, $post_limits, 'append');
        $thumbnail_limit->setContent(
                sprintf(_gettext('Images greater than %d x %d pixels will be thumbnailed.'), $this->domain->setting('max_width'),
                        $this->domain->setting('max_height')));
        $rules_nodes['rules-list']->remove();
        $posting_form->appendChild($form_rules_list);
    }
}