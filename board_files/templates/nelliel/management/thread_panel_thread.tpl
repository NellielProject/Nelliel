            <tr class="{nel_render_out('bg_class')}">
{{ if !nel_render_out('expand_thread') }}
                <td>
                    <input type="submit" name="expand_thread" value="{nel_stext('FORM_EXPAND')} {nel_render_out('post_number')}">
                </td>
{{ else }}
                <td>
                </td>
{{ endif }}
                <td>{nel_render_out('post_number')}</td>
                <td>
{{ if nel_render_out('is_op') }}
                    <input type="checkbox" name="thread_{nel_render_out('post_number')}" value="deletethread_{nel_render_out('post_number')}" title="Delete entire post">(OP)
{{ else }}
                    <input type="checkbox" name="post_{nel_render_out('post_number')}_{nel_render_out('response_to')}" value="deletepost_{nel_render_out('post_number')}_{nel_render_out('response_to')}" title="Delete entire post">
{{ endif }}
                </td>
{{ if nel_render_out('sticky') }}
                <td>
                </td>
                <td>
                    <input type="checkbox" name="{nel_render_out('post_number')}" value="unsticky_{nel_render_out('post_number')}">
                </td>
{{ else }}
                <td>
                    <input type="checkbox" name="{nel_render_out('post_number')}" value="sticky_{nel_render_out('post_number')}">
                </td>
                <td>
                </td>
{{ endif }}
                <td>
                    {nel_render_out('post_time')}
                </td>
                <td>
                    <a href="{PAGE_DIR}{nel_render_out('post_number')}/{nel_render_out('post_number')}.html" rel="external">{nel_render_out('subject')}</a>
                </td>
                <td>
                    {nel_render_out('name')}
                </td>
                <td>
                    {nel_render_out('comment')}
                </td>
                <td>
                    {nel_render_out('host')}
                </td>
{{ if nel_render_out('has_file') }}
                <td>
                    <table>
    {{ foreach nel_render_out('files') as $file }}
                        <tr>
                            <td>
                                <input type="checkbox" name="fileid{nel_render_out('post_number')}_{$file['file_order']}" value="deletefile_{nel_render_out('post_number')}_{$file['file_order']}" title="Delete file">
                            </td>
        {{ if nel_render_out('response_to') == 0 }}
                            <td>
                                <a href="{SRC_DIR}{nel_render_out('post_number')}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a> ( {$file['filesize']} KB )<br>MD5: {$file['md5']}
                            </td>
                        </tr>
        {{ else }}
                            <td>
                                <a href="{SRC_DIR}{nel_render_out('response_to')}/{$file['filename']}{$file['extension']}" rel="external">{$file['filename']}{$file['extension']}</a> ( {$file['filesize']} KB )<br>MD5: {$file['md5']}
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