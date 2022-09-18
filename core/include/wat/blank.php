<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

function nel_blank_page()
{
    echo '<!DOCTYPE html>
<html>
    <head>
        <title>This Page Intentionally Left Blank</title>
    </head>
    <body style="background-color: #EEEEEE;">
        <!-- TPLIB Project: https://web.archive.org/web/20060504184559/http://www.this-page-intentionally-left-blank.org/whythat.html -->
        <p style="text-align: center;">
            <a href="http://www.this-page-intentionally-left-blank.org/" title="TPILB Project" style="color: #000000; text-decoration: none;">This page intentionally left blank</a>
        </p>
    </body>
</html>';
    nel_clean_exit();
}