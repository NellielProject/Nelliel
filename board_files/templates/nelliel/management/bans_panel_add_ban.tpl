    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_IP')} 
                </td>
                <td>
                    <input type="text" name="banhost" size="24" maxlength="50" value="">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_LENGTH')} 
                </td>
                <td>
                    {nel_stext('MANAGE_BANMOD_DAY')} <input type="text" name="timedays" size="4" maxlength="4" value="0"> &nbsp;&nbsp;&nbsp; {nel_stext('MANAGE_BANMOD_HOUR')} <input type="text" name="timehours" size="4" maxlength="4" value="0">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" name="mode" value="admin">
                    <input type="hidden" name="adminmode" value="addban">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_BANMOD_RSN')} 
                </td>
                <td>
                    <textarea name="banreason" cols="32" rows="3"></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_UPDATE')}">
                </td>
            </tr>
        </table>
    </form>