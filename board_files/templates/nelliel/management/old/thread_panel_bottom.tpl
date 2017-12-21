        </table>
        <div>
            [ <b>{nel_stext('MANAGE_FILESIZE_TOTAL')} {$render->get('all_filesize')}</b> KB ]
        </div>
        <div>
            <input type="submit" name="dostuff" value="Update">&nbsp;
            <input type=reset value="{nel_stext('FORM_RESET')}">
       </div>
    </form>
{{ if $render->get('expand_thread') }}
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="admin->thread->panel">
        <input type="submit" value="{nel_stext('FORM_RETURN_THREAD')}">
    </form>
{{ endif }}