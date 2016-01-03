{{ if nel_render_out('insert_hr') }}
            </div>
            <hr class="clear">
{{ else }}
            <a id="p{ nel_render_out('post_number') }"></a>
            <div class="op-post">
                <input type="checkbox" name="thread_{ nel_render_out('post_number') }" value="deletethread_{ nel_render_out('post_number') }" title="Delete entire post"><span class="op-subject">{ nel_render_out('subject') }</span>
                <span class="op-poster-name">
    {{ if nel_render_out('email') }}
                <a href="mailto:{ nel_render_out('email') }" class="mailto-name">{ nel_render_out('name') }</a>{ nel_render_out('tripcode') }{ nel_render_out('secure_tripcode') }&nbsp;&nbsp;{ nel_render_out('staff_post') }
    {{ else }}
                { nel_render_out('name') }{ nel_render_out('tripcode') }{ nel_render_out('secure_tripcode') }&nbsp;&nbsp;{ nel_render_out('staff_post') }
    {{ endif }}
                </span>
    {{ if nel_render_out('response_id') }}
                { nel_render_out('post_time') } No. <a href="javascript:postQuote('{ nel_render_out('post_number') }')" class="post-link">{ nel_render_out('post_number') }</a>&nbsp;
    {{ else }}
                { nel_render_out('post_time') } No. <a href="{ PAGE_DIR }{ nel_render_out('post_number') }/{ nel_render_out('post_number') }.html" class="post-link">{ nel_render_out('post_number') }</a>&nbsp;
    {{ endif }}
    {{ if nel_render_out('sticky') }}
                <img src="{ nel_render_out('dotdot') }{ BOARD_FILES }/imagez/nelliel/{ nel_stext('THREAD_STICKY_ICON') }" width="22" height="22" alt="{ nel_stext('THREAD_STICKY') }">
    {{ endif }}
    {{ if !nel_render_out('response_id') }}
        {{ if nel_render_out('logged_in') }}
                [<a href="{ PHP_SELF }?mode=display&post={ nel_render_out('post_number') }">{ nel_stext('LINK_REPLY') }</a>]
        {{ else }}
                [<a href="{ PAGE_DIR }{ nel_render_out('post_number') }/{ nel_render_out('post_number') }.html">{ nel_stext('LINK_REPLY') }</a>]
        {{ endif }}
    {{ endif }}
    {{ if nel_render_out('expand_post') && nel_render_out('logged_in') }}
                [<a href="javascript:clientSideInclude('expand{ nel_render_out('post_number') }', 'expLink{ nel_render_out('post_number') }', '{ PHP_SELF }?mode=display&post={ nel_render_out('post_number') }&expand=TRUE', '{ PHP_SELF }?mode=display&post={ nel_render_out('post_number') }&collapse=TRUE', 'Collapse thread')" id="expLink{ nel_render_out('post_number') }">{ nel_stext('THREAD_EXPAND') }</a>]
    {{ elseif nel_render_out('expand_post') && !nel_render_out('logged_in') }}
                [<a href="javascript:clientSideInclude('expand{ nel_render_out('post_number') }', 'expLink{ nel_render_out('post_number') }', '{ PAGE_DIR }{ nel_render_out('post_number') }/{ nel_render_out('post_number') }-expand.html', '{ PAGE_DIR }{ nel_render_out('post_number') }/{ nel_render_out('post_number') }-collapse.html', 'Collapse thread')" id="expLink{ nel_render_out('post_number') }">{ nel_stext('THREAD_EXPAND') }</a>]
    {{ endif }}
    {{ if nel_render_out('first100') }}
                [<a href="{ PAGE_DIR }{ nel_render_out('post_number') }/{ nel_render_out('post_number') }-0-100.html">First 100 Posts</a>]
    {{ endif }}
                <br>
    {{ if nel_render_out('logged_in') }}
                <br>IP: <b>{ nel_render_out('host') }</b>
        {{ if $_SESSION['perms']['perm_ban'] }}
                <input type="button" onClick="addBanDetails('ban{ nel_render_out('post_number') }', '{ nel_render_out('post_number') }', '{ nel_render_out('name') }', '{ nel_render_out('host') }')" value="Set Ban Details">
        {{ endif }}
    {{ endif }}
                <div class="clear"></div>
    {{ if nel_render_out('has_file') }}
        {{ foreach nel_render_out('files') as $file }}
            {{ if nel_render_out('multifile') }}
                <div class="op-multiple-fileinfo">
                {{ if BS1_USE_NEW_IMGDEL }}
                    <input type="checkbox" name="fileid{ nel_render_out('post_number') }_{ $file['file_order'] }" value="deletefile_{ nel_render_out('post_number') }_{ $file['file_order'] }" title="Delete file" class="multi-file-delete-box">
                {{ endif }}
                    <a href="{ $file['file_location'] }" rel="external">{ $file['filename'] }.{ $file['extension'] }</a>
                    <br>{{ if $file['img_dim'] }}{ $file['image_width'] } x { $file['image_height'] }{{ endif }} ({ $file['filesize'] } KB)
                    <br>[<a href="javascript:displayImgMeta('imgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }','showimgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }','none','{ nel_stext('THREAD_LESS_DATA') }')" id="showimgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }">{ nel_stext('THREAD_MOAR_DATA') }</a>]
                    <span id="imgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }" class="none">
                {{ if $file['source'] != '' }}
                    <br><span class="source">Source: { $file['source'] }</span>
                {{ endif }}
                {{ if $file['license'] != '' }}
                    <br><span class="license">License: { $file['license'] }</span>
                {{ endif }}
                    <br><span class="md5">MD5: { $file['md5'] }</span>
                    </span>
                    <br>
                {{ if $file['has_preview'] }}
                    <a href="{ $file['file_location'] }" rel="external"><img src="{ $file['preview_location'] }" width="{ $file['preview_width'] }" height="{ $file['preview_height'] }" alt="{ $file['filesize'] } KB" class="op-post-multiple-preview"></a>
                {{ endif }}
                </div>
            {{ else }}
                <div class="op-fileinfo">
                {{ if BS1_USE_NEW_IMGDEL }}
                    <span class="file-delete-box"><input type="checkbox" name="fileid{ nel_render_out('post_number') }_{ $file['file_order'] }" value="deletefile_{ nel_render_out('post_number') }_{ $file['file_order'] }" title="Delete file"></span>
                {{ endif }}
                    <a href="{ $file['file_location'] }" rel="external">{ $file['filename'] }.{ $file['extension'] }</a> -&nbsp;
                {{ if $file['img_dim'] }}{ $file['image_width'] } x { $file['image_height'] }{{ endif }} ({ $file['filesize'] } KB)
                    [<a href="javascript:displayImgMeta('imgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }','showimgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }','none','{ nel_stext('THREAD_LESS_DATA') }')" id="showimgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }">{ nel_stext('THREAD_MOAR_DATA') }</a>]
                    <span id="imgmeta{ nel_render_out('post_number') }_{ $file['file_order'] }" class="none">
                {{ if $file['source'] != '' }}
                    <br><span class="source">Source: { $file['source'] }</span>
                {{ endif }}
                {{ if $file['license'] != '' }}
                    <br><span class="license">License: { $file['license'] }</span>
                {{ endif }}
                    <br><span class="md5">MD5: { $file['md5'] }</span>
                    </span>
                    <br>
                {{ if $file['has_preview'] }}
                    <a href="{ $file['file_location'] }" rel="external"><img src="{ $file['preview_location'] }" width="{ $file['preview_width'] }" height="{ $file['preview_height'] }" alt="{ $file['filesize'] } KB" class="op-post-preview"></a>
                {{ endif }}
                </div>
            {{ endif }}
        {{ endforeach }}
    {{ endif }}
                <p class="op-post-text">
            	   { nel_render_out('comment') }
            	   <span class="mod-comment"><b>{ nel_render_out('mod_comment') }</b></span>
                </p>
                <div id="ban{ nel_render_out('post_number') }"></div>
            </div>
            <div id="expand{ nel_render_out('post_number') }">
{{ endif }}