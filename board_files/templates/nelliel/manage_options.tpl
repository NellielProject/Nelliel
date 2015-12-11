<div class="pass-valid">{$lang['MANAGE_MODE']}</div>
<div class="del-list">{$lang['MANAGE_OPTIONS']}</div>
{{ if $rendervar['perm_config'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="admincontrol">
        <input type="submit" value="{$lang['MANAGE_OPT_SETTINGS']}">
    </div>
</form>
{{ endif }}
{{ if $rendervar['perm_ban_panel'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="bancontrol">
        <input type="submit" value="{$lang['MANAGE_OPT_BAN']}">
    </div>
</form>
{{ endif }}
{{ if $rendervar['perm_thread_panel'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="modcontrol">
        <input type="submit" value="{$lang['MANAGE_OPT_THREAD']}">
    </div>
</form>
{{ endif }}
{{ if $rendervar['perm_staff_panel'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="staff">
        <input type="submit" value="{$lang['MANAGE_OPT_STAFF']}">
    </div>
</form>
{{ endif }}
{{ if $rendervar['perm_mod_mode'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="modmode">
        <input type="submit" value="{$lang['MANAGE_OPT_MMODE']}">
    </div>
</form>
{{ endif }}
<hr>
{{ if $rendervar['perm_update_pages'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <br>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="fullupdate">
        <input type="submit" value="{$lang['FORM_UPDATE_PAGES']}">
        <br>
        {$lang['MANAGE_UPDATE_WARN']}
    </div>
</form>
{{ endif }}
{{ if $rendervar['perm_update_cache'] }}
<form action="imgboard.php" method="post" enctype="multipart/form-data">
    <div>
        <br>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="updatecache">
        <input type="submit" value="{$lang['FORM_UPDATE_CACHE']}">
        <br>
        {$lang['MANAGE_UPDATE_CACHE_WARN']}
    </div>
</form>
{{ endif }}