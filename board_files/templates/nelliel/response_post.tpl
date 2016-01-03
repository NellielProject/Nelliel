{{ if nel_render_out('omitted_posts') }}
                <span class="omitted-posts">{nel_render_out('omitted_count')}{nel_stext('TEXT_OMITTED_POSTS')}</span><br>
{{ endif }}
                <a id="p{nel_render_out('post_number')}"></a>
                <div class="indents">&gt;&gt;</div>
                <div class="reply-post">
                    <input type="checkbox" name="thread_{nel_render_out('post_number')}" value="deletethread_{nel_render_out('post_number')}" title="Delete entire post"><span class="reply-subject">{nel_render_out('subject')}</span>
                    <span class="reply-poster-name">
{{ if nel_render_out('email') }}
                    <a href="mailto:{nel_render_out('email')}" class="mailto-name">{nel_render_out('name')}</a>{nel_render_out('tripcode')}{nel_render_out('secure_tripcode')}&nbsp;&nbsp;{nel_render_out('staff_post')}
{{ else }}
                    {nel_render_out('name')}{nel_render_out('tripcode')}{nel_render_out('secure_tripcode')}&nbsp;&nbsp;{nel_render_out('staff_post')}
{{ endif }}
                    </span>
{{ if nel_render_out('response_id') }}
                    {nel_render_out('post_time')} No. <a href="javascript:postQuote('{nel_render_out('post_number')}')" class="post-link">{nel_render_out('post_number')}</a>&nbsp;
{{ else }}
                    {nel_render_out('post_time')} No. <a href="{PAGE_DIR}{nel_render_out('response_to')}/{nel_render_out('response_to')}.html#p{nel_render_out('post_number')}" class="post-link">{nel_render_out('post_number')}</a>&nbsp;
{{ endif }}
{{ if nel_render_out('sticky') }}
                    <img src="{nel_render_out('dotdot')}{BOARD_FILES}/imagez/nelliel/{nel_stext('THREAD_STICKY_ICON')}" width="22" height="22" alt="{nel_stext('THREAD_STICKY')}">
{{ endif }}
                    <br>
{{ if nel_render_out('logged_in') }}
                    <br>IP: <b>{nel_render_out('host')}</b>
    {{ if $_SESSION['perms']['perm_ban'] }}
                    <input type="button" onClick="addBanDetails('ban{nel_render_out('post_number')}', '{nel_render_out('post_number')}', '{nel_render_out('name')}', '{nel_render_out('host')}')" value="Set Ban Details">
    {{ endif }}
{{ endif }}
                    <div class="clear"></div>
{{ if nel_render_out('has_file') }}
    {{ foreach nel_render_out('files') as $file }}
        {{ if nel_render_out('multifile') }}
                        <div class="reply-multiple-fileinfo">
            {{ if BS1_USE_NEW_IMGDEL }}
                        <input type="checkbox" name="fileid{nel_render_out('post_number')}_{$file['file_order']}" value="deletefile_{nel_render_out('post_number')}_{$file['file_order']}" title="Delete file" class="multi-file-delete-box">
            {{ endif }}
                        <a href="{$file['file_location']}" rel="external">{$file['filename']}.{$file['extension']}</a>
                        <br>{{ if $file['img_dim'] }}{$file['image_width']} x {$file['image_height']}{{ endif }} ({$file['filesize']} KB)
                        <br>[<a href="javascript:displayImgMeta('imgmeta{nel_render_out('post_number')}_{$file['file_order']}','showimgmeta{nel_render_out('post_number')}_{$file['file_order']}','none','{nel_stext('THREAD_LESS_DATA')}')" id="showimgmeta{nel_render_out('post_number')}_{$file['file_order']}">{nel_stext('THREAD_MOAR_DATA')}</a>]
                        <span id="imgmeta{nel_render_out('post_number')}_{$file['file_order']}" class="none">
            {{ if $file['source'] != '' }}
                        <br><span class="source">Source: {$file['source']}</span>
            {{ endif }}
            {{ if $file['license'] != '' }}
                        <br><span class="license">License: {$file['license']}</span>
            {{ endif }}
                        <br><span class="md5">MD5: {$file['md5']}</span>
                        </span>
                        <br>
            {{ if $file['has_preview'] }}
                        <a href="{$file['file_location']}" rel="external"><img src="{$file['preview_location']}" width="{$file['preview_width']}" height="{$file['preview_height']}" alt="{$file['filesize']} KB" class="reply-post-multiple-preview"></a>
            {{ endif }}
                    </div>
        {{ else }}
                    <div class="reply-fileinfo">
            {{ if BS1_USE_NEW_IMGDEL }}
                        <span class="file-delete-box"><input type="checkbox" name="fileid{nel_render_out('post_number')}_{$file['file_order']}" value="deletefile_{nel_render_out('post_number')}_{$file['file_order']}" title="Delete file"></span>
            {{ endif }}
                        <a href="{$file['file_location']}" rel="external">{$file['filename']}.{$file['extension']}</a> - 
            {{ if $file['img_dim'] }}{$file['image_width']} x {$file['image_height']}{{ endif }} ({$file['filesize']} KB)
                        [<a href="javascript:displayImgMeta('imgmeta{nel_render_out('post_number')}_{$file['file_order']}','showimgmeta{nel_render_out('post_number')}_{$file['file_order']}','none','{nel_stext('THREAD_LESS_DATA')}')" id="showimgmeta{nel_render_out('post_number')}_{$file['file_order']}">{nel_stext('THREAD_MOAR_DATA')}</a>]
                        <span id="imgmeta{nel_render_out('post_number')}_{$file['file_order']}" class="none">
            {{ if $file['source'] != '' }}
                        <br><span class="source">Source: {$file['source']}</span>
            {{ endif }}
            {{ if $file['license'] != '' }}
                        <br><span class="license">License: {$file['license']}</span>
            {{ endif }}
                        <br><span class="md5">MD5: {$file['md5']}</span>
                        </span>
                        <br>
            {{ if $file['has_preview'] }}
                        <a href="{$file['file_location']}" rel="external"><img src="{$file['preview_location']}" width="{$file['preview_width']}" height="{$file['preview_height']}" alt="{$file['filesize']} KB" class="reply-post-preview"></a>
            {{ endif }}
                    </div>
        {{ endif }}
    {{ endforeach }}
{{ endif }}
                    <p class="reply-post-text clear">
                        {nel_render_out('comment')}
                        <span class="mod-comment"><b>{nel_render_out('mod_comment')}</b></span>
                    </p>
                    <div id="ban{nel_render_out('post_number')}"></div>
                </div>
                <div class="clear"></div>