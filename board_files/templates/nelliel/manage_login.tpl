    <div class="pass-valid">
        {stext('MANAGE_LOGIN')}
    </div>
    <form accept-charset="utf-8" action="{PHP_SELF}" method="post">
        <div>
            <div>
                Username: <input type="text" name="username" size="16"><br>
                Password: <input type="password" name="super_sekrit" size="16"><br>
                <input type="hidden" name="mode" value="admin">
                <input type="hidden" name="adminmode" value="login">
                <input type="submit" value="{stext('FORM_SUBMIT')}">
            </div>
        </div>
    </form>