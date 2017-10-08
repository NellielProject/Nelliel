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
                    <input type="hidden" name="mode" value="admin->staff->role->update">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="rid">{nel_stext('MANAGE_STAFF_ROLE_ID')}</label>
                </td>
                <td>
                    <input type="text" name="role_id" id="rid" size="50" value="{$render->retrieve_data('role_id')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="rtitle">{nel_stext('MANAGE_STAFF_ROLE_TITLE')}</label>
                </td>
                <td>
                    <input type="text" name="role_title" id="rtitle" size="50" value="{$render->retrieve_data('role_title')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="rcaptext">{nel_stext('MANAGE_STAFF_CAPCODE_TEXT')}</label>
                </td>
                <td>
                    <input type="text" name="capcode_text" id="rcaptext" size="50" value="{$render->retrieve_data('capcode_text')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_board_config" id="paccconfig" value=1 {$render->retrieve_data('perm_board_config')}><label for="paccconfig">{nel_stext('MANAGE_PERM_CONFIG')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_staff_access" id="paccstaff" value=1 {$render->retrieve_data('perm_staff_access')}><label for="paccstaff">{nel_stext('MANAGE_STAFF_ACCESS_STAFF')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_staff_add" id="paddstaff" value=1 {$render->retrieve_data('perm_staff_add')}><label for="paccstaff">{nel_stext('MANAGE_STAFF_ADD')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_staff_modify" id="pmodstaff" value=1 {$render->retrieve_data('perm_staff_modify')}><label for="pmodstaff">{nel_stext('MANAGE_STAFF_MODIFY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_ban_access" id="paccban" value=1 {$render->retrieve_data('perm_ban_access')}><label for="paccban">{nel_stext('MANAGE_STAFF_ACCESS_BAN')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_ban_add" id="paddban" value=1 {$render->retrieve_data('perm_ban_add')}><label for="paddban">{nel_stext('MANAGE_STAFF_ADD_BAN')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_ban_modify" id="pmodban" value=1 {$render->retrieve_data('perm_ban_modify')}><label for="pmodban">{nel_stext('MANAGE_STAFF_MODIFY_BAN')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_post_access" id="paccpost" value=1 {$render->retrieve_data('perm_post_access')}><label for="paccpost">{nel_stext('MANAGE_STAFF_ACCESS_POSTS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_post_modify" id="pmodpost" value=1 {$render->retrieve_data('perm_post_modify')}><label for="pmodpost">{nel_stext('MANAGE_STAFF_MODIFY_POST')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_can_post" id="pppost" value=1 {$render->retrieve_data('perm_can_post')}><label for="pppost">{nel_stext('MANAGE_STAFF_CAN_POST')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_can_post_name" id="ppname" value=1 {$render->retrieve_data('perm_can_post_name')}><label for="ppname">{nel_stext('MANAGE_STAFF_CAN_POST_NAME')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_make_sticky" id="ppsticky" value=1 {$render->retrieve_data('perm_make_sticky')}><label for="ppsticky">{nel_stext('MANAGE_STAFF_MAKE_STICKY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_make_locked" id="pplocked" value=1 {$render->retrieve_data('perm_make_locked')}><label for="pplocked">{nel_stext('MANAGE_STAFF_MAKE_LOCKED')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_regen_caches" id="pcregen" value=1 {$render->retrieve_data('perm_regen_caches')}><label for="pcregen">{nel_stext('MANAGE_STAFF_REGEN_CACHE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_regen_thread" id="ptregen" value=1 {$render->retrieve_data('perm_regen_thread')}><label for="ptregen">{nel_stext('MANAGE_STAFF_REGEN_THREAD')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="perm_mod_mode" id="pmodmode" value=1 {$render->retrieve_data('perm_mod_mode')}><label for="pmodmode">{nel_stext('MANAGE_STAFF_MODMODE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_STAFF_ROLE_UPDATE')}">
                </td>
            </tr>
        </table>
    </form>