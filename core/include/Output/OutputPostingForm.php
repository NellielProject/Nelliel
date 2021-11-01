<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

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
        $this->render_data['in_modmode'] = $this->session->inModmode($this->domain) && !$this->write_mode;
        $this->render_data['not_anonymous_minlength'] = $this->domain->setting('min_name_length');
        $this->render_data['not_anonymous_maxlength'] = $this->domain->setting('max_name_length');
        $this->render_data['spam_target_minlength'] = $this->domain->setting('min_email_length');
        $this->render_data['spam_target_maxlength'] = $this->domain->setting('max_email_length');
        $this->render_data['verb_minlength'] = $this->domain->setting('min_subject_length');
        $this->render_data['verb_maxlength'] = $this->domain->setting('max_subject_length');
        $this->render_data['forced_anonymous'] = $this->domain->setting('forced_anonymous');
        $this->render_data['display_name_field'] = $this->domain->setting('enable_name_field');
        $this->render_data['display_email_field'] = $this->domain->setting('enable_email_field');
        $this->render_data['display_subject_field'] = $this->domain->setting('enable_subject_field');
        $this->render_data['display_comment_field'] = $this->domain->setting('enable_comment_field');
        $this->render_data['display_fgsfds_field'] = $this->domain->setting('enable_fgsfds_field');
        $this->render_data['display_password_field'] = $this->domain->setting('enable_password_field');
        $this->render_data['require_name'] = $this->domain->setting('require_name') ? 'required' : '';
        $this->render_data['require_email'] = $this->domain->setting('require_email') ? 'required' : '';
        $this->render_data['require_subject'] = $this->domain->setting('require_subject') ? 'required' : '';
        $this->render_data['require_comment'] = $this->domain->setting('require_comment') ? 'required' : '';
        $this->render_data['name_field_placeholder'] = $this->domain->setting('name_field_placeholder');
        $this->render_data['email_field_placeholder'] = $this->domain->setting('email_field_placeholder');
        $this->render_data['subject_field_placeholder'] = $this->domain->setting('subject_field_placeholder');
        $this->render_data['comment_field_placeholder'] = $this->domain->setting('comment_field_placeholder');
        $this->render_data['fgsfds_field_placeholder'] = $this->domain->setting('fgsfds_field_placeholder');
        $this->render_data['password_field_placeholder'] = $this->domain->setting('password_field_placeholder');

        if ($this->render_data['in_modmode']) {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'new-post'], false,
                'modmode');
            $this->render_data['flags']['post_as_staff'] = $this->session->user()->checkPermission($this->domain,
                'perm_post_as_staff');
            $this->render_data['flags']['raw_html'] = $this->session->user()->checkPermission($this->domain,
                'perm_raw_html');
        } else {
            $this->render_data['form_action'] = nel_build_router_url([$this->domain->id(), 'new-post']);
        }

        if (!$response_to) {
            $this->render_data['allow_files'] = $this->domain->setting('allow_op_files');
            $this->render_data['file_required'] = $this->domain->setting('require_op_file');
            $this->render_data['allow_embeds'] = $this->domain->setting('allow_op_embeds');
            $this->render_data['embed_required'] = $this->domain->setting('require_op_embed');
            $max_files = intval($this->domain->setting('max_op_files'));
            $max_embeds = intval($this->domain->setting('max_op_embeds'));
        } else {
            $this->render_data['allow_files'] = $this->domain->setting('allow_reply_files');
            $this->render_data['file_required'] = $this->domain->setting('require_reply_file');
            $this->render_data['allow_embeds'] = $this->domain->setting('allow_reply_embeds');
            $this->render_data['embed_required'] = $this->domain->setting('require_reply_embed');
            $max_files = intval($this->domain->setting('max_reply_files'));
            $max_embeds = intval($this->domain->setting('max_reply_embeds'));
        }

        if ($this->domain->setting('use_fgsfds_menu')) {
            $output_menu = new OutputMenu($this->domain, $this->write_mode);
            $this->render_data['use_fgsfds_menu'] = true;
            $this->render_data['fgsfds_options'] = $output_menu->fgsfds([], true);
        }

        $this->render_data['allowed_files'] = array_fill(0, $max_files, '');
        $this->render_data['allowed_embeds'] = array_fill(0, $max_embeds, '');
        $this->render_data['file_max_message'] = sprintf(_gettext('Maximum files: %d'), $max_files);
        $this->render_data['embed_max_message'] = sprintf(_gettext('Maximum embeds: %d'), $max_embeds);
        $this->render_data['embed_replaces'] = $this->domain->setting('embed_replaces_file');
        $this->render_data['spoilers_enabled'] = $this->domain->setting('enable_spoilers');
        $this->render_data['fgsfds_name'] = $this->domain->setting('fgsfds_name');
        $this->render_data['use_post_captcha'] = $this->domain->setting('use_post_captcha');
        $this->render_data['captcha_gen_url'] = nel_build_router_url([Domain::SITE, 'captcha', 'get']);
        $this->render_data['captcha_regen_url'] = nel_build_router_url([Domain::SITE, 'captcha', 'regenerate']);
        $this->render_data['use_post_recaptcha'] = $this->domain->setting('use_post_recaptcha');
        $this->render_data['recaptcha_sitekey'] = $this->site_domain->setting('recaptcha_site_key');
        $this->render_data['captcha_label'] = true;
        $this->render_data['use_honeypot'] = $this->domain->setting('use_honeypot');
        $this->render_data['honeypot_field_name1'] = NEL_BASE_HONEYPOT_FIELD1 . '_' . $this->domain->id();
        $this->render_data['honeypot_field_name2'] = NEL_BASE_HONEYPOT_FIELD2 . '_' . $this->domain->id();
        $this->render_data['honeypot_field_name3'] = NEL_BASE_HONEYPOT_FIELD3 . '_' . $this->domain->id();
        $this->render_data['posting_submit'] = ($response_to) ? _gettext('Reply') : _gettext('New thread');
        $this->postingRules();
        $output = $this->output('thread/posting_form', $data_only, true, $this->render_data);
        return $output;
    }

    private function postingRules()
    {
        $filetypes = new \Nelliel\FileTypes($this->domain->database());

        foreach ($filetypes->enabledCategories($this->domain->id()) as $category) {
            $supported_types = sprintf(_gettext('Supported %s file types: '), $category);
            $supported = '';
            $joiner = '';

            foreach ($filetypes->enabledFormats($this->domain->id(), $category) as $format) {
                $extensions = '';
                $add = '';

                if ($this->domain->setting('list_file_extensions')) {
                    $joiner = ', ';

                    foreach ($filetypes->formatExtensions($format) as $extension) {
                        $extensions .= $extension . ', ';
                    }

                    $extensions = substr($extensions, 0, -2);
                }

                $add = $extensions;

                if ($this->domain->setting('list_file_formats')) {
                    $joiner = ', ';

                    if ($extensions !== '') {
                        $extensions = '(' . $extensions . ')';
                    }

                    $add = utf8_strtoupper($format) . '' . $extensions;
                }

                $supported .= $add . $joiner;
            }

            if (empty($supported)) {
                continue;
            }

            $supported_types .= $supported;
            $this->render_data['posting_rules_items'][]['rules_text'] = substr($supported_types, 0,
                -utf8_strlen($joiner));
        }

        $this->render_data['posting_rules_items'][]['rules_text'] = sprintf(
            _gettext('Maximum file size allowed is %dKB'), $this->domain->setting('max_filesize') / 1024);
        $this->render_data['posting_rules_items'][]['rules_text'] = sprintf(
            _gettext('Images greater than %d x %d pixels will be thumbnailed.'),
            $this->domain->setting('max_preview_width'), $this->domain->setting('max_preview_height'));
    }
}