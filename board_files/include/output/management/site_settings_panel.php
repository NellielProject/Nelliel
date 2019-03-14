<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_site_settings_panel(\Nelliel\Domain $domain, $user)
{
    if (!$user->domainPermission($domain, 'perm_site_config_access'))
    {
        nel_derp(360, _gettext('You are not allowed to access the site settings.'));
    }

    $database = $domain->database();
    $translator = new \Nelliel\Language\Translator();
    $domain->renderInstance()->startRenderTimer();
    $output_header = new \Nelliel\Output\OutputHeader($domain);
    $extra_data = ['header' => _gettext('General Management'), 'sub_header' => _gettext('Site Settings')];
    $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/site_settings_panel.html');
    $dom->getElementById('site-settings-form')->extSetAttribute('action',
            MAIN_SCRIPT . '?module=site-settings&action=update');
    $result = $database->query('SELECT * FROM "' . SITE_CONFIG_TABLE . '"');
    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    foreach ($rows as $config_line)
    {
        $config_element = $dom->getElementById($config_line['config_name']);

        if (is_null($config_element))
        {
            continue;
        }

        if ($config_line['data_type'] === 'boolean')
        {
            if ($config_line['setting'] == 1)
            {
                $config_element->extSetAttribute('checked', 'true');
            }
        }
        else
        {
            $config_element->extSetAttribute('value', $config_line['setting']);
        }
    }

    $translator->translateDom($dom);
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}