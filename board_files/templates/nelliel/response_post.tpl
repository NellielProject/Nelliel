{{ if $rendervar['omitted_posts'] }}
        <span class="omitted-posts">{$rendervar['omitted_count']}{$lang['TEXT_OMITTED_POSTS']}</span><br>
{{ endif }}
            <a id="p{$rendervar['post_number']}"></a>
            <div class="indents">&gt;&gt;</div>
            <div class="reply-post">
                <input type="checkbox" name="thread_{$rendervar['post_number']}" value="deletethread_{$rendervar['post_number']}" title="Delete entire post"><span class="reply-subject">{$rendervar['subject']}</span>
                <span class="reply-poster-name">
    {{ if $rendervar['email'] }}
                <a href="mailto:{$rendervar['email']}" class="mailto-name">{$rendervar['name']}</a>{$rendervar['tripcode']}{$rendervar['secure_tripcode']}&nbsp;&nbsp;{$rendervar['staff_post']}
    {{ else }}
                {$rendervar['name']}{$rendervar['tripcode']}{$rendervar['secure_tripcode']}&nbsp;&nbsp;{$rendervar['staff_post']}
    {{ endif }}
                </span>
    {{ if $rendervar['response_id'] }}
                {$rendervar['post_time']} No. <a href="javascript:postQuote('{$rendervar['post_number']}')" class="post-link">{$rendervar['post_number']}</a>&nbsp;
    {{ else }}
                {$rendervar['post_time']} No. <a href="{PAGE_DIR}{$rendervar['response_to']}/{$rendervar['response_to']}.html#p{$rendervar['post_number']}" class="post-link">{$rendervar['post_number']}</a>&nbsp;
    {{ endif }}
    {{ if $rendervar['sticky'] }}
                <img src="{$rendervar['dotdot']}{BOARD_FILES}/imagez/nelliel/{$lang['THREAD_STICKY_ICON']}" width="22" height="22" alt="{$lang['THREAD_STICKY']}">
    {{ endif }}
                <br>
    {{ if $rendervar['logged_in'] }}
                <br>IP: <b>{$rendervar['host']}</b>
        {{ if $rendervar['perm_ban'] }}
                <input type="button" onClick="addBanDetails('ban{$rendervar['post_number']}', '{$rendervar['post_number']}', '{$rendervar['name']}', '{$rendervar['host']}')" value="Set Ban Details">
        {{ endif }}
    {{ endif }}
                <div class="clear"></div>
    {{ if $rendervar['has_file'] }}
        {{ foreach $rendervar['files'] as $file }}
            {{ if $rendervar['multifile'] }}
                <div class="reply-multiple-fileinfo">
                {{ if BS1_USE_NEW_IMGDEL }}
                    <input type="checkbox" name="fileid{$rendervar['post_number']}_{$file['file_order']}" value="deletefile_{$rendervar['post_number']}_{$file['file_order']}" title="Delete file" class="multi-file-delete-box">
                {{ endif }}
                    <a href="{$file['file_location']}" rel="external">{$file['filename']}.{$file['extension']}</a>
                    <br>{{ if $file['img_dim'] }}{$file['image_width']} x {$file['image_height']}{{ endif }} ({$file['filesize']} KB)
                    <br>[<a href="javascript:displayImgMeta('imgmeta{$rendervar['post_number']}_{$file['file_order']}','showimgmeta{$rendervar['post_number']}_{$file['file_order']}','none','{$lang['THREAD_LESS_DATA']}')" id="showimgmeta{$rendervar['post_number']}_{$file['file_order']}">{$lang['THREAD_MOAR_DATA']}</a>]
                    <span id="imgmeta{$rendervar['post_number']}_{$file['file_order']}" class="none">
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
                    <span class="file-delete-box"><input type="checkbox" name="fileid{$rendervar['post_number']}_{$file['file_order']}" value="deletefile_{$rendervar['post_number']}_{$file['file_order']}" title="Delete file"></span>
                {{ endif }}
                    <a href="{$file['file_location']}" rel="external">{$file['filename']}.{$file['extension']}</a> - 
                {{ if $file['img_dim'] }}{$file['image_width']} x {$file['image_height']}{{ endif }} ({$file['filesize']} KB)
                    [<a href="javascript:displayImgMeta('imgmeta{$rendervar['post_number']}_{$file['file_order']}','showimgmeta{$rendervar['post_number']}_{$file['file_order']}','none','{$lang['THREAD_LESS_DATA']}')" id="showimgmeta{$rendervar['post_number']}_{$file['file_order']}">{$lang['THREAD_MOAR_DATA']}</a>]
                    <span id="imgmeta{$rendervar['post_number']}_{$file['file_order']}" class="none">
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
                {$rendervar['comment']}
                <span class="mod-comment"><b>{$rendervar['mod_comment']}</b></span>
                </p>
            <div id="ban{$rendervar['post_number']}"></div>
        </div>
        <div class="clear"></div>