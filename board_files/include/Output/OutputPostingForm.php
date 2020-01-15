<?php

namespace Nelliel\Output;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domain;

class OutputPostingForm extends OutputCore
{

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $this->domain->database();
        $this->selectRenderCore('mustache');
        $this->utilitySetup();
    }

    public function render(array $parameters, bool $data_only)
    {
        $render_data = array();
        $this->render_data['page_language'] = str_replace('_', '-', $this->domain->locale());
        $session = new \Nelliel\Session();
        $dotdot = $parameters['dotdot'];
        $response_to = $parameters['response_to'];
        $this->render_data['is_response'] = $response_to > 0;
        $this->render_data['response_to'] = $response_to;

        $this->startTimer();
        $this->render_data['form_action'] = $dotdot . MAIN_SCRIPT . '?module=threads&action=new-post&board_id=' .
                $this->domain->id();

        if ($response_to)
        {
            if ($session->inModmode($this->domain))
            {
                $return_url = $this->url_constructor->dynamic(MAIN_SCRIPT,
                        ['module' => 'render', 'action' => 'view-index', 'index' => '0',
                            'board_id' => $this->domain->id(), 'modmode' => 'true']);
            }
            else
            {
                $return_url = $dotdot . $this->domain->reference('board_directory') . '/' . MAIN_INDEX . PAGE_EXT;
            }

            $this->render_data['return_url'] = $return_url;
        }

        $this->render_data['is_staff'] = $session->inModmode($this->domain);
        $this->render_data['not_anonymous_maxlength'] = $this->domain->setting('max_name_length');
        $this->render_data['spam_target_maxlength'] = $this->domain->setting('max_email_length');
        $this->render_data['verb_maxlength'] = $this->domain->setting('max_subject_length');
        $this->render_data['force_anonymous'] = $this->domain->setting('force_anonymous');
        $max_files = 1;

        if ($this->domain->setting('allow_multifile'))
        {
            if ($response_to)
            {
                $max_files = $this->domain->setting('max_post_files');
            }
            else
            {
                if ($this->domain->setting('allow_op_multifile'))
                {
                    $max_files = $this->domain->setting('max_post_files');
                }
            }
        }

        // File Block
        for ($i = 1, $j = 2; $i <= $max_files; ++ $i, ++ $j)
        {
            $block_data = array();
            $block_data['hidden'] = ($i > 1) ? 'hidden' : '';
            $block_data['block_id'] = 'form-file-' . $i;
            $block_data['up_file_id'] = 'up-file-' . $i;
            $block_data['file_number'] = $i;
            $block_data['up_file_name'] = 'up_file_' . $i;
            $block_data['add_sauce'] = 'add-sauce-' . $i;
            $block_data['add_lol_drama'] = 'add-lol_drama-' . $i;
            $block_data['add_alt_text'] = 'add-alt_text-' . $i;
            $block_data['sauce_id'] = 'form-sauce-' . $i;
            $block_data['sauce_name'] = 'new_post[file_info][up_file_' . $i . '][sauce]';
            $block_data['sauce_maxlength'] = $this->domain->setting('max_source_length');
            $block_data['lol_drama_id'] = 'form-lol_drama-' . $i;
            $block_data['lol_drama_name'] = 'new_post[file_info][up_file_' . $i . '][lol_drama]';
            $block_data['lol_drama_maxlength'] = $this->domain->setting('max_license_length');
            $block_data['alt_text_id'] = 'form-alt_text-' . $i;
            $block_data['alt_text_name'] = 'new_post[file_info][up_file_' . $i . '][alt_text]';
            $block_data['alt_text_maxlength'] = '255';

            if ($this->domain->setting('enable_spoilers'))
            {
                $block_data['spoilers_enabled'] = true;
                $block_data['spoiler_id'] = 'form-spoiler-' . $i;
                $block_data['spoiler_name'] = 'new_post[file_info][up_file_' . $i . '][spoiler]';
            }
            else
            {
                $block_data['spoilers_enabled'] = false;
            }

            $this->render_data['file_blocks'][] = $block_data;
        }

        $this->render_data['use_fgsfds'] = $this->domain->setting('use_fgsfds');
        $this->render_data['fgsfds_name'] = $this->domain->setting('fgsfds_name');
        $this->render_data['use_captcha'] = $this->domain->setting('use_captcha');
        $this->render_data['captcha_gen_url'] = $dotdot . MAIN_SCRIPT . '?module=captcha&action=generate&board_id=' . $this->domain->id() . '&time=' . time();
        $this->render_data['captcha_width'] = 250;
        $this->render_data['captcha_height'] = 80;
        $this->render_data['use_recaptcha'] = $this->domain->setting('use_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->domain->setting('recaptcha_site_key');
        $this->render_data['use_honeypot'] = $this->domain->setting('use_honeypot');
        $this->render_data['honeypot_field_name1'] = BASE_HONEYPOT_FIELD1 . '_' . $this->domain->id();
        $this->render_data['honeypot_field_name2'] = BASE_HONEYPOT_FIELD2 . '_' . $this->domain->id();
        $this->render_data['honeypot_field_name3'] = BASE_HONEYPOT_FIELD3 . '_' . $this->domain->id();
        $this->render_data['posting_mode'] = ($response_to) ? _gettext('Posting mode: Reply') : _gettext(
                'Posting mode: New thread');
        $this->postingRules();
        $output = $this->output('thread/posting_form', $data_only, true);
        return $output;
    }

    private function postingRules()
    {
        $filetypes = new \Nelliel\FileTypes($this->domain->database());

        foreach ($filetypes->settings($this->domain->id()) as $type => $formats)
        {
            if (!$filetypes->typeIsEnabled($this->domain->id(), $type))
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
                $this->render_data['rules_list'][]['rules_text'] = sprintf(_gettext('Supported %s file types: '), $type) .
                        substr($list_set, 0, -2);
            }
        }

        $this->render_data['rules_list'][]['rules_text'] = sprintf(_gettext('Maximum file size allowed is %dKB'),
                $this->domain->setting('max_filesize'));
        $this->render_data['rules_list'][]['rules_text'] = sprintf(
                _gettext('Images greater than %d x %d pixels will be thumbnailed.'), $this->domain->setting('max_width'),
                $this->domain->setting('max_height'));
    }
}