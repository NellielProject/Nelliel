    <div class="pass-valid">
        {nel_stext('MANAGE_MODE')}
    </div>
    <div class="del-list">
        {nel_stext('MANAGE_STAFF')}
    </div>
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    <input type="hidden" name="mode" value="admin->staff->user->update">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_STAFF_USER_ID')}</label>
                </td>
                <td>
                    <input type="text" name="user_id" id="bname" size="50" value="{$render->get('user_id')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bpass">{nel_stext('MANAGE_STAFF_USER_PASS')}</label>
                </td>
                <td>
                    <input type="text" name="user_password" id="bpass" size="50">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="change_pass" id="pchange" value=0><label for="pchange">{nel_stext('MANAGE_STAFF_CHANGE_PASS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="utitle">{nel_stext('MANAGE_STAFF_USER_TITLE')}</label>
                </td>
                <td>
                    <input type="text" name="user_title" id="utitle" size="50" value="{$render->get('user_title')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="rselect">{nel_stext('MANAGE_STAFF_ROLE_ID')}</label>
                </td>
                <td>
                    <input type="text" name="role_id" id="rselect" size="50" value="{$render->get('role_id')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_STAFF_USER_UPDATE')}">
                </td>
            </tr>
        </table>
    </form>