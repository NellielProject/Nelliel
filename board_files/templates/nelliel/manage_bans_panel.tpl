{{ if $rendervar['ban_panel_head'] }}
<div class="pass-valid">{LANG_MANAGE_MODE}</div>
<div class="del-list">{LANG_MANAGE_BANS}</div>
<table class="post-lists">
    <tr class="manage-header">
        <th>{LANG_MANAGE_BAN_ID}</th>
        <th>{LANG_MANAGE_BAN_TYPE}</th>
        <th>{LANG_MANAGE_BAN_HOST}</th>
        <th>{LANG_MANAGE_BAN_NAME}</th>
        <th>{LANG_MANAGE_BAN_REASON}</th>
        <th>{LANG_MANAGE_BAN_EXPIRE}</th>
        <th>{LANG_MANAGE_BAN_APPEAL}</th>
        <th>{LANG_MANAGE_BAN_APPEAL_RESPONSE}</th>
        <th>{LANG_MANAGE_BAN_STATUS}</th>
        <th>{LANG_MANAGE_BAN_MODIFY}</th>
        <th>{LANG_MANAGE_BAN_REMOVE}</th>
    </tr>
    {{ endif }} {{ if $rendervar['ban_panel_loop'] }}
    <tr class={$rendervar['bg_class']}>
        <td>{$rendervar['id']}</td>
        <td>{$rendervar['type']}</td>
        <td>{$rendervar['host']}</td>
        <td>{$rendervar['name']}</td>
        <td>{$rendervar['reason']}</td>
        <td>{$rendervar['ban_expire']}</a></td>
        <td>{$rendervar['appeal']}</td>
        <td>{$rendervar['appeal_response']}</td>
        <td>{$rendervar['appeal_status']}</td>
        <td><form accept-charset="utf-8" action="{$rendervar['dotdot']}{PHP_SELF}" method="post">
                <div>
                    <input type="hidden" name="mode" value="admin"> <input type="hidden" name="adminmode" value="modifyban"> <input
                        type="hidden" name="banid" value="{$rendervar['id']}"> <input type="submit" value="{LANG_FORM_MOD_BAN}">
                </div>
            </form></td>
        <td><form accept-charset="utf-8" action="{$rendervar['dotdot']}{PHP_SELF}" method="post">
                <div>
                    <input type="hidden" name="mode" value="admin"> <input type="hidden" name="adminmode" value="removeban"> <input
                        type="hidden" name="banid" value="{$rendervar['id']}"> <input type="submit" value="{LANG_FORM_REMOVE_BAN}">
                </div>
            </form></td>
    </tr>
    {{ endif }} {{ if $rendervar['ban_panel_end'] }}
</table>
<hr>
<table>
    <tr>
        <td><form accept-charset="utf-8" action="{$rendervar['dotdot']}{PHP_SELF}" method="post">
                <div>
                    <input type="hidden" name="mode" value="admin"> <input type="hidden" name="adminmode" value="newban"> <input
                        type="submit" value="{LANG_FORM_ADD_BAN}">
                </div>
            </form></td>
    </tr>
</table>
{{ endif }} {{ if $rendervar['ban_panel_modify'] }}
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
    <table>
        <tr>
            <td>{LANG_MANAGE_BANMOD_GEN} {$rendervar['ban_time']}</td>
        </tr>
        <tr>
            <td>{LANG_MANAGE_BANMOD_EXP} {$rendervar['ban_expire']}</td>
        </tr>
        <tr>
            <td>{LANG_MANAGE_BANMOD_LENGTH}</td>
            <td>{LANG_MANAGE_BANMOD_DAY} <input type="text" name="timedays" size="4" maxlength="4" value="{$rendervar['ban_length_days']}">
                &nbsp;&nbsp;&nbsp; {LANG_MANAGE_BANMOD_HOUR} <input type="text" name="timehours" size="4" maxlength="4"
                value="{$rendervar['ban_length_hours']}"></td>
        </tr>
        <tr>
            <td>{LANG_MANAGE_BANMOD_NAME} {$rendervar['name']}</td>
        </tr>
        <tr>
            <td><input type="hidden" name="banid" value="{$rendervar['id']}"> <input type="hidden" name="mode" value="admin">
                <input type="hidden" name="adminmode" value="changeban"> <input type="hidden" name="original"
                value="{$rendervar['length']}"></td>
        </tr>
        <tr>
            <td>{LANG_MANAGE_BANMOD_RSN}</td>
            <td><textarea name="banreason" cols="32" rows="3">{$rendervar['reason']}</textarea></td>
        </tr>
        {{ if isset($rendervar['appeal']) }}
        <tr>
            <td>Appeal:</td>
            <td>{$rendervar['appeal']}</td>
        </tr>
        {{ endif }}
        <tr>
            <td>{LANG_MANAGE_BAN_APPEAL_RESPONSE}</td>
            <td><textarea name="appealresponse" cols="32" rows="3">{$rendervar['appeal_response']}</textarea></td>
        </tr>
        <tr>
            <td><input type="checkbox" name="appealreview" value=1{$rendervar['appeal_check']}>{LANG_MANAGE_BANMOD_MRKAPPL}</td>
        </tr>
        <tr>
            <td><input type="submit" value="{LANG_FORM_UPDATE}"></td>
        </tr>
    </table>
    {{ endif }} {{ if $rendervar['ban_panel_add'] }}
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>{LANG_MANAGE_BANMOD_IP}</td>
                <td><input type="text" name="banhost" size="24" maxlength="50" value=""></td>
            </tr>
            <tr>
                <td>{LANG_MANAGE_BANMOD_LENGTH}</td>
                <td>{LANG_MANAGE_BANMOD_DAY} <input type="text" name="timedays" size="4" maxlength="4" value="0">
                    &nbsp;&nbsp;&nbsp; {LANG_MANAGE_BANMOD_HOUR} <input type="text" name="timehours" size="4" maxlength="4" value="0"></td>
            </tr>
            <tr>
                <td><input type="hidden" name="mode" value="admin"> <input type="hidden" name="adminmode" value="addban"></td>
            </tr>
            <tr>
                <td>{LANG_MANAGE_BANMOD_RSN}</td>
                <td><textarea name="banreason" cols="32" rows="3"></textarea></td>
            </tr>
            <tr>
                <td><input type="submit" value="{LANG_FORM_UPDATE}"></td>
            </tr>
        </table>
        {{ endif }}