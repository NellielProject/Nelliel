<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type"  content="text/html;charset=utf-8">
    <meta name="robots" content="noarchive">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="{$render->get('dotdot')}{CSSDIR}nelliel.css" title="Nelliel">
    <link rel="alternate stylesheet" type="text/css" href="{$render->get('dotdot')}{CSSDIR}nigra.css" title="Nigra">
    <link rel="alternate stylesheet" type="text/css" href="{$render->get('dotdot')}{CSSDIR}futaba.css" title="Futaba">
    <link rel="alternate stylesheet" type="text/css" href="{$render->get('dotdot')}{CSSDIR}burichan.css" title="Burichan">
    <script type="text/javascript" src="{$render->get('dotdot')}{JSDIR}nel.js"></script>
    <script type="text/javascript">processCookie("style-{CONF_BOARD_DIR}");
        window.onload = function doImportantStuff()
        {
            externalLinks();
            fillForms("{CONF_BOARD_DIR}");
        }
    </script>
    <!--[if lt IE 9]>
        <script src="{$render->get('dotdot')}{BOARD_FILES}{JSDIR}html5shiv-printshiv.js"></script>
    <![endif]-->
    <title>{$render->get('page_title')}</title>
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
        {$render->get('log_out')}
        [<a href="{$render->get('dotdot')}{HOME}" rel="home">{nel_stext('LINK_HOME')}</a>]
        [<a href="{$render->get('dotdot')}{PHP_SELF}?mode=admin">{nel_stext('LINK_MANAGE')}</a>]
        [<a href="{$render->get('dotdot')}{PHP_SELF}?mode=about">{nel_stext('LINK_ABOUT')}</a>]
        </span>
    </div>
    <div class="logo">
        {$render->get('titlepart')}
    </div>
    <hr>
