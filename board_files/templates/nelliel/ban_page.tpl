<div class="float-right"></div>
    <div class="float-left">
        <div>
            You have been banned from <span class="ban-bold">{$render->get('board')}</span>. This ban was given on <span class="ban-bold">{$render->get('format_time')}</span><br><br>
        </div>
        <table>
            <tr>
                <td>
                    Reason for your ban: 
                </td>
                <td class="ban-bold">
                    {$render->get('reason')}
                </td>
            </tr>
            <tr>
                <td>
                    Ban will expire: 
                </td>
                <td class="ban-bold">
                    {$render->get('format_length')}
                </td>
                    </tr>
                    <tr>
                        <td>
                            The banned IP or hostname is: 
                        </td>
                        <td class="ban-bold">
                            {$render->get('ip_address')}
                        </td>
                    </tr>
                    <tr>
                        <td>
                        The name used was: 
                        </td>
                        <td class="ban-bold">
                            {$render->get('poster_name')}
                        </td>
                    </tr>
                </tr>
            </table>
        </div>
        {{ if $render->get('appeal_status') === 0 }}
        <form accept-charset="utf-8" name="postingform" action="{$render->get('dotdot')}{PHP_SELF}" method="post" enctype="multipart/form-data">
            <div>
                <p>
                    {nel_stext('ABOUT_APPEALS')}
                </p>
                <input type="hidden" name="mode" value="banappeal">
                <input type="hidden" name="banned_ip" value="{$render->get('ip_address')}">
                <input type="hidden" name="banned_board" value="{$render->get('board')}">
                <textarea name="bawww" id="bawww" cols="60" rows="3"></textarea>
                <input type="submit" value="BAWWWWW">
            </div>
        </form>
    </div>
    {{ elseif $render->get('appeal_status') === 1 }}
    <p>
        {nel_stext('BAN_RESPONSE_PENDING')}
    </p>
    {{ elseif $render->get('appeal_status') === 2 }}
    <p>
        {nel_stext('APPEAL_REVIEWED')}<br>
        {{ if $render->get('appeal_response') !== '' }}
        {nel_stext('BAN_APPEAL_RESPONSE')}
    </p>
    <p>
        {$render->get('appeal_response')}
    </p>
        {{ else }}
    <p>
        {nel_stext('BAN_NO_RESPONSE')}
    </p>
        {{ endif }}
    {{ elseif $render->get('appeal_status') === 3 }}
    <p>
        {nel_stext('BAN_ALTERED')}<br>
        {{ if $render->get('appeal_response') !== '' }}
        {nel_stext('BAN_APPEAL_RESPONSE')}
    </p>
    <p>
        {$render->get('appeal_response')}
    </p>
        {{ else }}
    <p>
        {nel_stext('BAN_NO_RESPONSE')}
    </p>
        {{ endif }}
    {{ endif }}
    <hr class="clear">