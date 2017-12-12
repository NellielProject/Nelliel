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
                    <input type="hidden" name="mode" value="admin->staff->user->edit">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_STAFF_USER_ID')}</label>
                </td>
                <td>
                    <input type="text" name="user_id" id="bname" size="50" value="">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_STAFF_USER_EDIT')}">
                </td>
            </tr>
        </table>
    </form>
    <hr>
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    <input type="hidden" name="mode" value="admin->staff->role->edit">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_STAFF_ROLE_ID')}</label>
                </td>
                <td>
                    <input type="text" name="role_id" id="bname" size="50" value="">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_STAFF_ROLE_EDIT')}">
                </td>
            </tr>
        </table>
    </form>