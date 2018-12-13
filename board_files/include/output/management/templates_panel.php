<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_templates_panel($user, $domain)
{
    if (!$user->boardPerm($domain->id(), 'perm_template_access'))
    {
        nel_derp(341, _gettext('You are not allowed to access the template panel.'));
    }

    $database = nel_database();
    $url_constructor = new \Nelliel\URLConstructor();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    nel_render_general_header($domain->renderInstance(), null, null,
            array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Template Management')));
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/templates_panel.html');

    $templates = $database->executeFetchAll(
            'SELECT * FROM "' . FRONT_END_TABLE . '" WHERE "resource_type" = \'template\' ORDER BY "entry" DESC',
            PDO::FETCH_ASSOC);

    $form_action = $url_constructor->dynamic(PHP_SELF,
            ['manage' => 'general', 'module' => 'templates', 'action' => 'add']);
    $dom->getElementById('add-template-form')->extSetAttribute('action', $form_action);

    $template_list = $dom->getElementById('template-list');
    $template_list_nodes = $template_list->getElementsByAttributeName('data-parse-id', true);
    $i = 0;
    $bgclass = 'row1';

    foreach ($templates as $template)
    {
        $bgclass = ($bgclass === 'row1') ? 'row2' : 'row1';
        $template_row = $dom->copyNode($template_list_nodes['template-row'], $template_list, 'append');
        $template_row->modifyAttribute('class', ' ' . $bgclass, 'after');
        $template_row_nodes = $template_row->getElementsByAttributeName('data-parse-id', true);
        $template_row_nodes['template-id']->setContent($template['id']);
        $template_row_nodes['template-name']->setContent($template['display_name']);
        $template_row_nodes['template-directory']->setContent($template['location']);
        $remove_link = $url_constructor->dynamic(PHP_SELF,
                ['manage' => 'general', 'module' => 'templates', 'action' => 'remove',
                    'template-id' => $template['id']]);
        $template_row_nodes['template-remove-link']->extSetAttribute('href', $remove_link);
        $i ++;
    }

    $template_list_nodes['template-row']->remove();
    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}