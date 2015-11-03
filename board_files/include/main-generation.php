<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Genrerates the main thread listings
//
function main_thread_generator($dataforce)
{
    global $rendervar, $dbh, $authorized;
    $rendervar['insert_hr'] = FALSE;
    $page_output = '';
    $rendervar['dotdot'] = '';
    
    $result = $dbh->query('SELECT post_number FROM ' . POSTTABLE . ' WHERE response_to=0 AND archive_status=0 ORDER BY sticky desc,last_update desc');
    $front_page_list = $result->fetchALL(PDO::FETCH_COLUMN);
    unset($result);
    $treeline = array(
            0 );
    
    // Finding the last entry number
    $result = $dbh->query('SELECT COUNT(post_number) FROM ' . POSTTABLE . ' WHERE response_to=0');
    $row = $result->fetch();
    unset($result);
    
    $counttree = count($front_page_list);
    
    // Special handling when there's no content
    if ($counttree === 0)
    {
        $page_output .= generate_header($dataforce, 'NORMAL', $treeline);
        $page_output .= form($page_output, $dataforce, $authorized);
        $rendervar['main_page'] = TRUE;
        $rendervar['prev_nav'] = '';
        $rendervar['next_nav'] = '';
        $rendervar['page_nav'] = '';
        $page_output .= footer($authorized, FALSE, TRUE, TRUE, FALSE);
        
        if (empty($_SESSION) || $_SESSION['ignore_login'])
        {
            write_file(PHP_SELF2 . PHP_EXT, $page_output, 0644);
        }
        else
        {
            echo $page_output;
            die();
        }
        return;
    }
    
    $thread_counter = 0;
    $page = 1;
    $gen_data['post_counter'] = -1;
    
    while ($thread_counter < $counttree)
    {
        $page_output = '';
        $dataforce['omitted_done'] = TRUE;
        $rendervar['page_title'] = BS_BOARD_NAME;
        $page_output .= generate_header($dataforce, 'NORMAL', $treeline);
        $page_output .= form($page_output, $dataforce, $authorized);
        $end_of_thread = FALSE;
        $sub_page_thread_counter = 0;
        
        $rendervar['first100'] = FALSE;
        while ($sub_page_thread_counter < BS_THREADS_PER_PAGE)
        {
            if ($gen_data['post_counter'] === -1)
            {
                $result = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE post_number=' . $front_page_list[$thread_counter] . '');
                $tree_op = $result->fetchALL(PDO::FETCH_ASSOC);
                unset($result);
                
                $result = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE response_to=' . $front_page_list[$thread_counter] . ' ORDER BY post_number desc LIMIT ' . (BS_ABBREVIATE_THREAD - 1) . '');
                $tree_replies = $result->fetchALL(PDO::FETCH_ASSOC);
                unset($result);
                
                $treeline = array_merge($tree_op, array_reverse($tree_replies));
                $gen_data['post_count'] = $treeline[0]['post_count'];
                $rendervar['expand_post'] = ($gen_data['post_count'] > BS_ABBREVIATE_THREAD) ? TRUE : FALSE;
                $rendervar['last50'] = ($gen_data['post_count'] > 50) ? TRUE : FALSE;
                $rendervar['first100'] = ($gen_data['post_count'] > 100) ? TRUE : FALSE;
            }
            
            if (!empty($treeline[$gen_data['post_counter']]) && !empty($treeline[$gen_data['post_counter'] + 1]))
            {
                ++ $gen_data['post_counter'];
            }
            else if ($gen_data['post_counter'] === -1)
            {
                $gen_data['post_counter'] = 0;
            }
            else
            {
                $end_of_thread = TRUE;
                $sub_page_thread_counter = ($thread_counter == $counttree - 1) ? BS_THREADS_PER_PAGE : ++ $sub_page_thread_counter;
                ++ $thread_counter;
                $rendervar['insert_hr'] = TRUE;
                $page_output .= renderPost($dataforce, $authorized, FALSE, FALSE, $gen_data, $treeline);
                $rendervar['insert_hr'] = FALSE;
            }
            
            if (!$end_of_thread)
            {
                if ($treeline[$gen_data['post_counter']]['has_file'] == 1)
                {
                    $result = $dbh->query('SELECT * FROM ' . FILETABLE . ' WHERE post_ref=' . $treeline[$gen_data['post_counter']]['post_number'] . ' ORDER BY file_order asc');
                    $rendervar['files'] = $result->fetchALL(PDO::FETCH_ASSOC);
                    unset($result);
                }
                
                if ($treeline[$gen_data['post_counter']]['response_to'] > 0)
                {
                    if (!$treeline[$gen_data['post_counter']]['post_number'])
                    {
                        break;
                    }
                    
                    if ($gen_data['post_count'] > BS_ABBREVIATE_THREAD && $gen_data['post_counter'] === 1)
                    {
                        $dataforce['omitted_done'] = FALSE;
                        $page_output .= renderPost($dataforce, $authorized, TRUE, TRUE, $gen_data, $treeline);
                        $dataforce['omitted_done'] = TRUE;
                    }
                    else
                    {
                        $page_output .= renderPost($dataforce, $authorized, TRUE, TRUE, $gen_data, $treeline);
                    }
                }
                else
                {
                    $page_output .= renderPost($dataforce, $authorized, FALSE, FALSE, $gen_data, $treeline);
                }
            }
            else
            {
                $end_of_thread = FALSE;
                $gen_data['post_counter'] = -1;
            }
        }
        
        // if not in res display mode
        $prev = $page - 1;
        $next = $page + 1;
        
        $rendervar['page_nav'] = ' ';
        $page_count = (int) ceil($counttree / BS_THREADS_PER_PAGE);
        $rendervar['main_page'] = TRUE;
        
        if ($page === 1)
        {
            $rendervar['prev_nav'] = 'Previous';
        }
        else if ($page === 2)
        {
            $rendervar['prev_nav'] = '<a href="' . PHP_SELF2 . PHP_EXT . '">Previous</a> ';
        }
        else
        {
            $rendervar['prev_nav'] = '<a href="' . PHP_SELF2 . ($page - 2) . PHP_EXT . '">Previous</a>';
        }
        
        $rendervar['next_nav'] = ($page === $page_count || $dataforce['max_pages'] === 1) ? 'Next' : '<a href="' . PHP_SELF2 . ($page) . PHP_EXT . '">Next</a>';
        $i = 0;
        
        while ($i < $page_count)
        {
            if ($i === 0)
            {
                $rendervar['page_nav'] .= ($page > 1) ? '[<a href="' . PHP_SELF2 . PHP_EXT . '">0</a>] ' : '[0] ';
            }
            else if ($i === ($page - 1) || $dataforce['max_pages'] === 1)
            {
                $rendervar['page_nav'] .= '[' . ($i) . '] ';
            }
            else
            {
                $rendervar['page_nav'] .= '[<a href="' . PHP_SELF2 . ($i) . PHP_EXT . '">' . ($i) . '</a>] ';
            }
            
            ++ $i;
        }
        
        $page_output .= footer($authorized, FALSE, TRUE, TRUE, FALSE);
        
        if (!empty($_SESSION) && !$_SESSION['ignore_login'])
        {
            
            if ($page >= $dataforce['current_page'])
            {
                $page = $counttree;
            }
            
            echo $page_output;
            die();
        }
        else
        {
            $logfilename = ($page === 1) ? PHP_SELF2 . PHP_EXT : PHP_SELF2 . ($page - 1) . PHP_EXT;
            write_file($logfilename, $page_output, 0644);
        }
        
        ++ $page;
    }
}

?>