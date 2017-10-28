{{ if $render->get('response_id') }}
		<div>
			[<a href="{$render->get('dotdot')}{$render->get('page_ref1')}">{nel_stext('LINK_RETURN')}</a>]
		</div>
{{ endif }}
	<div>
		<div class="posting-form">
			<form accept-charset="utf-8" name="postingform" action="{$render->get('form_submit_url')}" method="post" enctype="multipart/form-data">
				<div>
					<input type="hidden" name="mode" value="new_post">
					<input type="hidden" name="new_post[post_info][response_to]" value="{$render->get('response_id')}">
{{ if $render->get('modmode') }}
					<input type="hidden" name="mode2" value="modmode">
{{ endif }}
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
							<label for="not_anonymous">{nel_stext('FORM_LABEL_NAME')}</label>
						</td>
						<td class="posting-form-input">
							<input type="text" name="new_post[post_info][not_anonymous]" id="not_anonymous" size="30" maxlength="{BS_MAX_NAME_LENGTH}">
						</td>
					</tr>
					<tr class="posting-form-row">
						<td class="posting-form-label">
							<label for="spam_target">{nel_stext('FORM_LABEL_EMAIL')}</label>
						</td>
						<td class="posting-form-input">
							<input type="text" name="new_post[post_info][spam_target]" id="spam_target" size="30" maxlength="{BS_MAX_EMAIL_LENGTH}">
						</td>
					</tr>
{{ endif }}
					<tr class="posting-form-row">
						<td class="posting-form-label">
							<label for="verb">{nel_stext('FORM_LABEL_SUBJECT')}</label>
						</td>
						<td class="posting-form-input">
							<input type="text" name="new_post[post_info][verb]" id="verb" size="30" maxlength="{BS_MAX_SUBJECT_LENGTH}">
						</td>
					</tr>
					<tr class="posting-form-row">
						<td class="posting-form-label">
							<label for="wordswordswords">{nel_stext('FORM_LABEL_COMMENT')}</label>
						</td>
						<td class="posting-form-input">
							<textarea name="new_post[post_info][wordswordswords]" id="wordswordswords" cols="30" rows="6"></textarea>
						</td>
					</tr>
	{{ for $i = 1, $j = 2; $i <= BS_MAX_POST_FILES; ++$i, ++$j }}
		{{ if $i === 1 }}
					<tr class="posting-form-row" id="file_{$i}">
		{{ else }}
					<tr class="posting-form-row none" id="file_{$i}">
		{{ endif }}
						<td class="posting-form-label">
							<label for="up_file_{$i}">{nel_stext('FORM_LABEL_FILE')} #{$i}</label></td>
						<td class="posting-form-input">
							<input type="file" name="up_file_{$i}" id="up_file_{$i}" onchange="addMoarInput('file_{$j}',false)">&nbsp;
							<input type="button" value="Add Source" id="add_sauce_{$i}" onClick="addMoarInput('sauce_{$i}',true)">&nbsp;
							<input type="button" value="Add License" id="add_lol_drama_{$i}" onClick="addMoarInput('lol_drama_{$i}',true)">
						</td>
					</tr>
					<tr class="posting-form-row none" id="sauce_{$i}">
						<td class="posting-form-label">
							<label for="sauce_{$i}">{nel_stext('FORM_LABEL_SOURCE')}</label>
						</td>
						<td class="posting-form-input">
							<input type="text" name="new_post[file_info][file_{$i}][sauce]" id="sauce_{$i}" maxlength="{BS_MAX_SOURCE_LENGTH}">
						</td>
					</tr>
					<tr class="posting-form-row none" id="lol_drama_{$i}">
						<td class="posting-form-label">
							<label for="lol_drama_{$i}">{nel_stext('FORM_LABEL_LICENSE')}</label>
						</td>
						<td class="posting-form-input">
							<input type="text" name="new_post[file_info][file_{$i}][lol_drama]" id="lol_drama_{$i}" maxlength="{BS_MAX_LICENSE_LENGTH}">
						</td>
					</tr>
	{{ endfor }}
{{ if BS_USE_FGSFDS }}
					<tr class="posting-form-row">
						<td class="posting-form-label">
							<label for="fgsfds">{BS_FGSFDS_NAME}</label>
						</td>
						<td class="posting-form-input">
							<input type="text" name="new_post[post_info][fgsfds]" id="fgsfds" size="30">
						</td>
					</tr>
{{ endif }}
					<tr class="posting-form-row">
						<td class="posting-form-label">
							<label for="sekrit">{nel_stext('FORM_LABEL_PASS')}</label>
						</td>
						<td class="posting-form-input">
							<input type="password" name="new_post[post_info][sekrit]" id="sekrit" size="12" maxlength="16" value="">&nbsp;&nbsp;{nel_stext('TEXT_PASS_WAT')}
						</td>
					</tr>
					<tr class="posting-form-row">
						<td class="posting-form-label"></td>
						<td class="posting-form-input">
							<input type="submit" value="{nel_stext('FORM_SUBMIT')}">&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="reset" value="{nel_stext('FORM_RESET')}">
						</td>
					</tr>
					<tr class="posting-form-row">
{{ if $render->get('response_id') > 0 }}
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
								{$render->get('rules_list')}
								<li>{nel_stext('POSTING_RULES1_1')}{BS_MAX_FILESIZE}{nel_stext('POSTING_RULES1_2')}</li>
								<li>{nel_stext('POSTING_RULES2_1')}{BS_MAX_WIDTH} x {BS_MAX_HEIGHT}{nel_stext('POSTING_RULES2_2')}</li>
							</ul>
						</td>
					</tr>
{{ if BS_USE_SPAMBOT_TRAP }}
					<tr class="none">
						<td>
							<label for="website_url">{nel_stext('TEXT_SPAMBOT_TRAP')}</label>
						</td>
						<td>
							<input type="text" name="website_url" id="website_url" size="30" autocomplete="off">
						</td>
					</tr>
					<tr class="none">
						<td>
							<label for="user_comments">{nel_stext('TEXT_SPAMBOT_TRAP')}</label>
						</td>
						<td>
							<input type="text" name="user_comments" id="user_comments" size="60" autocomplete="off">
						</td>
					</tr>
{{ endif }}
				</table>
			</form>
		</div>
	</div>
	<hr>
	<form accept-charset="utf-8" action="{$render->get('dotdot').PHP_SELF}" method="post">
		<div class="outer-div">