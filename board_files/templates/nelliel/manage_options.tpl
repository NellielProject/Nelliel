    <div class="pass-valid">
        {stext('MANAGE_MODE')}
    </div>
    <div class="del-list">
        {stext('MANAGE_OPTIONS')}
    </div>
    <form action="imgboard.php" method="post" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="mode" value="admin">
{{ if $rendervar['perm_config'] }}
            <input type="radio" name="adminmode" id="admin" value="admincontrol"><label for="admin">{stext('MANAGE_OPT_SETTINGS')}</label><br>
{{ endif }}
{{ if $rendervar['perm_ban_panel'] }}
            <input type="radio" name="adminmode" id="ban" value="bancontrol"><label for="ban">{stext('MANAGE_OPT_BAN')}</label><br>
{{ endif }}
{{ if $rendervar['perm_thread_panel'] }}
            <input type="radio" name="adminmode" id="mod" value="modcontrol"><label for="mod">{stext('MANAGE_OPT_THREAD')}</label><br>
{{ endif }}
{{ if $rendervar['perm_staff_panel'] }}
            <input type="radio" name="adminmode" id="staff" value="staff"><label for="staff">{stext('MANAGE_OPT_STAFF')}</label><br>
{{ endif }}
{{ if $rendervar['perm_mod_mode'] }}
            <input type="radio" name="adminmode" id="mmode" value="modmode"><label for="mmode">{stext('MANAGE_OPT_MMODE')}</label><br>
{{ endif }}
            <input type="submit" value="{stext('FORM_SUBMIT')}">
        </div>
    </form>
    <hr>
{{ if $rendervar['perm_update_pages'] }}
    <form action="imgboard.php" method="post" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="mode" value="admin">
            <input type="hidden" name="adminmode" value="fullupdate">
            <input type="submit" value="{stext('FORM_UPDATE_PAGES')}"><br>
            {stext('MANAGE_UPDATE_WARN')}<br><br>
        </div>
    </form>
{{ endif }}
{{ if $rendervar['perm_update_cache'] }}
    <form action="imgboard.php" method="post" enctype="multipart/form-data">
        <div>
            <input type="hidden" name="mode" value="admin">
            <input type="hidden" name="adminmode" value="updatecache">
            <input type="submit" value="{stext('FORM_UPDATE_CACHE')}"><br>
            {stext('MANAGE_UPDATE_CACHE_WARN')}<br><br>
        </div>
    </form>
{{ endif }}