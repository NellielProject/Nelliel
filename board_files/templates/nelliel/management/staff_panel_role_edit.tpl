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
                	<input type="hidden" name="perm_config_access" value=0>
                    <input type="checkbox" name="perm_config_access" id="perm_config_access" value=1 {$render->retrieve_data('perm_config_access')}>
                    <label for="perm_config_access">{nel_stext('MANAGE_STAFF_CONFIG_ACCESS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_config_change" value=0>
                    <input type="checkbox" name="perm_config_change" id="perm_config_change" value=1 {$render->retrieve_data('perm_config_change')}>
                    <label for="perm_config_change">{nel_stext('MANAGE_STAFF_CONFIG_CHANGE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_user_access" value=0>
                    <input type="checkbox" name="perm_user_access" id="perm_user_access" value=1 {$render->retrieve_data('perm_user_access')}>
                    <label for="perm_user_access">{nel_stext('MANAGE_STAFF_USER_ACCESS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_user_add" value=0>
                    <input type="checkbox" name="perm_user_add" id="perm_user_add" value=1 {$render->retrieve_data('perm_user_add')}>
                    <label for="perm_user_add">{nel_stext('MANAGE_STAFF_USER_ADD')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_user_modify" value=0>
                    <input type="checkbox" name="perm_user_modify" id="perm_user_modify" value=1 {$render->retrieve_data('perm_user_modify')}>
                    <label for="perm_user_modify">{nel_stext('MANAGE_STAFF_USER_MODIFY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_user_delete" value=0>
                    <input type="checkbox" name="perm_user_delete" id="perm_user_delete" value=1 {$render->retrieve_data('perm_user_delete')}>
                    <label for="perm_user_delete">{nel_stext('MANAGE_STAFF_USER_DELETE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_user_change_pass" value=0>
                    <input type="checkbox" name="perm_user_change_pass" id="perm_user_change_pass" value=1 {$render->retrieve_data('perm_user_change_pass')}>
                    <label for="perm_user_change_pass">{nel_stext('MANAGE_STAFF_USER_CHANGE_PASS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_role_access" value=0>
                    <input type="checkbox" name="perm_role_access" id="perm_role_access" value=1 {$render->retrieve_data('perm_role_access')}>
                    <label for="perm_role_access">{nel_stext('MANAGE_STAFF_ROLE_ACCESS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_role_add" value=0>
                    <input type="checkbox" name="perm_role_add" id="perm_role_add" value=1 {$render->retrieve_data('perm_role_add')}>
                    <label for="perm_role_add">{nel_stext('MANAGE_STAFF_ROLE_ADD')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_role_modify" value=0>
                    <input type="checkbox" name="perm_role_modify" id="perm_role_modify" value=1 {$render->retrieve_data('perm_role_modify')}>
                    <label for="perm_role_modify">{nel_stext('MANAGE_STAFF_ROLE_MODIFY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_role_delete" value=0>
                    <input type="checkbox" name="perm_role_delete" id="perm_role_delete" value=1 {$render->retrieve_data('perm_role_delete')}>
                    <label for="perm_role_delete">{nel_stext('MANAGE_STAFF_ROLE_DELETE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_ban_access" value=0>
                    <input type="checkbox" name="perm_ban_access" id="perm_ban_access" value=1 {$render->retrieve_data('perm_ban_access')}>
                    <label for="perm_ban_access">{nel_stext('MANAGE_STAFF_BAN_ACCESS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_ban_add" value=0>
                    <input type="checkbox" name="perm_ban_add" id="perm_ban_add" value=1 {$render->retrieve_data('perm_ban_add')}>
                    <label for="perm_ban_add">{nel_stext('MANAGE_STAFF_BAN_ADD')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_ban_modify" value=0>
                    <input type="checkbox" name="perm_ban_modify" id="perm_ban_modify" value=1 {$render->retrieve_data('perm_ban_modify')}>
                    <label for="perm_ban_modify">{nel_stext('MANAGE_STAFF_BAN_MODIFY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_ban_delete" value=0>
                    <input type="checkbox" name="perm_ban_delete" id="perm_ban_delete" value=1 {$render->retrieve_data('perm_ban_delete')}>
                    <label for="perm_ban_delete">{nel_stext('MANAGE_STAFF_BAN_DELETE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_access" value=0>
                    <input type="checkbox" name="perm_post_access" id="perm_post_access" value=1 {$render->retrieve_data('perm_post_access')}>
                    <label for="perm_post_access">{nel_stext('MANAGE_STAFF_POST_ACCESS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_modify" value=0>
                    <input type="checkbox" name="perm_post_modify" id="perm_post_modify" value=1 {$render->retrieve_data('perm_post_modify')}>
                    <label for="perm_post_modify">{nel_stext('MANAGE_STAFF_POST_MODIFY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_delete" value=0>
                    <input type="checkbox" name="perm_post_delete" id="perm_post_delete" value=1 {$render->retrieve_data('perm_post_delete')}>
                    <label for="perm_post_delete">{nel_stext('MANAGE_STAFF_POST_DELETE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_file_delete" value=0>
                    <input type="checkbox" name="perm_post_file_delete" id="perm_post_file_delete" value=1 {$render->retrieve_data('perm_post_file_delete')}>
                    <label for="perm_post_file_delete">{nel_stext('MANAGE_STAFF_POST_FILE_DELETE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_default_name" value=0>
                    <input type="checkbox" name="perm_post_default_name" id="perm_post_default_name" value=1 {$render->retrieve_data('perm_post_default_name')}>
                    <label for="perm_post_default_name">{nel_stext('MANAGE_STAFF_POST_DEFAULT_NAME')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_custom_name" value=0>
                    <input type="checkbox" name="perm_post_custom_name" id="perm_post_custom_name" value=1 {$render->retrieve_data('perm_post_custom_name')}>
                    <label for="perm_post_custom_name">{nel_stext('MANAGE_STAFF_POST_CUSTOM_NAME')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_override_anon" value=0>
                    <input type="checkbox" name="perm_post_override_anon" id="perm_post_override_anon" value=1 {$render->retrieve_data('perm_post_override_anon')}>
                    <label for="perm_post_override_anon">{nel_stext('MANAGE_STAFF_POST_OVERRIDE_ANON')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_unsticky" value=0>
                    <input type="checkbox" name="perm_post_unsticky" id="perm_post_unsticky" value=1 {$render->retrieve_data('perm_post_unsticky')}>
                    <label for="perm_post_unsticky">{nel_stext('MANAGE_STAFF_POST_UNSTICKY')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_lock" value=0>
                    <input type="checkbox" name="perm_post_lock" id="perm_post_lock" value=1 {$render->retrieve_data('perm_post_lock')}>
                    <label for="perm_post_lock">{nel_stext('MANAGE_STAFF_POST_LOCK')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_unlock" value=0>
                    <input type="checkbox" name="perm_post_unlock" id="perm_post_unlock" value=1 {$render->retrieve_data('perm_post_unlock')}>
                    <label for="perm_post_unlock">{nel_stext('MANAGE_STAFF_POST_UNLOCK')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_in_locked" value=0>
                    <input type="checkbox" name="perm_post_in_locked" id="perm_post_in_locked" value=1 {$render->retrieve_data('perm_post_in_locked')}>
                    <label for="perm_post_in_locked">{nel_stext('MANAGE_STAFF_POST_IN_LOCKED')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_comment" value=0>
                    <input type="checkbox" name="perm_post_comment" id="perm_post_comment" value=1 {$render->retrieve_data('perm_post_comment')}>
                    <label for="perm_post_comment">{nel_stext('MANAGE_STAFF_POST_COMMENT')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_post_permsage" value=0>
                    <input type="checkbox" name="perm_post_permsage" id="perm_post_permsage" value=1 {$render->retrieve_data('perm_post_permsage')}>
                    <label for="perm_post_permsage">{nel_stext('MANAGE_STAFF_POST_PERMSAGE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_regen_caches" value=0>
                    <input type="checkbox" name="perm_regen_caches" id="perm_regen_caches" value=1 {$render->retrieve_data('perm_regen_caches')}>
                    <label for="perm_regen_caches">{nel_stext('MANAGE_STAFF_REGEN_CACHES')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_regen_index" value=0>
                    <input type="checkbox" name="perm_regen_index" id="perm_regen_index" value=1 {$render->retrieve_data('perm_regen_index')}>
                    <label for="perm_regen_index">{nel_stext('MANAGE_STAFF_REGEN_INDEX')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_regen_threads" value=0>
                    <input type="checkbox" name="perm_regen_threads" id="perm_regen_threads" value=1 {$render->retrieve_data('perm_regen_threads')}>
                    <label for="perm_regen_threads">{nel_stext('MANAGE_STAFF_REGEN_THREADS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_modmode_access" value=0>
                    <input type="checkbox" name="perm_modmode_access" id="perm_modmode_access" value=1 {$render->retrieve_data('perm_modmode_access')}>
                    <label for="perm_modmode_access">{nel_stext('MANAGE_STAFF_MODMODE_ACCESS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                	<input type="hidden" name="perm_modmode_view_ips" value=0>
                    <input type="checkbox" name="perm_modmode_view_ips" id="perm_modmode_view_ips" value=1 {$render->retrieve_data('perm_modmode_view_ips')}>
                    <label for="perm_modmode_view_ips">{nel_stext('MANAGE_STAFF_MODMODE_VIEW_IPS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('FORM_STAFF_ROLE_UPDATE')}">
                </td>
            </tr>
        </table>
    </form>