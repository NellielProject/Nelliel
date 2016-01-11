        <tr class={$render->retrieve_data('bg_class')}>
            <td>
                {$render->retrieve_data('id')}
            </td>
            <td>
                {$render->retrieve_data('type')}
            </td>
            <td>
                {$render->retrieve_data('host')}
            </td>
            <td>
               {$render->retrieve_data('name')}
            </td>
            <td>
                {$render->retrieve_data('reason')}
            </td>
            <td>
                {$render->retrieve_data('ban_expire')}
            </td>
            <td>
               {$render->retrieve_data('appeal')}
            </td>
            <td>
                {$render->retrieve_data('appeal_response')}
            </td>
            <td>
                    {$render->retrieve_data('appeal_status')}
            </td>
            <td>
                <form accept-charset="utf-8" action="{$render->retrieve_data('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin">
                        <input type="hidden" name="adminmode" value="modifyban">
                        <input type="hidden" name="banid" value="{$render->retrieve_data('id')}">
                        <input type="submit" value="{nel_stext('FORM_MOD_BAN')}">
                    </div>
                </form>
            </td>
            <td>
                <form accept-charset="utf-8" action="{$render->retrieve_data('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin">
                        <input type="hidden" name="adminmode" value="removeban">
                        <input type="hidden" name="banid" value="{$render->retrieve_data('id')}">
                        <input type="submit" value="{nel_stext('FORM_REMOVE_BAN')}">
                    </div>
                </form>
            </td>
        </tr>