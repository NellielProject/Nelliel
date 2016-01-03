<?php
function nel_render_ban_panel_top($dataforce)
{
    $dat = nel_parse_template('bans_panel_top.tpl', 'management', FALSE);
    return $dat;
}

function nel_render_ban_panel_list($dataforce, $dbh)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat .= nel_render_header($dataforce, 'ADMIN', array());
    $dat .= nel_render_ban_panel_top($dataforce);
    $result = $dbh->query('SELECT * FROM ' . BANTABLE . ' ORDER BY id DESC');

    while ($baninfo = $result->fetch(PDO::FETCH_ASSOC))
    {
        nel_render_in('ban_panel_loop', TRUE);
        nel_render_in('host', (@inet_ntop(nel_render_out('host'))) ? inet_ntop(nel_render_out('host')) : 'Unknown');
        nel_render_in('ban_appeal_response', $baninfo['appeal_response']);
        nel_render_in('ban_expire', date("D F jS Y  H:i:s", nel_render_out('length') + nel_render_out('ban_time')));
        if (nel_render_out('bg_class') === 'row1')
        {
            nel_render_in('bg_class', 'row2');
        }
        else
        {
            nel_render_in('bg_class', 'row1');
        }

        $dat .= nel_parse_template('bans_panel_list_bans.tpl', 'management', FALSE);
    }

    unset($result);

    $dat .= nel_render_ban_panel_bottom($dataforce);
    $dat .= nel_render_basic_footer();
    return $dat;
}

function nel_render_ban_panel_add($dataforce, $baninfo)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat .= nel_render_header($dataforce, 'ADMIN', array());
    $dat = nel_parse_template('bans_panel_add_ban.tpl', 'management', FALSE);
    $dat .= nel_render_basic_footer();
    return $dat;
}

function nel_render_ban_panel_modify($dataforce, $baninfo)
{
    nel_render_init(TRUE);
    $dat = '';
    $dat .= nel_render_header($dataforce, 'ADMIN', array());
    $result = $dbh->query('SELECT * FROM ' . BANTABLE . ' WHERE id=' . $dataforce['banid'] . '');
    $baninfo = $result->fetch(PDO::FETCH_ASSOC);
    unset($result);
    nel_render_multiple_in($c);

    nel_render_in('appeal_check', '');
    nel_render_in('ban_expire', date("D F jS Y  H:i:s", nel_render_out('length') + nel_render_out('ban_time')));
    nel_render_in('ban_time', date("D F jS Y  H:i:s", nel_render_out('ban_time')));
    $length2 = nel_render_out('length') / 3600;
    nel_render_in('ban_length_hours', 0);
    nel_render_in('ban_length_days', 0);

    if ($length2 >= 24)
    {
        $length2 = $length2 / 24;
        nel_render_in('ban_length_days', floor($length2));
        $length2 = $length2 - nel_render_out('ban_length_days');
        nel_render_in('ban_length_hours', $length2 * 24);
    }

    if (nel_render_out('appeal_status') > 1)
    {
        nel_render_in('appeal_check', 'checked');
    }

    $dat = nel_parse_template('bans_panel_modify_ban.tpl', 'management', FALSE);
    $dat .= nel_render_basic_footer();
    return $dat;
}

function nel_render_ban_panel_bottom($dataforce)
{
    $dat = nel_parse_template('bans_panel_bottom.tpl', 'management', FALSE);
    return $dat;
}
?>