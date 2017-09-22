    <div class="pass-valid">
        {nel_stext('MANAGE_MODE')}
    </div>
    <div class="del-list">
        {nel_stext('MANAGE_OPTIONS')}
    </div>
    <form action="imgboard.php" method="post" enctype="multipart/form-data">
        <div>
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_board_config') }}
            <input type="radio" name="mode" id="admin" value="admin->settings->panel"><label for="admin">{nel_stext('MANAGE_OPT_SETTINGS')}</label><br>
{{ endif }}
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_ban_access') }}
            <input type="radio" name="mode" id="ban" value="admin->ban->panel"><label for="ban">{nel_stext('MANAGE_OPT_BAN')}</label><br>
{{ endif }}
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_post_access') }}
            <input type="radio" name="mode" id="thread" value="admin->thread->panel"><label for="thread">{nel_stext('MANAGE_OPT_THREAD')}</label><br>
{{ endif }}
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_staff_access') }}
            <input type="radio" name="mode" id="staff" value="admin->staff->main"><label for="staff">{nel_stext('MANAGE_OPT_STAFF')}</label><br>
{{ endif }}
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_mod_mode') }}
            <input type="radio" name="mode" id="mmode" value="admin->modmode->enter"><label for="mmode">{nel_stext('MANAGE_OPT_MMODE')}</label><br>
{{ endif }}
            <input type="submit" value="{nel_stext('FORM_SUBMIT')}">
        </div>
    </form>
    <hr>
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_regen_index') }}
    <form action="imgboard.php" method="post" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="mode" value="admin->regen->full">
            <input type="submit" value="{nel_stext('FORM_UPDATE_PAGES')}"><br>
            {nel_stext('MANAGE_UPDATE_WARN')}<br><br>
        </div>
    </form>
{{ endif }}
{{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_regen_caches') }}
    <form action="imgboard.php" method="post" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="mode" value="admin->regen->cache">
            <input type="submit" value="{nel_stext('FORM_UPDATE_CACHE')}"><br>
            {nel_stext('MANAGE_UPDATE_CACHE_WARN')}<br><br>
        </div>
    </form>
{{ endif }}