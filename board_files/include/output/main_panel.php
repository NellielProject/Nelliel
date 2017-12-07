<?php

function nel_generate_main_panel($render)
{
    $render1 = new NellielTemplates\RenderCore();
    $dom = $render1->newDOMDocument();
    $render1->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    $dom->loadTemplateFromFile('management/main_panel.html');

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_config_access'))
    {
        $dom->removeChild($dom->getElementById('select-settings-panel'));
    }

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_ban_access'))
    {
        $dom->removeChild($dom->getElementById('select-ban-panel'));
    }

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_post_access'))
    {
        $dom->removeChild($dom->getElementById('select-thread-panel'));
    }

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_user_access') ||
         nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_role_access'))
    {
        $dom->removeChild($dom->getElementById('select-staff-panel'));
    }

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_modmode_access'))
    {
        $dom->removeChild($dom->getElementById('select-mod-mode'));
    }

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_regen_index'))
    {
        $dom->removeChild($dom->getElementById('regen-index-form'));
    }

    if (nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_regen_caches'))
    {
        $dom->removeChild($dom->getElementById('regen-index-form'));
    }

    nel_process_i18n($dom);
    $render->appendOutput($dom->outputHTML());
}