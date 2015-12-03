<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type"  content="text/html;charset=utf-8">
    <meta name="robots" content="noarchive">
    <link rel="stylesheet" type="text/css" href="{$rendervar['dotdot']}{CSSDIR}nelliel.css" title="Nelliel">
    <link rel="alternate stylesheet" type="text/css" href="{$rendervar['dotdot']}{CSSDIR}nigra.css" title="Nigra">
    <link rel="alternate stylesheet" type="text/css" href="{$rendervar['dotdot']}{CSSDIR}futaba.css" title="Futaba">
    <link rel="alternate stylesheet" type="text/css" href="{$rendervar['dotdot']}{CSSDIR}burichan.css" title="Burichan">
    <script type="text/javascript" src="{$rendervar['dotdot']}{JSDIR}nel.js"></script>
    <script type="text/javascript">processCookie("style-{CONF_BOARD_DIR}");
        window.onload = function doImportantStuff()
        {
            externalLinks();
            fillForms("{CONF_BOARD_DIR}");
        }
    </script>
    <!--[if lt IE 9]>
        <script src="{$rendervar['dotdot']}{BOARD_FILES}{JSDIR}html5shiv-printshiv.js"></script>
    <![endif]-->
    <title>{$rendervar['page_title']}</title>
</head>

<body>
    <div class="text-center">
        {S_SHORT_MENU}
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
        {$rendervar['log_out']}
        [<a href="{$rendervar['dotdot']}{HOME}" rel="home">{LANG_LINK_HOME}</a>]
        [<a href="{$rendervar['dotdot']}{PHP_SELF}?mode=admin">{LANG_LINK_MANAGE}</a>]
        [<a href="{$rendervar['dotdot']}{PHP_SELF}?mode=about">{LANG_LINK_ABOUT}</a>]
    </span>
    </div>
    
    <div class="logo">
        {$rendervar['titlepart']}
    </div>
    
    <hr>


