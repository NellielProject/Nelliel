{{ if $rendervar['edit_staff'] }}
<div class="pass-valid">{$lang['MANAGE_MODE']}</div>
<div class="del-list">{$lang['MANAGE_STAFF']}</div>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
    <tr><td>
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="updatestaff">
    </td></tr>
    <tr><td><label for="bname">{$lang['MANAGE_STAFF_UNAME']}</label></td><td><input type="text" name="staff_name" id="bname" size="50" value="{$rendervar['staff_name']}"></td></tr>
    <tr><td><label for="bpass">{$lang['MANAGE_STAFF_PASS']}</label></td><td><input type="text" name="staff_password" id="bpass" size="50"></td></tr>
    <tr><td><input type="checkbox" name="change_pass" id="pchange" value=0><label for="pchange">{$lang['MANAGE_STAFF_CHANGE_PASS']}</label></td></tr>
    <tr><td><label for="ptrip">{$lang['MANAGE_STAFF_PTRIP']}</label></td><td><input type="text" name="staff_trip" id="ptrip" size="50" value="{$rendervar['staff_trip']}"></td></tr>
    <tr><td><label for="stype">{$lang['MANAGE_STAFF_STYPE']}</label></td>
        <td><select name="staff_type" id="stype">
        {{ if $rendervar['staff_type'] === "admin" }}
        <option value="admin" selected>{$lang['MANAGE_STAFF_TADMIN']}</option>
        {{ else }}
        <option value="admin">{$lang['MANAGE_STAFF_TADMIN']}</option>
        {{ endif }}
        {{ if $rendervar['staff_type'] === "moderator" }}
        <option value="moderator" selected>{$lang['MANAGE_STAFF_TMOD']}</option>
        {{ else }}
        <option value="moderator">{$lang['MANAGE_STAFF_TMOD']}</option>
        {{ endif }}
        {{ if $rendervar['staff_type'] === "janitor" }}
        <option value="janitor" selected>{$lang['MANAGE_STAFF_TJAN']}</option>
        {{ else }}
        <option value="janitor">{$lang['MANAGE_STAFF_TJAN']}</option>
        {{ endif }}
        </td>
    </tr>
    <tr><td><input type="checkbox" name="perm_config" id="pconfig" value=1 {$rendervar['perm_config']}><label for="pconfig">{$lang['MANAGE_STAFF_ACC_SET']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_staff_panel" id="bstaff" value=1 {$rendervar['perm_staff_panel']}><label for="bstaff">{$lang['MANAGE_STAFF_ACC_STAFF']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_ban_panel" id="bpanel" value=1 {$rendervar['perm_ban_panel']}><label for="bpanel">{$lang['MANAGE_STAFF_ACC_BAN']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_thread_panel" id="pthread" value=1 {$rendervar['perm_thread_panel']}><label for="pthread">{$lang['MANAGE_STAFF_ACC_THREAD']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_mod_mode" id="pmode" value=1 {$rendervar['perm_mod_mode']}><label for="pmode">{$lang['MANAGE_STAFF_ACC_MMODE']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_ban" id="pban" value=1 {$rendervar['perm_ban']}><label for="pban">{$lang['MANAGE_STAFF_PERMBAN']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_delete" id="pdelete" value=1 {$rendervar['perm_delete']}><label for="pdelete">{$lang['MANAGE_STAFF_PERMDEL']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_post" id="ppost" value=1 {$rendervar['perm_post']}><label for="ppost">{$lang['MANAGE_STAFF_PERMPOST']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_post_anon" id="ppostanon" value=1 {$rendervar['perm_post_anon']}><label for="ppostanon">{$lang['MANAGE_STAFF_PERMANON']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_sticky" id="psticky" value=1 {$rendervar['perm_sticky']}><label for="psticky">{$lang['MANAGE_STAFF_PERMSTICK']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_update_pages" id="ppages" value=1 {$rendervar['perm_update_pages']}><label for="ppages">{$lang['MANAGE_STAFF_PERMGEN']}</label></td></tr>
    <tr><td><input type="checkbox" name="perm_update_cache" id="pcache" value=1 {$rendervar['perm_update_cache']}><label for="pcache">{$lang['MANAGE_STAFF_PERMCACHE']}</label></td></tr>
    <tr><td><input type="submit" value="{$lang['FORM_UPDATE_STAFF']}"></td></tr>
</table></form>
    <hr>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
    <tr><td><input type="hidden" name="mode" value="admin">
            <input type="hidden" name="adminmode" value="deletestaff">
            <input type="hidden" name="staff_name" value="{$rendervar['staff_name']}"></td></tr>
    <tr><td><input type="submit" value="{$lang['FORM_DELETE_STAFF']}"> - {$lang['MANAGE_STAFF_WARNDEL']}</td></tr>
</table></form>
{{ endif }}

{{ if $rendervar['enter_staff'] }}
<div class="pass-valid">{$lang['MANAGE_MODE']}</div>
<div class="del-list">{$lang['MANAGE_STAFF']}</div>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
    <tr><td><input type="hidden" name="mode" value="admin">
            <input type="hidden" name="adminmode" value="editstaff">
    <tr><td><label for="bname">{$lang['MANAGE_STAFF_EDIT']}</label></td><td><input type="text" name="staff_name" id="bname" size="50" value=""></td></tr>
    <tr><td><input type="submit" value="{$lang['FORM_EDIT_STAFF']}"></td></tr>
</table></form>
<hr>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
    <tr><td><input type="hidden" name="mode" value="admin">
            <input type="hidden" name="adminmode" value="addstaff">
    </td></tr>
    <tr><td><label for="bname">{$lang['MANAGE_STAFF_ADD']}</label></td>
        <td><input type="text" name="staff_name" id="bname" size="50" value=""></td></tr>
    <tr><td><label for="stype">{$lang['MANAGE_STAFF_TYPE']}</label></td>
        <td><select name="staff_type" id="stype">
        <option value="admin">{$lang['MANAGE_STAFF_TADMIN']}</option>
        <option value="moderator">{$lang['MANAGE_STAFF_TMOD']}</option>
        <option value="janitor">{$lang['MANAGE_STAFF_TJAN']}</option></td>
    </tr>
    <tr><td><input type="submit" value="{$lang['FORM_ADD_STAFF']}"></td></tr>
</table></form>
{{ endif }}