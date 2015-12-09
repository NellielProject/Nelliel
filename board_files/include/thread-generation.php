<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function thread_generator(&$dataforce, $authorized, $dbh)
{
    global $rendervar;
    
    $rendervar['insert_hr'] = FALSE;
    $rendervar['dotdot'] = '';
    $write_id = ($dataforce['response_to'] === 0 || is_null($dataforce['response_to'])) ? $dataforce['response_id'] : $dataforce['response_to'];
    
    if (empty($_SESSION) || $_SESSION['ignore_login'])
    {
        $rendervar['dotdot'] = '../../';
    }
    
    $result = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE post_number=' . $write_id . ' UNION SELECT * FROM ' . POSTTABLE . ' WHERE response_to=' . $write_id . ' ORDER BY post_number asc');
    $treeline = $result->fetchALL(PDO::FETCH_ASSOC);
    unset($result);
    
    if (empty($treeline))
    {
        return;
    }
    
    $gen_data['post_count'] = $treeline[0]['post_count'];
    $page = 1;
    $gen_data['post_counter'] = 0;
    $page_output = '';
    $page_output_expand = '';
    $page_output_collapse = '';
    $dataforce['omitted_done'] = TRUE;
    $partlimit = 1;
    $rendervar['first100'] = FALSE;
    
    while ($gen_data['post_counter'] < $gen_data['post_count'])
    {
        if ($treeline[$gen_data['post_counter']]['has_file'] == 1)
        {
            $result = $dbh->query('SELECT * FROM ' . FILETABLE . ' WHERE post_ref=' . $treeline[$gen_data['post_counter']]['post_number'] . ' ORDER BY file_order asc');
            $rendervar['files'] = $result->fetchALL(PDO::FETCH_ASSOC);
            unset($result);
        }
        
        if ($gen_data['post_counter'] === 0)
        {
            $page_output .= generate_header($dataforce, 'NORMAL', $treeline);
            $page_output .= form($page_output, $dataforce, $authorized);
        }
        
        if ($partlimit === 100)
        {
            $page_output2 = $page_output;
            $rendervar['insert_hr'] = TRUE;
            $page_output2 .= render_post($dataforce, $authorized, FALSE, FALSE, $gen_data, $treeline, $dbh);
            $rendervar['insert_hr'] = FALSE;
            $page_output2 .= footer($authorized, FALSE, TRUE, TRUE, TRUE);
            write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-0-100.html', $page_output2, 0644);
        }
        
        if ($treeline[$gen_data['post_counter']]['response_to'] > 0)
        {
            if (!$treeline[$gen_data['post_counter']]['post_number'])
            {
                break;
            }
            
            $resid = $dataforce['response_id'];
            
            $page_output_tmp = render_post($dataforce, $authorized, TRUE, FALSE, $gen_data, $treeline, $dbh); // for thread
            $dataforce['response_id'] = 0;
            $page_output_tmp2 = render_post($dataforce, $authorized, TRUE, TRUE, $gen_data, $treeline, $dbh); // for collapse
            $page_output_tmp3 = render_post($dataforce, $authorized, TRUE, TRUE, $gen_data, $treeline, $dbh); // for expand
            $dataforce['response_id'] = $resid;
            
            if ($gen_data['post_count'] > BS_ABBREVIATE_THREAD)
            {
                if ($gen_data['post_counter'] > ($gen_data['post_count'] - BS_ABBREVIATE_THREAD))
                {
                    $page_output_collapse .= $page_output_tmp2;
                }
                
                if ($gen_data['post_counter'] === ($gen_data['post_count'] - BS_ABBREVIATE_THREAD))
                {
                    $dataforce['omitted_done'] = FALSE;
                    $page_output_tmp2 = render_post($dataforce, $authorized, TRUE, TRUE, $gen_data, $treeline, $dbh); // for collapse
                    $dataforce['omitted_done'] = TRUE;
                    $page_output_collapse = $page_output_tmp2;
                }
            }
            
            $page_output .= $page_output_tmp;
            $page_output_expand .= $page_output_tmp3;
        }
        else
        {
            $page_output .= render_post($dataforce, $authorized, FALSE, FALSE, $gen_data, $treeline, $dbh);
        }
        
        ++ $partlimit;
        ++ $gen_data['post_counter'];
    }
    
    $rendervar['main_page'] = FALSE;
    $page_output .= footer($authorized, FALSE, TRUE, TRUE, TRUE);
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        if ($dataforce['expand'])
        {
            echo $page_output_expand;
        }
        else if ($dataforce['collapse'])
        {
            echo $page_output_collapse;
        }
        else
        {
            echo $page_output;
        }
        
        die();
    }
    else
    {
        write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '.html', $page_output, 0644);
        write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-expand.html', $page_output_expand, 0644);
        write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-collapse.html', $page_output_collapse, 0644);
    }

}

?>