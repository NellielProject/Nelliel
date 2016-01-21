<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_cleanse_the_aids($string)
{
    if ($string === '' || preg_match("#^\s*$#", $string))
    {
        return '';
    }
    else
    {
        if (get_magic_quotes_gpc())
        {
            $string = stripslashes($string);
        }

        $string = trim($string);
        $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
        return $string;
    }
}

function nel_word_filters($text)
{
    $cancer = array('', '');
    $chemo = array('', '');
    $total_cancer = count($cancer);

    for ($i = 0; $i < $total_cancer; ++ $i)
    {
        $text = preg_replace('#' . $cancer[$i] . '#', $chemo[$i], $text);
    }
    return $text;
}

function nel_newline_cleanup($string)
{
    if (nel_clear_whitespace($string) !== '')
    {
        $string = utf8_str_replace("\r", "\n", $string);

        if (utf8_substr_count($string, "\n") < BS_MAX_COMMENT_LINES)
        {
            $string = utf8_str_replace("\n\n", "<br>", $string);
            $string = utf8_str_replace("\n", "<br>", $string);
        }
        else
        {
            $string = utf8_str_replace("\n", "", $string); // \n is erased
        }
    }

    return $string;
}

function nel_clear_whitespace($string)
{
    if (ctype_space($string))
    {
        return '';
    }
}
?>