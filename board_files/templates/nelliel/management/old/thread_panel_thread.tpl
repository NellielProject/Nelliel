            <tr class="{$render->get('bg_class')}">
{{ if !$render->get('expand_thread') }}
                <td>
                    <input type="submit" name="expand_thread" value="{nel_stext('FORM_EXPAND')} {$render->get('parent_thread')}">
                </td>
{{ else }}
                <td>
                </td>
{{ endif }}
                <td>{$render->get('post_number')}</td>
                <td>
{{ if $render->get('is_op') }}
                    <input type="checkbox" name="thread_{$render->get('post_number')}" value="deletethread_{$render->get('parent_thread')}_{$render->get('post_number')}" title="Delete entire post">(OP)
{{ else }}
                    <input type="checkbox" name="post_{$render->get('parent_thread')}_{$render->get('post_number')}" value="deletepost_{$render->get('parent_thread')}_{$render->get('post_number')}" title="Delete entire post">
{{ endif }}
                </td>
{{ if $render->get('sticky') }}
                <td>
                </td>
                <td>
                    <input type="checkbox" name="{$render->get('post_number')}" value="threadunsticky_{$render->get('parent_thread')}_{$render->get('post_number')}">
                </td>
{{ else }}
                <td>
                    <input type="checkbox" name="{$render->get('post_number')}" value="threadsticky_{$render->get('parent_thread')}_{$render->get('post_number')}">
                </td>
                <td>
                </td>
{{ endif }}
                <td>
                    {$render->get('post_time')}
                </td>
                <td>
                    <a href="{PAGE_DIR}{$render->get('post_number')}/{$render->get('post_number')}.html" rel="external">{$render->get('subject')}</a>
                </td>
                <td>
                    {$render->get('poster_name')}
                </td>
                <td>
                    {$render->get('comment')}
                </td>
                <td>
                    {$render->get('ip_address')}
                </td>
{{ if $render->get('has_file') }}
                <td>
                    <table>
    {{ foreach $render->get('files') as $file }}
                        <tr>
                            <td>
                                <input type="checkbox" name="fileid{$render->get('post_number')}_{$file['file_order']}" value="deletefile_{$render->get('parent_thread')}_{$render->get('post_number')}_{$file['file_order']}" title="Delete file">
                            </td>
        {{ if $render->get('response_to') == 0 }}
                            <td>
                                <a href="{SRC_DIR}{$render->get('post_number')}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a> ( {$file['filesize']} KB )<br>MD5: {$file['md5']}<br>SHA1: {$file['sha1']}
                            </td>
                        </tr>
        {{ else }}
                            <td>
                                <a href="{SRC_DIR}{$render->get('response_to')}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a> ( {$file['filesize']} KB )<br>MD5: {$file['md5']}<br>SHA1: {$file['sha1']}
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