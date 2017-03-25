            <tr class="{$render->retrieve_data('bg_class')}">
{{ if !$render->retrieve_data('expand_thread') }}
                <td>
                    <input type="submit" name="expand_thread" value="{nel_stext('FORM_EXPAND')} {$render->retrieve_data('post_number')}">
                </td>
{{ else }}
                <td>
                </td>
{{ endif }}
                <td>{$render->retrieve_data('post_number')}</td>
                <td>
{{ if $render->retrieve_data('is_op') }}
                    <input type="checkbox" name="thread_{$render->retrieve_data('post_number')}" value="deletethread_{$render->retrieve_data('post_number')}" title="Delete entire post">(OP)
{{ else }}
                    <input type="checkbox" name="post_{$render->retrieve_data('post_number')}_{$render->retrieve_data('response_to')}" value="deletepost_{$render->retrieve_data('post_number')}_{$render->retrieve_data('parent_thread')}" title="Delete entire post">
{{ endif }}
                </td>
{{ if $render->retrieve_data('sticky') }}
                <td>
                </td>
                <td>
                    <input type="checkbox" name="{$render->retrieve_data('post_number')}" value="threadunsticky_{$render->retrieve_data('post_number')}">
                </td>
{{ else }}
                <td>
                    <input type="checkbox" name="{$render->retrieve_data('post_number')}" value="threadsticky_{$render->retrieve_data('post_number')}">
                </td>
                <td>
                </td>
{{ endif }}
                <td>
                    {$render->retrieve_data('post_time')}
                </td>
                <td>
                    <a href="{PAGE_DIR}{$render->retrieve_data('post_number')}/{$render->retrieve_data('post_number')}.html" rel="external">{$render->retrieve_data('subject')}</a>
                </td>
                <td>
                    {$render->retrieve_data('name')}
                </td>
                <td>
                    {$render->retrieve_data('comment')}
                </td>
                <td>
                    {$render->retrieve_data('ip_address')}
                </td>
{{ if $render->retrieve_data('has_file') }}
                <td>
                    <table>
    {{ foreach $render->retrieve_data('files') as $file }}
                        <tr>
                            <td>
                                <input type="checkbox" name="fileid{$render->retrieve_data('post_number')}_{$file['file_order']}" value="deletefile_{$render->retrieve_data('post_number')}_{$file['file_order']}" title="Delete file">
                            </td>
        {{ if $render->retrieve_data('response_to') == 0 }}
                            <td>
                                <a href="{SRC_DIR}{$render->retrieve_data('post_number')}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a> ( {$file['filesize']} KB )<br>MD5: {$file['md5']<br>SHA1: {$file['sha1']}
                            </td>
                        </tr>
        {{ else }}
                            <td>
                                <a href="{SRC_DIR}{$render->retrieve_data('response_to')}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a> ( {$file['filesize']} KB )<br>MD5: {$file['md5']<br>SHA1: {$file['sha1']}
                            </td>
                        </tr>
        {{ endif }}
    {{ endforeach }}
                    </table>
                </td>
{{ else }}
                <td>
                </td>
{{ endif }}
            </tr>