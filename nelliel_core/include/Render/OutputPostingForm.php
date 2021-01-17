<?php

namespace Nelliel\Render;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;

class OutputPostingForm extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $response_to = $parameters['response_to'];
        $this->render_data['allow_embeds'] = true; // TODO: Change this when we get a setting
        $this->render_data['response_to'] = $response_to;
        $this->render_data['form_action'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=threads&actions=new-post&board-id=' .
                $this->domain->id();
                $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;
                $this->render_data['is_staff'] = $this->session->inModmode($this->domain) && !$this->write_mode;
        $this->render_data['not_anonymous_maxlength'] = $this->domain->setting('max_name_length');
        $this->render_data['spam_target_maxlength'] = $this->domain->setting('max_email_length');
        $this->render_data['verb_maxlength'] = $this->domain->setting('max_subject_length');
        $this->render_data['forced_anonymous'] = $this->domain->setting('forced_anonymous');

        if (!$response_to)
        {
            $this->render_data['allow_files'] = $this->domain->setting('allow_files') &&
                    $this->domain->setting('allow_op_uploads');
            $this->render_data['allow_embeds'] = $this->domain->setting('allow_embeds') &&
                    $this->domain->setting('allow_op_uploads');
            $this->render_data['allow_multiple'] = $this->domain->setting('allow_op_multiple');
        }
        else
        {
            $this->render_data['allow_files'] = $this->domain->setting('allow_files') &&
                    $this->domain->setting('allow_reply_uploads');
            $this->render_data['allow_embeds'] = $this->domain->setting('allow_embeds') &&
                    $this->domain->setting('allow_reply_uploads');
            $this->render_data['allow_multiple'] = $this->domain->setting('allow_reply_multiple');
        }

        $this->render_data['embed_replaces'] = $this->domain->setting('embed_replaces_file');
        $this->render_data['spoilers_enabled'] = $this->domain->setting('enable_spoilers');
        $this->render_data['use_fgsfds'] = $this->domain->setting('use_fgsfds');
        $this->render_data['fgsfds_name'] = $this->domain->setting('fgsfds_name');
        $this->render_data['use_post_captcha'] = $this->domain->setting('use_post_captcha');
        $this->render_data['captcha_gen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'module=captcha&actions=get';
        $this->render_data['captcha_regen_url'] = NEL_MAIN_SCRIPT_QUERY_WEB_PATH .
                'module=captcha&actions=generate&no-display';
        $this->render_data['use_post_recaptcha'] = $this->domain->setting('use_post_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $this->render_data['captcha_label'] = true;
        $this->render_data['use_honeypot'] = $this->domain->setting('use_honeypot');
        $this->render_data['honeypot_field_name1'] = NEL_BASE_HONEYPOT_FIELD1 . '_' . $this->domain->id();
        $this->render_data['honeypot_field_name2'] = NEL_BASE_HONEYPOT_FIELD2 . '_' . $this->domain->id();
        $this->render_data['honeypot_field_name3'] = NEL_BASE_HONEYPOT_FIELD3 . '_' . $this->domain->id();
        $this->render_data['posting_mode'] = ($response_to) ? _gettext('Posting mode: Reply') : _gettext(
                'Posting mode: New thread');
        $this->postingRules();
        $output = $this->output('thread/posting_form', $data_only, true, $this->render_data);
        return $output;
    }

    private function postingRules()
    {
        $filetypes = new \Nelliel\FileTypes($this->domain->database());

        foreach ($filetypes->enabledTypes($this->domain->id()) as $type)
        {
            $supported_types = sprintf(_gettext('Supported %s file types: '), $type);
            $supported_formats = '';

            foreach ($filetypes->enabledFormats($this->domain->id(), $type) as $format)
            {
                $supported_formats .= utf8_strtoupper($format) . ', ';
            }

            if (empty($supported_formats))
            {
                continue;
            }

            $supported_types .= $supported_formats;
            $this->render_data['posting_rules_items'][]['rules_text'] = substr($supported_types, 0, -2);
        }

        $this->render_data['posting_rules_items'][]['rules_text'] = sprintf(
                _gettext('Maximum file size allowed is %dKB'), $this->domain->setting('max_filesize'));
        $this->render_data['posting_rules_items'][]['rules_text'] = sprintf(
                _gettext('Images greater than %d x %d pixels will be thumbnailed.'), $this->domain->setting('max_preview_width'),
                $this->domain->setting('max_preview_height'));
    }
}