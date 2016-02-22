<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_ban_panel_top($dataforce, $render)
{
    $render->parse('bans_panel_top.tpl', 'management');
}

function nel_render_ban_panel_list($dataforce, $dbh)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    nel_render_ban_panel_top($dataforce, $render);
    $result = $dbh->query('SELECT * FROM ' . BAN_TABLE . ' ORDER BY id DESC');
    $bgclass = 'row1';
    
    while ($baninfo = $result->fetch(PDO::FETCH_ASSOC))
    {
        $render->add_data('ban_panel_loop', TRUE);
        $render->add_data('host', (@inet_ntop($render->retrieve_data('host'))) ? inet_ntop($render->retrieve_data('host')) : 'Unknown');
        $render->add_data('ban_appeal_response', $baninfo['appeal_response']);
        $render->add_data('ban_expire', date("D F jS Y  H:i:s", $render->retrieve_data('length') + $render->retrieve_data('ban_time')));
        
        if ($bgclass === 'row1')
        {
            $render->add_data('bg_class', 'row2');
            $bgclass = 'row2';
        }
        else
        {
            $render->add_data('bg_class', 'row1');
            $bgclass = 'row1';
        }
        
        $render->parse('bans_panel_list_bans.tpl', 'management');
    }
    
    $result->closeCursor();
    
    nel_render_ban_panel_bottom($dataforce, $render);
    nel_render_basic_footer($render);
    $render->output(TRUE);
}

function nel_render_ban_panel_add($dataforce)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $render->parse('bans_panel_add_ban.tpl', 'management');
    nel_render_basic_footer($render);
    $render->output(TRUE);
}

function nel_render_ban_panel_modify($dataforce, $dbh)
{
    $render = new nel_render();
    nel_render_header($dataforce, $render, array());
    $result = $dbh->query('SELECT * FROM ' . BAN_TABLE . ' WHERE id=' . $dataforce['banid'] . '');
    $baninfo = $result->fetch(PDO::FETCH_ASSOC);
    $result->closeCursor();
    $render->add_data('appeal_check', '');
    $render->add_data('ban_expire', date("D F jS Y  H:i:s", $bandinfo['length'] + $baninfo['ban_time']));
    $render->add_data('ban_time', date("D F jS Y  H:i:s", $baninfo['ban_time']));
    $length2 = $baninfo['length'] / 3600;
    $render->add_data('ban_length_hours', 0);
    $render->add_data('ban_length_days', 0);
    
    if ($length2 >= 24)
    {
        $length2 = $length2 / 24;
        $render->add_data('ban_length_days', floor($length2));
        $length2 = $length2 - $render->retrieve_data('ban_length_days');
        $render->add_data('ban_length_hours', $length2 * 24);
    }
    
    if ($baninfo['appeal_status'] > 1)
    {
        $render->add_data('appeal_check', 'checked');
    }
    
    $render->parse('bans_panel_modify_ban.tpl', 'management');
    nel_render_basic_footer($render);
    $render->output(TRUE);
}

function nel_render_ban_panel_bottom($dataforce, $render)
{
    $render->parse('bans_panel_bottom.tpl', 'management');
}
?>