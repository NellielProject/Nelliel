        <tr class={$render->get('bg_class')}>
            <td>
                {$render->get('id')}
            </td>
            <td>
                {$render->get('type')}
            </td>
            <td>
                {$render->get('ip_address')}
            </td>
            <td>
               {$render->get('name')}
            </td>
            <td>
                {$render->get('reason')}
            </td>
            <td>
                {$render->get('ban_expire')}
            </td>
            <td>
               {$render->get('appeal')}
            </td>
            <td>
                {$render->get('appeal_response')}
            </td>
            <td>
                    {$render->get('appeal_status')}
            </td>
            <td>
                <form accept-charset="utf-8" action="{$render->get('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin->ban->modify">
                        <input type="hidden" name="banid" value="{$render->get('id')}">
                        <input type="submit" value="{nel_stext('FORM_MOD_BAN')}">
                    </div>
                </form>
            </td>
            <td>
                <form accept-charset="utf-8" action="{$render->get('dotdot')}{PHP_SELF}" method="post">
                    <div>
                        <input type="hidden" name="mode" value="admin->ban->remove">
                        <input type="hidden" name="banid" value="{$render->get('id')}">
                        <input type="submit" value="{nel_stext('FORM_REMOVE_BAN')}">
                    </div>
                </form>
            </td>
        </tr>