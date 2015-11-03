<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Clean up user input
//
function cleanse_the_aids($string)
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
        $string = htmlspecialchars($string);
        return $string;
    }
}

//
// Error Handling
//
function derp($error_id, $error_message, $diagnostic)
{
    $file_count = 0;
    $extra_data = '';

    if(array_key_exists(1, $diagnostic) && !is_null($diagnostic))
    {
        $file_count = count($diagnostic[1]);

        foreach($diagnostic[1] as $file)
        {
            if ($file !== '' && is_file($file['dest']))
            {
                unlink($file['dest']);
            }
        }
    }
    
    if ($error_location === 'SNACKS')
    {
        $extra_data = $diagnostic[2];
    }
    else if ($error_location === 'POST' && $file_count === 1)
    {
        $extra_data = $diagnostic[1]['basic_filename'] . $diagnostic[1]['ext'];
    }
    else
    {
        $extra_data = '';
    }
    
    echo generate_header(array(), 'DERP', array());
    echo '
        <div class="text-center"><font color="blue" size="5">' . LANG_ERROR_HEADER . '<br><br>' . $error_message . '<br>' . $extra_data . '<br><a href="' . PHP_SELF2 . PHP_EXT . '">' . LANG_LINK_RETURN . '</a></b></font></div>
        <br><br><hr>
</body></html>';
    
    die();
}

//
// End and delete session
//
function terminate_session()
{
    session_unset();
    session_destroy();
    setcookie("PHPSESSID", "", time() - 3600, "/");
}

//
// Regenerate session (Swiper no swiping!)
//
function regen_session()
{
    $timeout = time() - $_SESSION['last_activity'];
    
    if ($_COOKIE['PHPSESSID'] === session_id() && $timeout < 1800)
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
        $_SESSION['ignore_login'] = FALSE;
    }
    else // Session timed out or doesn't match the cookie
    {
        terminate_session();
        derp(105, LANG_ERROR_105, array('SEC'));
    }
}

//
// Parse quotelinks
//
function link_quote($matches)
{
    global $link_resno, $post_link_reference, $dbh;
    
    $found = FALSE;
    $back = ($link_resno === 0) ? PAGE_DIR : '../';
    $pattern = '#p' . $matches[1] . 't([0-9]+)#';
    $isquoted = preg_match($pattern, $post_link_reference, $matches2);
    
    if ($isquoted === 0)
    {
        $prepared = $dbh->prepare('SELECT response_to FROM ' . POSTTABLE . ' WHERE post_number=:pnum');
        $prepared->bindParam(':pnum', $matches[1], PDO::PARAM_STR);
        $prepared->execute();
        $link = $prepared->fetch(PDO::FETCH_NUM);
        unset($prepared);
        $found = TRUE;
        $post_link_reference .= 'p' . $matches[1] . 't' . $link[0];
        return '>>' . $matches[1];
    }
    else
    {
        $link = $matches2[1];
        
        if ($link[0] == '0')
        {
            return '<a href="' . $back . $matches[1] . '/' . $matches[1] . '.html" class="link_quote">>>' . $matches[1] . '</a>';
        }
        else
        {
            return '<a href="' . $back . $link . '/' . $link . '.html#' . $matches[1] . '" class="link_quote">>>' . $matches[1] . '</a>';
        }
    }
}

//
// Start/end timer
//
function lol_html_timer($derp)
{
    global $start_html, $end_html, $total_html;
    
    if ($derp === 0)
    {
        $start_html = 0;
        $end_html = 0;
        $total_html = 0;
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $start_html = $mtime[1] + $mtime[0];
        return;
    }
    else
    {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $end_html = $mtime[1] + $mtime[0];
        $total_html = round(($end_html - $start_html), 4);
        return;
    }
}

function regen($dataforce, $id, $mode, $modmode)
{
    global $dbh;
    
    if (!empty($_SESSION) && !$modmode)
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    if ($mode === 'full')
    {
        unset($GLOBALS['template_info']); // Make sure any template changes are included across the board
        $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE response_to=0 AND archive_status=0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
    }
    
    if ($mode === 'thread')
    {
        $ids = $id;
    }
    
    if ($mode === 'main' || $mode === 'full')
    {
        update_archive_status($dataforce);
        main_thread_generator($dataforce);
    }
    
    if ($mode === 'thread' || $mode === 'full')
    {
        $threads = count($ids);
        $i = 0;
        
        while ($i < $threads)
        {
            $dataforce['response_id'] = $ids[$i];
            thread_generator($dataforce);
            ++ $i;
        }
    }
    
    if ($mode === 'update_all_cache')
    {
        cache_rules();
        cache_settings();
        cache_post_links();
        regen_template_cache();
    }
    
    if (!empty($_SESSION) && !$modmode)
    {
        $_SESSION['ignore_login'] = $temp;
    }
}

//
// The content this function presents must remain intact and be accessible to users
//
function about_screen()
{
    echo generate_header(array(), 'ABOUT', array());
    echo '
        <div class="text-center">
        	<p><font color="blue" size="5">Nelliel Imageboard</font><br>
        	<font size="4">Version: ' . NELLIEL_VERSION . '</font></p>
			<p><font size="4" class="text-center">Copyright (c) 2010-2015, <a href="http://www.nelliel.com">Nelliel Project</a><br> 
			All rights reserved.</font></p>
			<div class="nelliel-license-div">
			<p><font size="4">Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:</font></p>
			<p><font size="4">1) Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.</font></p>
			<p><font size="4">2) Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation 
			and/or other materials provided with the distribution.</font></p>
			<p><font size="4">3) Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without 
			specific prior written permission.</font></p>
        	<img src="board_files/imagez/luna_canterlot_disclaimer.png" width="320" height="180" style="float: left; padding-right: 8px;">
			<p style="margin-left: 330px;"><font size="4">THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
			THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE 
			FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
			LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
			NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</font></p>
        	<hr>
        	<p><font size="4" >Default filetype icons are from the Soft Scraps pack made by <a href="http://deleket.deviantart.com/" title="Deleket">Deleket</a></font></p>
			<p class="text-center"><font size="4"><a href="' . PHP_SELF2 . PHP_EXT . '">' . LANG_LINK_RETURN . '</a></b></font></p>
			</div>
		</div>
		<br><br><hr>
</body></html>';
}

?>