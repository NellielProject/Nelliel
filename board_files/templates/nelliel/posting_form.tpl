{{ if $render->retrieve_data('response_id') }}
        <div>
            [<a href="{$render->retrieve_data('dotdot')}{$render->retrieve_data('page_ref1')}">{nel_stext('LINK_RETURN')}</a>]
        </div>
{{ endif }}
    <div>
        <div class="posting-form">
            <form accept-charset="utf-8" name="postingform" action="{$render->retrieve_data('form_submit_url')}" method="post" enctype="multipart/form-data">
                <div>
                    <input type="hidden" name="mode" value="new_post">
{{ if $render->retrieve_data('modmode') }}
                    <input type="hidden" name="mode2" value="modmode">
{{ endif }}
                    <input type="hidden" name="response_to" value="{$render->retrieve_data('response_id')}">
                </div>
                <table class="input-table">
                    <tr>
                        <td colspan="2">
                            <!-- Why is this here?
                            Because Firefox password management and autofill is dumb.
                            -->
                            <input class="none" type="password" name="fuckoffmozilla" size="1">
                        </td>
                    </tr>
{{ if !BS_FORCE_ANONYMOUS }}
                    <tr class="posting-form-row">
                        <td class="posting-form-label">
                            <label for="durrname">{nel_stext('FORM_LABEL_NAME')}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="text" name="notanonymous" id="durrname" size="40" maxlength="{BS_MAX_NAME_LENGTH}">
                        </td>
                    </tr>
                    <tr class="posting-form-row">
                        <td class="posting-form-label">
                            <label for="durrmail">{nel_stext('FORM_LABEL_EMAIL')}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="text" name="spamtarget" id="durrmail" size="40" maxlength="{BS_MAX_EMAIL_LENGTH}">
                        </td>
                    </tr>
{{ endif }}
                    <tr class="posting-form-row">
                        <td class="posting-form-label">
                            <label for="durrsubject">{nel_stext('FORM_LABEL_SUBJECT')}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="text" name="verb" id="durrsubject" size="40" maxlength="{BS_MAX_SUBJECT_LENGTH}">
                        </td>
                    </tr>
                    <tr class="posting-form-row">
                        <td class="posting-form-label">
                            <label for="durrwords">{nel_stext('FORM_LABEL_COMMENT')}</label>
                        </td>
                        <td class="posting-form-input">
                            <textarea name="wordswordswords" id="durrwords" cols="48" rows="6"></textarea>
                        </td>
                    </tr>
    {{ for $i = 1, $j = 2; $i <= BS_MAX_POST_FILES; ++$i, ++$j }}
        {{ if $i === 1 }}
                    <tr class="posting-form-row" id="file{$i}">
        {{ else }}
                    <tr class="posting-form-row none" id="file{$i}">
        {{ endif }}
                        <td class="posting-form-label">
                            <label for="durrfile{$i}">{nel_stext('FORM_LABEL_FILE')} #{$i}</label></td>
                        <td class="posting-form-input">
                            <input type="file" name="upfile{$i}" id="durrfile{$i}" onchange="addMoarInput('file{$j}',false)">&nbsp;
                            <input type="button" value="Add Source" id="addsrc{$i}" onClick="addMoarInput('src{$i}',true)">&nbsp;
                            <input type="button" value="Add License" id="addlcns{$i}" onClick="addMoarInput('lcns{$i}',true)">
                        </td>
                    </tr>
                    <tr class="posting-form-row none" id="src{$i}">
                        <td class="posting-form-label">
                            <label for="fs{$i}">{nel_stext('FORM_LABEL_SOURCE')}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="text" name="sauce{$i}" id="fs{$i}" maxlength="{BS_MAX_SOURCE_LENGTH}">
                        </td>
                    </tr>
                    <tr class="posting-form-row none" id="lcns{$i}">
                        <td class="posting-form-label">
                            <label for="lc{$i}">{nel_stext('FORM_LABEL_LICENSE')}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="text" name="loldrama{$i}" id="lc{$i}" maxlength="{BS_MAX_LICENSE_LENGTH}">
                        </td>
                    </tr>
    {{ endfor }}
{{ if BS_USE_FGSFDS }}
                    <tr class="posting-form-row">
                        <td class="posting-form-label">
                            <label for="lolwut">{BS_FGSFDS_NAME}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="text" name="fgsfds" id="lolwut" size="30">
                        </td>
                    </tr>
{{ endif }}
                    <tr class="posting-form-row">
                        <td class="posting-form-label">
                            <label for="durrpass">{nel_stext('FORM_LABEL_PASS')}</label>
                        </td>
                        <td class="posting-form-input">
                            <input type="password" name="sekrit" id="durrpass" size="12" maxlength="16" value="">&nbsp;&nbsp;{nel_stext('TEXT_PASS_WAT')}
                        </td>
                    </tr>
                    <tr class="posting-form-row">
                        <td class="posting-form-label"></td>
                        <td class="posting-form-input">
                            <input type="submit" value="{nel_stext('FORM_SUBMIT')}">&nbsp;&nbsp;&nbsp;&nbsp;<input type="reset" value="{nel_stext('FORM_RESET')}">
                        </td>
                    </tr>
                    <tr class="posting-form-row">
{{ if $render->retrieve_data('response_id') > 0 }}
                        <td class="posting-form-label"></td>
                        <td class="posting-form-input">
                            {nel_stext('TEXT_REPLYMODE')}
                        </td>
{{ else }}
                        <td class="posting-form-label"></td>
                        <td class="posting-form-input">
                            {nel_stext('TEXT_THREADMODE')}
                        </td>
{{ endif }}
                    </tr>
                    <tr class="posting-form-row">
                        <td colspan="2" class="rules">
                            <ul>
                                {$render->retrieve_data('rules_list')}
                                <li>{nel_stext('POSTING_RULES1_1')}{BS_MAX_FILESIZE}{nel_stext('POSTING_RULES1_2')}</li>
                                <li>{nel_stext('POSTING_RULES2_1')}{BS_MAX_WIDTH} x {BS_MAX_HEIGHT}{nel_stext('POSTING_RULES2_2')}</li>
                            </ul>
                        </td>
                    </tr>
{{ if BS_USE_SPAMBOT_TRAP }}
                    <tr class="none">
                        <td>
                            <label for="thename1">{nel_stext('TEXT_SPAMBOT_TRAP')}</label>
                        </td>
                        <td>
                            <input type="text" name="{nel_stext('TEXT_SPAMBOT_FIELD1')}" id="thename1" size="30">
                        </td>
                    </tr>
                    <tr class="none">
                        <td>
                            <label for="theurl1">{nel_stext('TEXT_SPAMBOT_TRAP')}</label>
                        </td>
                        <td>
                            <input type="text" name="{nel_stext('TEXT_SPAMBOT_FIELD2')}" id="theurl1" size="60">
                        </td>
                    </tr>
{{ endif }}
                </table>
            </form>
        </div>
    </div>
    <hr>
    <form accept-charset="utf-8" action="{$render->retrieve_data('dotdot').PHP_SELF}" method="post">
        <div class="outer-div">