<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_thread_generator($dataforce, $dbh)
{
    $render = new nel_render();
    $render_expand = new nel_render();
    $render_collapse = new nel_render();
    $gen_data['insert_hr'] = FALSE;
    $dataforce['dotdot'] = '';
    $write_id = ($dataforce['response_to'] === 0 || is_null($dataforce['response_to'])) ? $dataforce['response_id'] : $dataforce['response_to'];
    $result = $dbh->query('SELECT * FROM ' . POSTTABLE . ' WHERE post_number=' . $write_id . ' UNION SELECT * FROM ' . POSTTABLE . ' WHERE response_to=' . $write_id . ' ORDER BY post_number asc');
    $treeline = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);
    
    if (empty($treeline))
    {
        return;
    }
    
    if (empty($_SESSION) || $_SESSION['ignore_login'])
    {
        $dataforce['dotdot'] = '../../';
    }
    
    $gen_data['post_count'] = $treeline[0]['post_count'];
    $page = 1;
    $gen_data['post_counter'] = 0;
    $gen_data['expand_post'] = FALSE;
    $dataforce['omitted_done'] = TRUE;
    $partlimit = 1;
    $gen_data['first100'] = FALSE;
    
    while ($gen_data['post_counter'] < $gen_data['post_count'])
    {
        if ($treeline[$gen_data['post_counter']]['has_file'] == 1)
        {
            $gen_data['has_file'] = TRUE;
            $result = $dbh->query('SELECT * FROM ' . FILETABLE . ' WHERE post_ref=' . $treeline[$gen_data['post_counter']]['post_number'] . ' ORDER BY file_order asc');
            $gen_data['files'] = $result->fetchALL(PDO::FETCH_ASSOC);
            unset($result);
        }
        else
        {
            $gen_data['has_file'] = FALSE;
        }
        
        if ($gen_data['post_counter'] === 0)
        {
            $render->add_data('header_type', 'NORMAL');
            nel_render_header($dataforce, $render, $treeline);
            nel_render_posting_form($dataforce, $render);
        }
        
        if ($partlimit === 100)
        {
            $render_temp = clone $render;
            $gen_data['insert_hr'] = TRUE;
            nel_render_post($dataforce, $render_temp, FALSE, FALSE, $gen_data, $treeline, $dbh);
            $gen_data['insert_hr'] = FALSE;
            nel_render_footer($render_temp, FALSE, TRUE, TRUE, TRUE, FALSE);
            nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-0-100.html', $render_temp->output(), 0644);
            unset($render_temp);
        }
        
        if ($treeline[$gen_data['post_counter']]['response_to'] > 0)
        {
            if (!$treeline[$gen_data['post_counter']]['post_number'])
            {
                break;
            }
            
            $resid = $dataforce['response_id'];
            $render_temp = new nel_render();
            $render_temp2 = new nel_render();
            $render_temp3 = new nel_render();
            nel_render_post($dataforce, $render_temp, TRUE, FALSE, $gen_data, $treeline, $dbh); // for thread
            $dataforce['response_id'] = 0;
            nel_render_post($dataforce, $render_temp2, TRUE, TRUE, $gen_data, $treeline, $dbh); // for collapse
            nel_render_post($dataforce, $render_temp3, TRUE, TRUE, $gen_data, $treeline, $dbh); // for expand
            $dataforce['response_id'] = $resid;
            
            if ($gen_data['post_count'] > BS_ABBREVIATE_THREAD)
            {
                if ($render_collapse->output() === '')
                {
                    $dataforce['omitted_done'] = FALSE;
                    nel_render_post($dataforce, $render_temp2, TRUE, TRUE, $gen_data, $treeline, $dbh); // for collapse
                    $dataforce['omitted_done'] = TRUE;
                }
                
                if ($gen_data['post_counter'] > $gen_data['post_count'] - BS_ABBREVIATE_THREAD)
                {
                    $render_collapse->input($render_temp2->output());
                }
            }
            
            $render->input($render_temp->output());
            $render_expand->input($render_temp3->output());
        }
        else
        {
            nel_render_post($dataforce, $render, FALSE, FALSE, $gen_data, $treeline, $dbh);
        }
        
        ++ $partlimit;
        ++ $gen_data['post_counter'];
        unset($render_temp);
        unset($render_temp2);
        unset($render_temp3);
    }
    
    nel_render_footer($render, FALSE, TRUE, TRUE, TRUE, FALSE);
    
    if (!empty($_SESSION) && !$_SESSION['ignore_login'])
    {
        if ($dataforce['expand'])
        {
            $render_expand->output(TRUE);
        }
        else if ($dataforce['collapse'])
        {
            $render_collapse->output(TRUE);
        }
        else
        {
            $render->output(TRUE);
        }
        
        die();
    }
    else
    {
        nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '.html', $render->output(FALSE), 0644);
        nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-expand.html', $render_expand->output(FALSE), 0644);
        nel_write_file(PAGE_PATH . $write_id . '/' . $dataforce['response_id'] . '-collapse.html', $render_collapse->output(FALSE), 0644);
    }
}

?>