<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

function nel_special(): void
{
    switch ($_GET['special']) {
        case 'dawn':
            echo file_get_contents(NEL_WAT_FILES_PATH . 'dawn.jpg');
            break;
    }

    nel_clean_exit();
}
