<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_site_settings_panel()
{
    $dbh = nel_database();
    $render = new NellielTemplates\RenderCore();
    $render->startRenderTimer();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_general_header($render, null, null,
            array('header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')));
    $dom = $render->newDOMDocument();
    $render->loadTemplateFromFile($dom, 'management/site_settings_panel.html');
    $dom->getElementById('site-settings-form')->extSetAttribute('action',
            PHP_SELF . '?manage=general&module=site-settings&action=update');
    $result = $dbh->query('SELECT * FROM "nelliel_site_config"');
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    foreach ($rows as $config_line)
    {
        if ($config_line['data_type'] === 'bool')
        {
            $config_element = $dom->getElementById($config_line['config_name']);

            if (!is_null($config_element) && $config_line['setting'] == 1)
            {
                $config_element->extSetAttribute('checked', 'true');
            }
        }
        else
        {
            $config_element = $dom->getElementById($config_line['config_name']);

            if (!is_null($config_element))
            {
                $config_element->extSetAttribute('value', $config_line['setting']);
            }
        }
    }

    nel_language()->i18nDom($dom);
    $render->appendHTMLFromDOM($dom);
    nel_render_general_footer($render);
    echo $render->outputRenderSet();
    nel_clean_exit();
}