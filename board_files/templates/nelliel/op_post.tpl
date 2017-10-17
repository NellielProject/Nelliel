{{ if $render->retrieve_data('insert_hr') }}
            </div>
            <hr class="clear">
{{ else }}
            <a id="p{ $render->retrieve_data('post_number') }"></a>
            <div class="op-post">
                <input type="checkbox" name="thread_{$render->retrieve_data('post_number')}_{$render->retrieve_data('parent_thread')}" value="deletethread_{$render->retrieve_data('post_number')}_{$render->retrieve_data('parent_thread')}" title="Delete entire post"><span class="op-subject">{ $render->retrieve_data('subject') }</span>
                <span class="op-poster-name">
    {{ if $render->retrieve_data('email') }}
                <a href="mailto:{ $render->retrieve_data('email') }" class="mailto-name">{ $render->retrieve_data('poster_name') }</a>{ $render->retrieve_data('tripcode') }{ $render->retrieve_data('secure_tripcode') }&nbsp;&nbsp;{ $render->retrieve_data('staff_post') }
    {{ else }}
                { $render->retrieve_data('poster_name') }{ $render->retrieve_data('tripcode') }{ $render->retrieve_data('secure_tripcode') }&nbsp;&nbsp;{ $render->retrieve_data('staff_post') }
    {{ endif }}
                </span>
    {{ if $render->retrieve_data('response_id') }}
                { $render->retrieve_data('post_time') } No. <a href="javascript:postQuote('{ $render->retrieve_data('post_number') }')" class="post-link">{ $render->retrieve_data('post_number') }</a>&nbsp;
    {{ else }}
                { $render->retrieve_data('post_time') } No. <a href="{ PAGE_DIR }{ $render->retrieve_data('post_number') }/{ $render->retrieve_data('post_number') }.html" class="post-link">{ $render->retrieve_data('post_number') }</a>&nbsp;
    {{ endif }}
    {{ if $render->retrieve_data('sticky') }}
                <img src="{ $render->retrieve_data('dotdot') }{ BOARD_FILES }/imagez/nelliel/{ nel_stext('THREAD_STICKY_ICON') }" width="22" height="22" alt="{ nel_stext('THREAD_STICKY') }">
    {{ endif }}
    {{ if !$render->retrieve_data('response_id') }}
        {{ if $render->retrieve_data('logged_in') }}
                [<a href="{ PHP_SELF }?mode=display&post={ $render->retrieve_data('post_number') }">{ nel_stext('LINK_REPLY') }</a>]
        {{ else }}
                [<a href="{ PAGE_DIR }{ $render->retrieve_data('post_number') }/{ $render->retrieve_data('post_number') }.html">{ nel_stext('LINK_REPLY') }</a>]
        {{ endif }}
    {{ endif }}
    {{ if $render->retrieve_data('expand_post') && $render->retrieve_data('logged_in') }}
                [<a href="javascript:clientSideInclude('expand{ $render->retrieve_data('post_number') }', 'expLink{ $render->retrieve_data('post_number') }', '{ PHP_SELF }?mode=display&post={ $render->retrieve_data('post_number') }&expand=TRUE', '{ PHP_SELF }?mode=display&post={ $render->retrieve_data('post_number') }&collapse=TRUE', 'Collapse thread')" id="expLink{ $render->retrieve_data('post_number') }">{ nel_stext('THREAD_EXPAND') }</a>]
    {{ elseif $render->retrieve_data('expand_post') && !$render->retrieve_data('logged_in') }}
                [<a href="javascript:clientSideInclude('expand{ $render->retrieve_data('post_number') }', 'expLink{ $render->retrieve_data('post_number') }', '{ PAGE_DIR }{ $render->retrieve_data('post_number') }/{ $render->retrieve_data('post_number') }-expand.html', '{ PAGE_DIR }{ $render->retrieve_data('post_number') }/{ $render->retrieve_data('post_number') }-collapse.html', 'Collapse thread')" id="expLink{ $render->retrieve_data('post_number') }">{ nel_stext('THREAD_EXPAND') }</a>]
    {{ endif }}
    {{ if $render->retrieve_data('first100') }}
                [<a href="{ PAGE_DIR }{ $render->retrieve_data('post_number') }/{ $render->retrieve_data('post_number') }-0-100.html">First 100 Posts</a>]
    {{ endif }}
                <br>
    {{ if $render->retrieve_data('logged_in') }}
                <br>IP: <b>{ $render->retrieve_data('ip_address') }</b>
        {{ if nel_get_authorization()->get_user_perm($_SESSION['username'], 'perm_ban_add') }}
                <input type="button" onClick="addBanDetails('ban{ $render->retrieve_data('post_number') }', '{ $render->retrieve_data('post_number') }', '{ $render->retrieve_data('poster_name') }', '{ $render->retrieve_data('ip_address') }')" value="Set Ban Details">
        {{ endif }}
    {{ endif }}
                <div class="clear"></div>
    {{ if $render->retrieve_data('has_file') }}
        {{ foreach $render->retrieve_data('files') as $file }}
            {{ if $render->retrieve_data('multifile') }}
                <div class="op-multiple-fileinfo">
                {{ if BS_USE_NEW_IMGDEL }}
                    <input type="checkbox" name="fileid{ $render->retrieve_data('post_number') }_{ $file['file_order'] }" value="deletefile_{ $render->retrieve_data('post_number') }_{ $file['file_order'] }" title="Delete file" class="multi-file-delete-box">
                {{ endif }}
                    <a href="{ $file['file_location'] }" rel="external">{ $file['display_filename'] }.{ $file['extension'] }</a>
                    <br>{{ if $file['img_dim'] }}{ $file['image_width'] } x { $file['image_height'] }{{ endif }} ({ $file['filesize'] } KB)
                    <br>[<a href="javascript:displayImgMeta('imgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }','showimgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }','none','{ nel_stext('THREAD_LESS_DATA') }')" id="showimgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }">{ nel_stext('THREAD_MOAR_DATA') }</a>]
                    <span id="imgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }" class="none">
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
                {{ if BS_USE_NEW_IMGDEL }}
                    <span class="file-delete-box"><input type="checkbox" name="fileid{ $render->retrieve_data('post_number') }_{ $file['file_order'] }" value="deletefile_{ $render->retrieve_data('post_number') }_{ $file['file_order'] }" title="Delete file"></span>
                {{ endif }}
                    <a href="{ $file['file_location'] }" rel="external">{ $file['display_filename'] }.{ $file['extension'] }</a> -&nbsp;
                {{ if $file['img_dim'] }}{ $file['image_width'] } x { $file['image_height'] }{{ endif }} ({ $file['filesize'] } KB)
                    [<a href="javascript:displayImgMeta('imgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }','showimgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }','none','{ nel_stext('THREAD_LESS_DATA') }')" id="showimgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }">{ nel_stext('THREAD_MOAR_DATA') }</a>]
                    <span id="imgmeta{ $render->retrieve_data('post_number') }_{ $file['file_order'] }" class="none">
                {{ if $file['source'] != '' }}
                    <br><span class="source">Source: { $file['source'] }</span>
                {{ endif }}
                {{ if $file['license'] != '' }}
                    <br><span class="license">License: { $file['license'] }</span>
                {{ endif }}
                    <br><span class="md5">MD5: { $file['md5'] }</span>
                    {{ if $file['sha1'] != '' }}
                    <br><span class="sha1">SHA1: { $file['sha1'] }</span>
                    {{ endif }}
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
            	   { $render->retrieve_data('comment') }
            	   <span class="mod-comment"><b>{ $render->retrieve_data('mod_comment') }</b></span>
                </p>
                <div id="ban{ $render->retrieve_data('post_number') }"></div>
            </div>
    {{ if $render->retrieve_data('omitted_posts') }}
                <span class="omitted-posts">{$render->retrieve_data('omitted_count')}{nel_stext('TEXT_OMITTED_POSTS')}</span><br>
    {{ endif }}
            <div id="expand{ $render->retrieve_data('post_number') }">
{{ endif }}