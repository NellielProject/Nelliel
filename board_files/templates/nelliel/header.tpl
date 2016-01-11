<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type"  content="text/html;charset=utf-8">
    <meta name="robots" content="noarchive">
    <link rel="stylesheet" type="text/css" href="{$render->retrieve_data('dotdot')}{CSSDIR}nelliel.css" title="Nelliel">
    <link rel="alternate stylesheet" type="text/css" href="{$render->retrieve_data('dotdot')}{CSSDIR}nigra.css" title="Nigra">
    <link rel="alternate stylesheet" type="text/css" href="{$render->retrieve_data('dotdot')}{CSSDIR}futaba.css" title="Futaba">
    <link rel="alternate stylesheet" type="text/css" href="{$render->retrieve_data('dotdot')}{CSSDIR}burichan.css" title="Burichan">
    <script type="text/javascript" src="{$render->retrieve_data('dotdot')}{JSDIR}nel.js"></script>
    <script type="text/javascript">processCookie("style-{CONF_BOARD_DIR}");
        window.onload = function doImportantStuff()
        {
            externalLinks();
            fillForms("{CONF_BOARD_DIR}");
        }
    </script>
    <!--[if lt IE 9]>
        <script src="{$render->retrieve_data('dotdot')}{BOARD_FILES}{JSDIR}html5shiv-printshiv.js"></script>
    <![endif]-->
    <title>{$render->retrieve_data('page_title')}</title>
</head>
<body>
    <div class="text-center">
        {nel_stext('S_SHORT_MENU')}
    </div>
    <div>
        <span class="top_styles">
        Styles:
        [<a href="#" onclick="changeCSS('Nelliel','style-{CONF_BOARD_DIR}'); return false;">Nelliel</a>]
        [<a href="#" onclick="changeCSS('Futaba','style-{CONF_BOARD_DIR}'); return false;">Futaba</a>]
        [<a href="#" onclick="changeCSS('Burichan','style-{CONF_BOARD_DIR}'); return false;">Burichan</a>]
        [<a href="#" onclick="changeCSS('Nigra','style-{CONF_BOARD_DIR}'); return false;">Nigra</a>]
        </span>
        <span class="admin-bar">
        {$render->retrieve_data('log_out')}
        [<a href="{$render->retrieve_data('dotdot')}{HOME}" rel="home">{nel_stext('LINK_HOME')}</a>]
        [<a href="{$render->retrieve_data('dotdot')}{PHP_SELF}?mode=admin">{nel_stext('LINK_MANAGE')}</a>]
        [<a href="{$render->retrieve_data('dotdot')}{PHP_SELF}?mode=about">{nel_stext('LINK_ABOUT')}</a>]
        </span>
    </div>
    <div class="logo">
        {$render->retrieve_data('titlepart')}
    </div>
    <hr>
