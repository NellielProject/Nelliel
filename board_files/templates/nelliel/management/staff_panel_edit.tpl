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
                    <input type="hidden" name="mode" value="admin->staff->update">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_STAFF_UNAME')}</label>
                </td>
                <td>
                    <input type="text" name="staff_name" id="bname" size="50" value="{$render->retrieve_data('staff_name')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bpass">{nel_stext('MANAGE_STAFF_PASS')}</label>
                </td>
                <td>
                    <input type="text" name="staff_password" id="bpass" size="50">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="change_pass" id="pchange" value=0><label for="pchange">{nel_stext('MANAGE_STAFF_CHANGE_PASS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="ptrip">{nel_stext('MANAGE_STAFF_PTRIP')}</label>
                </td>
                <td>
                    <input type="text" name="staff_trip" id="ptrip" size="50" value="{$render->retrieve_data('staff_trip')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="stype">{nel_stext('MANAGE_STAFF_STYPE')}</label>
                </td>
                <td>
                    <select name="staff_type" id="stype">
    {{ if $render->retrieve_data('staff_type') === "admin" }}
                    <option value="admin" selected>{nel_stext('MANAGE_STAFF_TADMIN')}</option>
    {{ else }}
                    <option value="admin">{nel_stext('MANAGE_STAFF_TADMIN')}</option>
    {{ endif }}
    {{ if $render->retrieve_data('staff_type') === "moderator" }}
                    <option value="moderator" selected>{nel_stext('MANAGE_STAFF_TMOD')}</option>
    {{ else }}
                    <option value="moderator">{nel_stext('MANAGE_STAFF_TMOD')}</option>
    {{ endif }}
    {{ if $render->retrieve_data('staff_type') === "janitor" }}
                    <option value="janitor" selected>{nel_stext('MANAGE_STAFF_TJAN')}</option>
    {{ else }}
                    <option value="janitor">{nel_stext('MANAGE_STAFF_TJAN')}</option>
    {{ endif }}
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_config" id="pconfig" value=1 {$render->retrieve_data('perm_config')}><label for="pconfig">{nel_stext('MANAGE_STAFF_ACC_SET')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_staff_panel" id="bstaff" value=1 {$render->retrieve_data('perm_staff_panel')}><label for="bstaff">{nel_stext('MANAGE_STAFF_ACC_STAFF')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_ban_panel" id="bpanel" value=1 {$render->retrieve_data('perm_ban_panel')}><label for="bpanel">{nel_stext('MANAGE_STAFF_ACC_BAN')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_thread_panel" id="pthread" value=1 {$render->retrieve_data('perm_thread_panel')}><label for="pthread">{nel_stext('MANAGE_STAFF_ACC_THREAD')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_mod_mode" id="pmode" value=1 {$render->retrieve_data('perm_mod_mode')}><label for="pmode">{nel_stext('MANAGE_STAFF_ACC_MMODE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_ban" id="pban" value=1 {$render->retrieve_data('perm_ban')}><label for="pban">{nel_stext('MANAGE_STAFF_PERMBAN')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_delete" id="pdelete" value=1 {$render->retrieve_data('perm_delete')}><label for="pdelete">{nel_stext('MANAGE_STAFF_PERMDEL')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_post" id="ppost" value=1 {$render->retrieve_data('perm_post')}><label for="ppost">{nel_stext('MANAGE_STAFF_PERMPOST')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_post_anon" id="ppostanon" value=1 {$render->retrieve_data('perm_post_anon')}><label for="ppostanon">{nel_stext('MANAGE_STAFF_PERMANON')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_sticky" id="psticky" value=1 {$render->retrieve_data('perm_sticky')}><label for="psticky">{nel_stext('MANAGE_STAFF_PERMSTICK')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_update_pages" id="ppages" value=1 {$render->retrieve_data('perm_update_pages')}><label for="ppages">{nel_stext('MANAGE_STAFF_PERMGEN')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_update_cache" id="pcache" value=1 {$render->retrieve_data('perm_update_cache')}><label for="pcache">{nel_stext('MANAGE_STAFF_PERMCACHE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_UPDATE_STAFF')}">
                </td>
            </tr>
        </table>
    </form>
    <hr>
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    <input type="hidden" name="mode" value="admin->staff->delete">
                    <input type="hidden" name="staff_name" value="{$render->retrieve_data('staff_name')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_DELETE_STAFF')}"> - {nel_stext('MANAGE_STAFF_WARNDEL')}
                </td>
            </tr>
        </table>
    </form>