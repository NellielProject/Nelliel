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
        $session = new \Nelliel\Account\Session();
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
        $uploads_data = array();
        $uploads_data['allow_multiple'] = false;

        if ($this->domain->setting('allow_multifile'))
        {
            if ($response_to)
            {
                $uploads_data['allow_multiple'] = $this->domain->setting('max_post_files');
            }
            else
            {
                if ($this->domain->setting('allow_op_multifile'))
                {
                    $uploads_data['allow_multiple'] = $this->domain->setting('max_post_files');
                }
            }
        }

        $uploads_data['spoilers_enabled'] = $this->domain->setting('enable_spoilers');
        $this->render_data['file_uploads'] = $uploads_data;

        $this->render_data['use_fgsfds'] = $this->domain->setting('use_fgsfds');
        $this->render_data['fgsfds_name'] = $this->domain->setting('fgsfds_name');
        $this->render_data['use_post_captcha'] = $this->domain->setting('use_post_captcha');
        $this->render_data['captcha_gen_url'] = $dotdot . MAIN_SCRIPT . '?module=captcha&action=get';
        $this->render_data['captcha_regen_url'] = $dotdot . MAIN_SCRIPT . '?module=captcha&action=regenerate';
        $this->render_data['use_post_recaptcha'] = $this->domain->setting('use_post_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
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

            if ($list_set !== '_site_')
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