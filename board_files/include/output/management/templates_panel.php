<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_templates_panel($user, $domain)
{
    if (!$user->boardPerm($domain->id(), 'perm_templates_access'))
    {
        nel_derp(341, _gettext('You are not allowed to access the templates panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Templates')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/templates_panel.html');
    $ini_parser = new \Nelliel\INIParser(new \Nelliel\FileHandler());
    $template_inis = $ini_parser->parseDirectories(TEMPLATE_PATH, 'template_info.ini');

    $templates = $database->executeFetchAll('SELECT * FROM "' . TEMPLATE_TABLE . '" ORDER BY "entry" DESC',
            PDO::FETCH_ASSOC);
    $installed_ids = array();
    $default_template_id = '';
    $installed_template_list = $dom->getElementById('installed-template-list');
    $installed_template_list_nodes = $installed_template_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($templates as $template)
    {
        if ($template['is_default'] == 1)
        {
            $default_template_id = $template['id'];
        }

        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $installed_ids[] = $template['id'];
        $template_row = $dom->copyNode($installed_template_list_nodes['template-row'], $installed_template_list,
                'append');
        $template_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
        $template_row_nodes['template-id']->setContent($template['id']);
        $template_row_nodes['template-name']->setContent($template['name']);
        $template_row_nodes['template-directory']->setContent($template['directory']);
        $template_row_nodes['template-output']->setContent($template['output_type']);

        if ($template['is_default'] == 1)
        {
            $template_row_nodes['template-default-link']->remove();
            $template_row_nodes['template-remove-link']->remove();
            $template_row_nodes['template-action-1']->setContent(_gettext('Default Template'));
        }
        else
        {
            $default_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'general', 'module' => 'templates', 'action' => 'make-default',
                        'template-id' => $template['id']]);
            $template_row_nodes['template-default-link']->extSetAttribute('href', $default_link);
            $remove_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'general', 'module' => 'templates', 'action' => 'remove',
                        'template-id' => $template['id']]);
            $template_row_nodes['template-remove-link']->extSetAttribute('href', $remove_link);
        }
    }

    $installed_template_list_nodes['template-row']->remove();

    $available_template_list = $dom->getElementById('available-template-list');
    $available_template_list_nodes = $available_template_list->getElementsByAttributeName('data-parse-id', true);
    $bgclass = 'row1';

    foreach ($template_inis as $template)
    {
        if ($template['id'] === $default_template_id)
        {
            continue;
        }

        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $template_row = $dom->copyNode($available_template_list_nodes['template-row'], $available_template_list,
                'append');
        $template_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
        $template_row_nodes['template-id']->setContent($template['id']);
        $template_row_nodes['template-name']->setContent($template['name']);
        $template_row_nodes['template-directory']->setContent($template['directory']);
        $template_row_nodes['template-output']->setContent($template['output_type']);

        if (in_array($template['id'], $installed_ids))
        {
            $template_row_nodes['template-install-link']->remove();
            $template_row_nodes['template-action-1']->setContent(_gettext('Template Installed'));
        }
        else
        {
            $remove_link = $url_constructor->dynamic(PHP_SELF,
                    ['manage' => 'general', 'module' => 'templates', 'action' => 'add',
                        'template-id' => $template['id']]);
            $template_row_nodes['template-install-link']->extSetAttribute('href', $remove_link);
        }
    }

    $available_template_list_nodes['template-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}