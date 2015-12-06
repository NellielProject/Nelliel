{{ if $rendervar['thread_panel_form'] }}
<div class="pass-valid">{LANG_MANAGE_MODE}</div>
<div class="del-list">{LANG_MANAGE_THREADS}</div>
<form accept-charset="utf-8" action="imgboard.php" method="post"enctype"multipart/form-data">
    <input type="hidden" name="mode" value="admin"> <input type="hidden" name="adminmode" value="updatethread">
    <table class="post-lists">
        <tr class="manage-header">
            {{ if !$rendervar['expand_thread'] }}
            <th>{LANG_MANAGE_THREAD_EXPAND}</th> {{ else }}
            <th></th> {{ endif }}
            <th>{LANG_MANAGE_THREAD_POST_NUM}</th>
            <th>{LANG_MANAGE_THREAD_DELETE}
            <th>{LANG_MANAGE_THREAD_STICKY}</th>
            <th>{LANG_MANAGE_THREAD_UNSTICKY}</th>
            <th>{LANG_MANAGE_THREAD_TIME}</th>
            <th>{LANG_MANAGE_THREAD_SUBJECT}</th>
            <th>{LANG_MANAGE_THREAD_NAME}</th>
            <th>{LANG_MANAGE_THREAD_COMMENT}</th>
            <th>{LANG_MANAGE_THREAD_HOST}</th>
            <th>{LANG_MANAGE_THREAD_FILE}</th>
        </tr>
        {{ endif }} {{ if $rendervar['thread_panel_loop'] }}
        <tr class="{$rendervar['bg_class']}">
            {{ if !$rendervar['expand_thread'] }}
            <td><input type="submit" name="expand_thread" value="{LANG_FORM_EXPAND} {$rendervar['post_number']}">
                </form></td> {{ else }}
            <td></td> {{ endif }}
            <td>{$rendervar['post_number']}</td>
            <td>{{ if $rendervar['is_op'] }} <input type="checkbox" name="thread_{$rendervar['post_number']}"
                value="deletethread_{$rendervar['post_number']}" title="Delete entire post">(OP) {{ else }} <input type="checkbox"
                name="post_{$rendervar['post_number']}_{$rendervar['response_to']}"
                value="deletepost_{$rendervar['post_number']}_{$rendervar['response_to']}" title="Delete entire post"> {{ endif }}
            </td> {{ if $rendervar['sticky'] }}
            <td></td>
            <td><input type="checkbox" name="{$rendervar['post_number']}" value="unsticky_{$rendervar['post_number']}"></td> {{ else }}
            <td><input type="checkbox" name="{$rendervar['post_number']}" value="sticky_{$rendervar['post_number']}"></td>
            <td></td> {{ endif }}
            <td>{$rendervar['post_time']}</td>
            <td><a href="{PAGE_DIR}{$rendervar['post_number']}/{$rendervar['post_number']}.html" rel="external">{$rendervar['subject']}</a></td>
            <td>{$rendervar['name']}</td>
            <td>{$rendervar['comment']}</td>
            <td>{$rendervar['host']}</td> {{ if $rendervar['has_file'] }}
            <td>
                <table>
                    {{ foreach $rendervar['files'] as $file }}
                    <tr>
                        <td><input type="checkbox" name="fileid{$rendervar['post_number']}_{$file['file_order']}"
                            value="deletefile_{$rendervar['post_number']}_{$file['file_order']}" title="Delete file"></td> {{ if
                        $rendervar['response_to'] == 0 }}
                        <td><a href="{SRC_DIR}{$rendervar['post_number']}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a>
                            ( {$file['filesize']} KB )<br>MD5: {$file['md5']}</td>
                    </tr>
                    {{ else }}
                    <td><a href="{SRC_DIR}{$rendervar['response_to']}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a>
                        ( {$file['filesize']} KB )<br>MD5: {$file['md5']}</td>
                    </tr>
                    {{ endif }} {{ endforeach }}
                </table>
            </td> {{ else }}
            <td></td> {{ endif }}
        </tr>
        {{ endif }} {{ if $rendervar['thread_panel_end'] }}
    </table>
    <div>
        [ <b>{LANG_MANAGE_FILESIZE_TOTAL} {$rendervar['all_filesize']}</b> KB ]
    </div>
    <br> <input type="submit" name="dostuff" value="Update">&nbsp;&nbsp;<input type=reset value="{LANG_FORM_RESET}">
    </div>
</form>
<br>
{{ if $rendervar['expand_thread'] }}
<form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="admin"> <input type="hidden" name="adminmode" value="returnthreadlist"> <input
        type="submit" value="{LANG_FORM_RETURN_THREAD}">
</form>
{{ endif }} {{ endif }}
