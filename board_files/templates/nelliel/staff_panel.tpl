{{ if $rendervar['edit_staff'] }}
<div class="pass-valid">{LANG_MANAGE_MODE}</div>
<div class="del-list">{LANG_MANAGE_STAFF}</div>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
	<tr><td>
		<input type="hidden" name="mode" value="admin">
		<input type="hidden" name="adminmode" value="updatestaff">
	</td></tr>
	<tr><td><label for="bname">{LANG_MANAGE_STAFF_UNAME}</label></td><td><input type="text" name="staff_name" id="bname" size="50" value="{$rendervar['staff_name']}"></td></tr>
	<tr><td><label for="bpass">{LANG_MANAGE_STAFF_PASS}</label></td><td><input type="text" name="staff_password" id="bpass" size="50"></td></tr>
	<tr><td><input type="checkbox" name="change_pass" id="pchange" value=0><label for="pchange">{LANG_MANAGE_STAFF_CHANGE_PASS}</label></td></tr>
	<tr><td><label for="ptrip">{LANG_MANAGE_STAFF_PTRIP}</label></td><td><input type="text" name="staff_trip" id="ptrip" size="50" value="{$rendervar['staff_trip']}"></td></tr>
	<tr><td><label for="stype">{LANG_MANAGE_STAFF_STYPE}</label></td>
		<td><select name="staff_type" id="stype">
		{{ if $rendervar['staff_type'] === "admin" }}
		<option value="admin" selected>{LANG_MANAGE_STAFF_TADMIN}</option>
		{{ else }}
		<option value="admin">{LANG_MANAGE_STAFF_TADMIN}</option>
		{{ endif }}
		{{ if $rendervar['staff_type'] === "moderator" }}
		<option value="moderator" selected>{LANG_MANAGE_STAFF_TMOD}</option>
		{{ else }}
		<option value="moderator">{LANG_MANAGE_STAFF_TMOD}</option>
		{{ endif }}
		{{ if $rendervar['staff_type'] === "janitor" }}
		<option value="janitor" selected>{LANG_MANAGE_STAFF_TJAN}</option>
		{{ else }}
		<option value="janitor">{LANG_MANAGE_STAFF_TJAN}</option>
		{{ endif }}
		</td>
	</tr>
	<tr><td><input type="checkbox" name="perm_config" id="pconfig" value=1 {$rendervar['perm_config']}><label for="pconfig">{LANG_MANAGE_STAFF_ACC_SET}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_staff_panel" id="bstaff" value=1 {$rendervar['perm_staff_panel']}><label for="bstaff">{LANG_MANAGE_STAFF_ACC_STAFF}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_ban_panel" id="bpanel" value=1 {$rendervar['perm_ban_panel']}><label for="bpanel">{LANG_MANAGE_STAFF_ACC_BAN}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_thread_panel" id="pthread" value=1 {$rendervar['perm_thread_panel']}><label for="pthread">{LANG_MANAGE_STAFF_ACC_THREAD}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_mod_mode" id="pmode" value=1 {$rendervar['perm_mod_mode']}><label for="pmode">{LANG_MANAGE_STAFF_ACC_MMODE}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_ban" id="pban" value=1 {$rendervar['perm_ban']}><label for="pban">{LANG_MANAGE_STAFF_PERMBAN}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_delete" id="pdelete" value=1 {$rendervar['perm_delete']}><label for="pdelete">{LANG_MANAGE_STAFF_PERMDEL}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_post" id="ppost" value=1 {$rendervar['perm_post']}><label for="ppost">{LANG_MANAGE_STAFF_PERMPOST}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_post_anon" id="ppostanon" value=1 {$rendervar['perm_post_anon']}><label for="ppostanon">{LANG_MANAGE_STAFF_PERMANON}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_sticky" id="psticky" value=1 {$rendervar['perm_sticky']}><label for="psticky">{LANG_MANAGE_STAFF_PERMSTICK}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_update_pages" id="ppages" value=1 {$rendervar['perm_update_pages']}><label for="ppages">{LANG_MANAGE_STAFF_PERMGEN}</label></td></tr>
	<tr><td><input type="checkbox" name="perm_update_cache" id="pcache" value=1 {$rendervar['perm_update_cache']}><label for="pcache">{LANG_MANAGE_STAFF_PERMCACHE}</label></td></tr>
	<tr><td><input type="submit" value="{LANG_FORM_UPDATE_STAFF}"></td></tr>
</table></form>
	<hr>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
	<tr><td><input type="hidden" name="mode" value="admin">
			<input type="hidden" name="adminmode" value="deletestaff">
			<input type="hidden" name="staff_name" value="{$rendervar['staff_name']}"></td></tr>
	<tr><td><input type="submit" value="{LANG_FORM_DELETE_STAFF}"> - {LANG_MANAGE_STAFF_WARNDEL}</td></tr>
</table></form>
{{ endif }}

{{ if $rendervar['enter_staff'] }}
<div class="pass-valid">{LANG_MANAGE_MODE}</div>
<div class="del-list">{LANG_MANAGE_STAFF}</div>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
	<tr><td><input type="hidden" name="mode" value="admin">
			<input type="hidden" name="adminmode" value="editstaff">
	<tr><td><label for="bname">{LANG_MANAGE_STAFF_EDIT}</label></td><td><input type="text" name="staff_name" id="bname" size="50" value=""></td></tr>
	<tr><td><input type="submit" value="{LANG_FORM_EDIT_STAFF}"></td></tr>
</table></form>
<hr>
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
<table>
	<tr><td><input type="hidden" name="mode" value="admin">
			<input type="hidden" name="adminmode" value="addstaff">
	</td></tr>
	<tr><td><label for="bname">{LANG_MANAGE_STAFF_ADD}</label></td>
		<td><input type="text" name="staff_name" id="bname" size="50" value=""></td></tr>
	<tr><td><label for="stype">{LANG_MANAGE_STAFF_TYPE}</label></td>
		<td><select name="staff_type" id="stype">
		<option value="admin">{LANG_MANAGE_STAFF_TADMIN}</option>
		<option value="moderator">{LANG_MANAGE_STAFF_TMOD}</option>
		<option value="janitor">{LANG_MANAGE_STAFF_TJAN}</option></td>
	</tr>
	<tr><td><input type="submit" value="{LANG_FORM_ADD_STAFF}"></td></tr>
</table></form>
{{ endif }}