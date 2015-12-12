<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Error Handling
//
function derp($lang, $error_id, $error_message, $diagnostic)
{
    $file_count = 0;
    $extra_data = '';
    
    if (array_key_exists(1, $diagnostic) && !is_null($diagnostic))
    {
        $file_count = count($diagnostic[1]);
        
        foreach ($diagnostic[1] as $file)
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
    
    echo generate_header(array(), $lang, 'DERP', array());
    echo '
        <div class="text-center"><font color="blue" size="5">' . $lang['ERROR_HEADER'] . '<br><br>' . $error_message . '<br>' . $extra_data . '<br><a href="' . PHP_SELF2 . PHP_EXT . '">' . $lang['LINK_RETURN'] . '</a></b></font></div>
        <br><br><hr>
</body></html>';
    
    die();
}

function regen($dataforce, $authorized, $lang, $id, $mode, $modmode, $dbh)
{
    global $link_resno, $link_updates;
    
    if (!empty($_SESSION) && !$modmode)
    {
        $temp = $_SESSION['ignore_login'];
        $_SESSION['ignore_login'] = TRUE;
    }
    
    if ($mode === 'full')
    {
        $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE response_to=0 AND archive_status=0');
        $ids = $result->fetchAll(PDO::FETCH_COLUMN);
    }
    
    if ($mode === 'thread')
    {
        if (is_array($id))
        {
            $ids = $id;
        }
        else
        {
            $ids[0] = $id;
        }
    }
    
    if ($mode === 'main' || $mode === 'full')
    {
        update_archive_status($dataforce, $dbh);
        $dataforce['response_id'] = 0;
        $link_resno = 0;
        main_thread_generator($dataforce, $authorized, $lang, $dbh);
    }
    
    if ($mode === 'thread' || $mode === 'full')
    {
        $threads = count($ids);
        $i = 0;
        
        while ($i < $threads)
        {
            $dataforce['response_id'] = $ids[$i];
            thread_generator($dataforce, $authorized, $lang, $dbh);
            ++ $i;
        }
    }
    
    if ($mode === 'update_all_cache')
    {
        $dataforce['rules_list'] = cache_rules($lang, $dbh);
        cache_settings($dbh);
        $dataforce['post_links'] = $link_updates;
        // cache_post_links();
        regen_template_cache($lang);
    }
    
    if (!empty($_SESSION) && !$modmode)
    {
        $_SESSION['ignore_login'] = $temp;
    }
    
    $dataforce['post_links'] = $link_updates;
}

//
// The content this function presents must remain intact and be accessible to users
//
function about_screen($lang)
{
    echo generate_header(array(), $lang, 'ABOUT', array());
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
			<p class="text-center"><font size="4"><a href="' . PHP_SELF2 . PHP_EXT . '">' . $lang['LINK_RETURN'] . '</a></b></font></p>
			</div>
		</div>
		<br><br><hr>
</body></html>';
}

?>