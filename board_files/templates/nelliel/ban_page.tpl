<div class="float-right"></div>
    <div class="float-left">
        <div>
            You have been banned from <span class="ban-bold">{$render->retrieve_data('board')}</span>. This ban was given on <span class="ban-bold">{$render->retrieve_data('format_time')}</span><br><br>
        </div>
        <table>
            <tr>
                <td>
                    Reason for your ban: 
                </td>
                <td class="ban-bold">
                    {$render->retrieve_data('reason')}
                </td>
            </tr>
            <tr>
                <td>
                    Ban will expire: 
                </td>
                <td class="ban-bold">
                    {$render->retrieve_data('format_length')}
                </td>
                    </tr>
                    <tr>
                        <td>
                            The banned IP or hostname is: 
                        </td>
                        <td class="ban-bold">
                            {$render->retrieve_data('ip_address')}
                        </td>
                    </tr>
                    <tr>
                        <td>
                        The name used was: 
                        </td>
                        <td class="ban-bold">
                            {$render->retrieve_data('poster_name')}
                        </td>
                    </tr>
                </tr>
            </table>
        </div>
        {{ if $render->retrieve_data('appeal_status') === 0 }}
        <form accept-charset="utf-8" name="postingform" action="{$render->retrieve_data('dotdot')}{PHP_SELF}" method="post" enctype="multipart/form-data">
            <div>
                <p>
                    {nel_stext('ABOUT_APPEALS')}
                </p>
                <input type="hidden" name="mode" value="banappeal">
                <input type="hidden" name="banned_ip" value="{$render->retrieve_data('ip_address')}">
                <input type="hidden" name="banned_board" value="{$render->retrieve_data('board')}">
                <textarea name="bawww" id="bawww" cols="60" rows="3"></textarea>
                <input type="submit" value="BAWWWWW">
            </div>
        </form>
    </div>
    {{ elseif $render->retrieve_data('appeal_status') === 1 }}
    <p>
        {nel_stext('BAN_RESPONSE_PENDING')}
    </p>
    {{ elseif $render->retrieve_data('appeal_status') === 2 }}
    <p>
        {nel_stext('APPEAL_REVIEWED')}<br>
        {{ if $render->retrieve_data('appeal_response') !== '' }}
        {nel_stext('BAN_APPEAL_RESPONSE')}
    </p>
    <p>
        {$render->retrieve_data('appeal_response')}
    </p>
        {{ else }}
    <p>
        {nel_stext('BAN_NO_RESPONSE')}
    </p>
        {{ endif }}
    {{ elseif $render->retrieve_data('appeal_status') === 3 }}
    <p>
        {nel_stext('BAN_ALTERED')}<br>
        {{ if $render->retrieve_data('appeal_response') !== '' }}
        {nel_stext('BAN_APPEAL_RESPONSE')}
    </p>
    <p>
        {$render->retrieve_data('appeal_response')}
    </p>
        {{ else }}
    <p>
        {nel_stext('BAN_NO_RESPONSE')}
    </p>
        {{ endif }}
    {{ endif }}
    <hr class="clear">