    </table>
    <hr>
    <table>
        <tr>
            <td>
                <form accept-charset="utf-8" action="{$render->retrieve_data('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin">
                        <input type="hidden" name="adminmode" value="newban">
                        <input type="submit" value="{nel_stext('FORM_ADD_BAN')}">
                    </div>
                </form>
            </td>
        </tr>
    </table>