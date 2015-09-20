{{ if $rendervar['insert_hr'] }}
        </div>
        <br>
        <hr class="clear">
{{ else }}
        <a id="{$rendervar['post_number']}"></a>
        <div class="op-post">
            <input type="checkbox" name="thread_{$rendervar['post_number']}" value="deletethread_{$rendervar['post_number']}" title="Delete entire post"><span class="op-subject">{$rendervar['subject']}</span>
            <span class="op-poster-name">
    {{ if $rendervar['email'] }}
            <a href="mailto:{$rendervar['email']}" class="mailto-name">{$rendervar['name']}</a>{$rendervar['tripcode']}{$rendervar['secure_tripcode']}&nbsp;&nbsp;{$rendervar['staff_post']}
    {{ else }}
                {$rendervar['name']}{$rendervar['tripcode']}{$rendervar['secure_tripcode']}&nbsp;&nbsp;{$rendervar['staff_post']}
    {{ endif }}
            </span>
    {{ if $rendervar['response_id'] }}
            {$rendervar['post_time']} No. <a href="javascript:postQuote('{$rendervar['post_number']}')" class="post-link">{$rendervar['post_number']}</a>&nbsp;
    {{ else }}
            {$rendervar['post_time']} No. <a href="{PAGE_DIR}{$rendervar['post_number']}/{$rendervar['post_number']}.html" class="post-link">{$rendervar['post_number']}</a>&nbsp;
    {{ endif }}
    {{ if $rendervar['sticky'] }}
            <img src="{$rendervar['dotdot']}{BOARD_FILES}/imagez/nelliel/{LANG_THREAD_STICKY_ICON}" width="22" height="22" alt="{LANG_THREAD_STICKY}">
    {{ endif }}
    {{ if !$rendervar['response_id'] }}
        {{ if $rendervar['logged_in'] }}
            [<a href="{PHP_SELF}?mode=display&post={$rendervar['post_number']}">{LANG_LINK_REPLY}</a>]
        {{ else }}
            [<a href="{PAGE_DIR}{$rendervar['post_number']}/{$rendervar['post_number']}.html">{LANG_LINK_REPLY}</a>]
        {{ endif }}
    {{ endif }}
    {{ if $rendervar['expand_post'] && $rendervar['logged_in'] }}
            [<a href="javascript:clientSideInclude('expand{$rendervar['post_number']}', 'expLink{$rendervar['post_number']}', '{PHP_SELF}?mode=display&post={$rendervar['post_number']}&expand=TRUE', '{PHP_SELF}?mode=display&post={$rendervar['post_number']}&collapse=TRUE', 'Collapse thread')" id="expLink{$rendervar['post_number']}">{LANG_THREAD_EXPAND}</a>]
    {{ elseif $rendervar['expand_post'] && !$rendervar['logged_in'] }}
            [<a href="javascript:clientSideInclude('expand{$rendervar['post_number']}', 'expLink{$rendervar['post_number']}', '{PAGE_DIR}{$rendervar['post_number']}/{$rendervar['post_number']}-expand.html', '{PAGE_DIR}{$rendervar['post_number']}/{$rendervar['post_number']}-collapse.html', 'Collapse thread')" id="expLink{$rendervar['post_number']}">{LANG_THREAD_EXPAND}</a>]
    {{ endif }}
    {{ if $rendervar['first100'] }}
            [<a href="{PAGE_DIR}{$rendervar['post_number']}/{$rendervar['post_number']}-0-100.html">First 100 Posts</a>]
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
            <div class="op-multiple-fileinfo">
                {{ if BS1_USE_NEW_IMGDEL }}
                <input type="checkbox" name="fileid{$rendervar['post_number']}_{$file['ord']}" value="deletefile_{$rendervar['post_number']}_{$file['ord']}" title="Delete file" class="multi-file-delete-box">
                {{ endif }}
                <a href="{$file['file_location']}" rel="external">{$file['filename']}.{$file['extension']}</a>
                <br>{{ if $file['img_dim'] }}{$file['image_width']} x {$file['image_height']}{{ endif }} ({$file['filesize']} KB)
                <br>[<a href="javascript:displayImgMeta('imgmeta{$rendervar['post_number']}_{$file['ord']}','showimgmeta{$rendervar['post_number']}_{$file['ord']}','none','{LANG_THREAD_LESS_DATA}')" id="showimgmeta{$rendervar['post_number']}_{$file['ord']}">{LANG_THREAD_MOAR_DATA}</a>]
                <span id="imgmeta{$rendervar['post_number']}_{$file['ord']}" class="none">
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
                        <a href="{$file['file_location']}" rel="external"><img src="{$file['preview_location']}" width="{$file['preview_width']}" height="{$file['preview_height']}" alt="{$file['filesize']} KB" class="op-post-multiple-preview"></a>
                {{ endif }}
            </div>
            {{ else }}
            <div class="op-fileinfo">
                {{ if BS1_USE_NEW_IMGDEL }}
                <span class="file-delete-box"><input type="checkbox" name="fileid{$rendervar['post_number']}_{$file['ord']}" value="deletefile_{$rendervar['post_number']}_{$file['ord']}" title="Delete file"></span>
                {{ endif }}
                <a href="{$file['file_location']}" rel="external">{$file['filename']}.{$file['extension']}</a> - 
                {{ if $file['img_dim'] }}{$file['image_width']} x {$file['image_height']}{{ endif }} ({$file['filesize']} KB)
                [<a href="javascript:displayImgMeta('imgmeta{$rendervar['post_number']}_{$file['ord']}','showimgmeta{$rendervar['post_number']}_{$file['ord']}','none','{LANG_THREAD_LESS_DATA}')" id="showimgmeta{$rendervar['post_number']}_{$file['ord']}">{LANG_THREAD_MOAR_DATA}</a>]
                <span id="imgmeta{$rendervar['post_number']}_{$file['ord']}" class="none">
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
                <a href="{$file['file_location']}" rel="external"><img src="{$file['preview_location']}" width="{$file['preview_width']}" height="{$file['preview_height']}" alt="{$file['filesize']} KB" class="op-post-preview"></a>
                {{ endif }}
            </div>
            {{ endif }}
        {{ endforeach }}
    {{ endif }}
            <p class="op-post-text">
            {$rendervar['comment']}
            <span class="mod-comment"><b>{$rendervar['mod_comment']}</b></span>
            </p>
            <div id="ban{$rendervar['post_number']}"></div>
        </div>
        <div id="expand{$rendervar['post_number']}">
{{ endif }}