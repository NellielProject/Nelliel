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
                    <input type="hidden" name="mode" value="admin">
                    <input type="hidden" name="adminmode" value="editstaff">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_STAFF_EDIT')}</label>
                </td>
                <td>
                    <input type="text" name="staff_name" id="bname" size="50" value="">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_EDIT_STAFF')}">
                </td>
            </tr>
        </table>
    </form>
    <hr>
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    <input type="hidden" name="mode" value="admin">
                    <input type="hidden" name="adminmode" value="addstaff">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_STAFF_ADD')}</label>
                </td>
                <td>
                    <input type="text" name="staff_name" id="bname" size="50" value="">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="stype">{nel_stext('MANAGE_STAFF_TYPE')}</label>
                </td>
                <td>
                    <select name="staff_type" id="stype">
                        <option value="admin">{nel_stext('MANAGE_STAFF_TADMIN')}</option>
                        <option value="moderator">{nel_stext('MANAGE_STAFF_TMOD')}</option>
                        <option value="janitor">{nel_stext('MANAGE_STAFF_TJAN')}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_ADD_STAFF')}">
                </td>
            </tr>
        </table>
    </form>