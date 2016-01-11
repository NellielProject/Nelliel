    <div class="pass-valid">
        {nel_stext('MANAGE_MODE')}
    </div>
    <div class="del-list">
        {nel_stext('MANAGE_THREADS')}
    </div>
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype"multipart/form-data">
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="adminmode" value="updatethread">
        <table class="post-lists">
            <tr class="manage-header">
{{ if !$render->retrieve_data('expand_thread') }}
                <th>{nel_stext('MANAGE_THREAD_EXPAND')}</th>
{{ else }}
                <th></th>
{{ endif }}
                <th>{nel_stext('MANAGE_THREAD_POST_NUM')}</th>
                <th>{nel_stext('MANAGE_THREAD_DELETE')}</th>
                <th>{nel_stext('MANAGE_THREAD_STICKY')}</th>
                <th>{nel_stext('MANAGE_THREAD_UNSTICKY')}</th>
                <th>{nel_stext('MANAGE_THREAD_TIME')}</th>
                <th>{nel_stext('MANAGE_THREAD_SUBJECT')}</th>
                <th>{nel_stext('MANAGE_THREAD_NAME')}</th>
                <th>{nel_stext('MANAGE_THREAD_COMMENT')}</th>
                <th>{nel_stext('MANAGE_THREAD_HOST')}</th>
                <th>{nel_stext('MANAGE_THREAD_FILE')}</th>
            </tr>