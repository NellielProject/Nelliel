        <tr class={nel_render_out('bg_class')}>
            <td>
                {nel_render_out('id')}
            </td>
            <td>
                {nel_render_out('type')}
            </td>
            <td>
                {nel_render_out('host')}
            </td>
            <td>
               {nel_render_out('name')}
            </td>
            <td>
                {nel_render_out('reason')}
            </td>
            <td>
                {nel_render_out('ban_expire')}
            </td>
            <td>
               {nel_render_out('appeal')}
            </td>
            <td>
                {nel_render_out('appeal_response')}
            </td>
            <td>
                    {nel_render_out('appeal_status')}
            </td>
            <td>
                <form accept-charset="utf-8" action="{nel_render_out('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin">
                        <input type="hidden" name="adminmode" value="modifyban">
                        <input type="hidden" name="banid" value="{nel_render_out('id')}">
                        <input type="submit" value="{nel_stext('FORM_MOD_BAN')}">
                    </div>
                </form>
            </td>
            <td>
                <form accept-charset="utf-8" action="{nel_render_out('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin">
                        <input type="hidden" name="adminmode" value="removeban">
                        <input type="hidden" name="banid" value="{nel_render_out('id')}">
                        <input type="submit" value="{nel_stext('FORM_REMOVE_BAN')}">
                    </div>
                </form>
            </td>
        </tr>