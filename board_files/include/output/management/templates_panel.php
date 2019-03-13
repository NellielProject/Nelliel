<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_templates_panel($user, \Nelliel\Domain $domain)
{
    if (!$user->domainPermission($domain, 'perm_templates_access'))
    {
        nel_derp(341, _gettext('You are not allowed to access the templates panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain, nel_database());
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/templates_panel.html');
    $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
    $template_inis = $ini_parser->parseDirectories(TEMPLATES_FILE_PATH, 'template_info.ini');

    $templates = $database->executeFetchAll(
            'SELECT * FROM "' . TEMPLATES_TABLE . '" ORDER BY "entry" ASC, "is_default" DESC', PDO::FETCH_ASSOC);
    $installed_ids = array();
    $installed_template_list = $dom->getElementById('installed-template-list');
    $installed_template_list_nodes = $installed_template_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($templates as $template)
    {
        $template_info = json_decode($template['info'], true);
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $installed_ids[] = $template['id'];
        $template_row = $dom->copyNode($installed_template_list_nodes['template-row'], $installed_template_list,
                'append');
        $template_row->extSetAttribute('class', $bgclass);
        $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
        $template_row_nodes['id']->setContent($template['id']);
        $template_row_nodes['name']->setContent($template_info['name']);
        $template_row_nodes['directory']->setContent($template_info['directory']);
        $template_row_nodes['output']->setContent($template_info['output_type']);

        if ($template['is_default'] == 1)
        {
            $template_row_nodes['default-link']->remove();
            $template_row_nodes['remove-link']->remove();
            $template_row_nodes['action-1']->setContent(_gettext('Default Template'));
        }
        else
        {
            $default_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'make-default', 'template-id' => $template['id']]);
            $template_row_nodes['template-default-link']->extSetAttribute('href', $default_link);
            $remove_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'remove', 'template-id' => $template['id']]);
            $template_row_nodes['remove-link']->extSetAttribute('href', $remove_link);
        }
    }

    $installed_template_list_nodes['template-row']->remove();

    $available_template_list = $dom->getElementById('available-template-list');
    $available_template_list_nodes = $available_template_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($template_inis as $template)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $template_row = $dom->copyNode($available_template_list_nodes['template-row'], $available_template_list,
                'append');
        $template_row->extSetAttribute('class', $bgclass);
        $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
        $template_row_nodes['id']->setContent($template['id']);
        $template_row_nodes['name']->setContent($template['name']);
        $template_row_nodes['directory']->setContent($template_info['directory']);
        $template_row_nodes['output']->setContent($template['output_type']);

        if (in_array($template['id'], $installed_ids))
        {
            $template_row_nodes['install-link']->remove();
            $template_row_nodes['action-1']->setContent(_gettext('Template Installed'));
        }
        else
        {
            $remove_link = $url_constructor->dynamic(MAIN_SCRIPT,
                    ['module' => 'templates', 'action' => 'add', 'template-id' => $template['id']]);
            $template_row_nodes['install-link']->extSetAttribute('href', $remove_link);
        }
    }

    $available_template_list_nodes['template-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}