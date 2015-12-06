<div class="float-right"></div>
<div class="float-left">
    <div>
        You have been banned from <span class="ban-bold">{$rendervar['board']}</span>. This ban was given on <span class="ban-bold">{$rendervar['format_time']}</span><br>
        <br>
    </div>
    <table>
        <tr>
            <td>Reason for your ban:</td>
            <td class="ban-bold">{$rendervar['reason']}</td>
        </tr>
        <tr>
            <td>Ban will expire:</td>
            <td class="ban-bold">{$rendervar['format_length']}</td>
        </tr>
        <tr>
            <td>The banned IP or hostname is:</td>
            <td class="ban-bold">{$rendervar['host']}</td>
        </tr>
        <tr>
            <td>The name used was:</td>
            <td class="ban-bold">{$rendervar['name']}</td>
        </tr>
    </table>
</div>
{{ if $rendervar['appeal_status'] === 0 }}
<form accept-charset="utf-8" name="postingform" action="{$rendervar['dotdot']}{PHP_SELF}" method="post" enctype="multipart/form-data">
    <div>
        <p>{LANG_ABOUT_APPEALS}</p>
        <input type="hidden" name="mode" value="banappeal"> <input type="hidden" name="banned_ip" value="{$rendervar['host']}">
        <input type="hidden" name="banned_board" value="{$rendervar['board']}">
        <textarea name="bawww" id="bawww" cols="60" rows="3"></textarea>
        <input type="submit" value="BAWWWWW">
    </div>
</form>
</div>
{{ elseif $rendervar['appeal_status'] === 1 }}
<p>{LANG_BAN_RESPONSE_PENDING}</p>
{{ elseif $rendervar['appeal_status'] === 2 }}
<p>
    {LANG_APPEAL_REVIEWED}<br> {{ if isset($rendervar['appeal_response']) }} {LANG_BAN_APPEAL_RESPONSE}
</p>
<p>{$rendervar['appeal_response']}</p>
{{ else }}
<p>{LANG_BAN_NO_RESPONSE}</p>
{{ endif }} {{ elseif $rendervar['appeal_status'] === 3 }}
<p>
    {LANG_BAN_ALTERED}<br> {{ if isset($rendervar['appeal_response']) }} {LANG_BAN_APPEAL_RESPONSE}
</p>
<p>{$rendervar['appeal_response']}</p>
{{ else }}
<p>{LANG_BAN_NO_RESPONSE}</p>
{{ endif }} {{ endif }}
<hr class="clear">