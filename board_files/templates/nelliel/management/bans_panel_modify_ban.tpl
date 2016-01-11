    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_GEN')} {$render->retrieve_data('ban_time')}
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_EXP')} {$render->retrieve_data('ban_expire')}
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_LENGTH')} 
                </td>
                <td>
                    {nel_stext('MANAGE_BANMOD_DAY')} <input type="text" name="timedays" size="4" maxlength="4" value="{$render->retrieve_data('ban_length_days')}"> &nbsp;&nbsp;&nbsp; {nel_stext('MANAGE_BANMOD_HOUR')} <input type="text" name="timehours" size="4" maxlength="4" value="{$render->retrieve_data('ban_length_hours')}">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_NAME')} {$render->retrieve_data('name')}
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" name="banid" value="{$render->retrieve_data('id')}">
                    <input type="hidden" name="mode" value="admin">
                    <input type="hidden" name="adminmode" value="changeban">
                    <input type="hidden" name="original" value="{$render->retrieve_data('length')}">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_RSN')} 
                </td>
                <td>
                    <textarea name="banreason" cols="32" rows="3">{$render->retrieve_data('reason')}</textarea>
                </td>
            </tr>
        {{ if $render->retrieve_data('appeal') !== '' }}
            <tr>
                <td>
                    Appeal: 
                </td>
                <td>
                    {$render->retrieve_data('appeal')}
                </td>
            </tr>
        {{ endif }}
            <tr>
                <td>
                    {nel_stext('MANAGE_BAN_APPEAL_RESPONSE')} 
                </td>
                <td>
                    <textarea name="appealresponse" cols="32" rows="3">{$render->retrieve_data('appeal_response')}</textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="appealreview" value=1 {$render->retrieve_data('appeal_check')}>{nel_stext('MANAGE_BANMOD_MRKAPPL')}
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_UPDATE')}">
                </td>
            </tr>
        </table>
    </form>