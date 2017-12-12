    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_GEN')} {$render->get('ban_time')}
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_EXP')} {$render->get('ban_expire')}
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_LENGTH')} 
                </td>
                <td>
                    {nel_stext('MANAGE_BANMOD_DAY')} <input type="text" name="timedays" size="4" maxlength="4" value="{$render->get('ban_length_days')}"> &nbsp;&nbsp;&nbsp; {nel_stext('MANAGE_BANMOD_HOUR')} <input type="text" name="timehours" size="4" maxlength="4" value="{$render->get('ban_length_hours')}">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_NAME')} {$render->get('name')}
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" name="banid" value="{$render->get('id')}">
                    <input type="hidden" name="mode" value="admin->ban->change">
                    <input type="hidden" name="original" value="{$render->get('length')}">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_RSN')} 
                </td>
                <td>
                    <textarea name="banreason" cols="32" rows="3">{$render->get('reason')}</textarea>
                </td>
            </tr>
        {{ if $render->get('appeal') !== '' }}
            <tr>
                <td>
                    Appeal: 
                </td>
                <td>
                    {$render->get('appeal')}
                </td>
            </tr>
        {{ endif }}
            <tr>
                <td>
                    {nel_stext('MANAGE_BAN_APPEAL_RESPONSE')} 
                </td>
                <td>
                    <textarea name="appealresponse" cols="32" rows="3">{$render->get('appeal_response')}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="appealreview" value=1 {$render->get('appeal_check')}>{nel_stext('MANAGE_BANMOD_MRKAPPL')}
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_UPDATE')}">
                </td>
            </tr>
        </table>
    </form>