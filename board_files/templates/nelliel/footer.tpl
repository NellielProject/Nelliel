{{ if $render->get('del') }}
            <div class="clear"></div>
        </div>
        <table class="footer-form">
            <tr>
                <td>
                    <input type="hidden" name="mode" value="update">
    {{ if !nel_session_is_ignored('render') }}
                    <input type="checkbox" name="delpost" id="dpost"><label for="dpost">{nel_stext('FORM_DELETE_POSTS')}</label><br>
                    <input type="checkbox" name="banpost" id="bpost"><label for="bpost">{nel_stext('FORM_BAN_POSTS')}</label><br>
                    <input type="hidden" name="mode" value="admin->modmode">
    {{ endif }}
    {{ if !BS_USE_NEW_IMGDEL }}
                    <input type="checkbox" name="onlyimgdel" id="delfbox" value="on"><label for="delfbox">{nel_stext('DELETE_FILES_ONLY')}</label><br>
    {{ endif }}
    {{ if !nel_session_is_ignored('render') }}
                    <input type="submit" value="{nel_stext('FORM_SUBMIT')}">
    {{ else }}
                    <label for="delpass">{nel_stext('FORM_LABEL_PASS')}</label><input type="password" name="sekrit" id="delpass" size="12" maxlength="16" value="">
                    <input type="submit" value="{nel_stext('FORM_DELETE')}">
    {{ endif }}
                </td>
            </tr>
        </table>
    {{ if $render->get('response') }}
        </div>
    {{ endif }}
<!--    </form> -->
{{ endif }}
{{ if $render->get('styles_link') }}
    <div class="bottom-styles">
        Styles:
        [<a href="#" onclick="changeCSS('Nelliel','style-{CONF_BOARD_DIR}'); return false;">Nelliel</a>]
        [<a href="#" onclick="changeCSS('Futaba','style-{CONF_BOARD_DIR}'); return false;">Futaba</a>]
        [<a href="#" onclick="changeCSS('Burichan','style-{CONF_BOARD_DIR}'); return false;">Burichan</a>]
        [<a href="#" onclick="changeCSS('Nigra','style-{CONF_BOARD_DIR}'); return false;">Nigra</a>]
    </div>
    <div class="clear-left"></div>
{{ endif }}
    <div class="footer">
{{ if $render->get('link') }}
        <p class="text-center">
            <a href="http://validator.w3.org/check?uri=referer" rel="external"><img src="http://www.w3.org/Icons/valid-html401" style="border: 0;" alt="Valid HTML 4.01 Strict" height="31" width="88"></a>&nbsp;&nbsp;
            <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="external"><img style="border: 0; width: 88px; height: 31px;" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!"></a>
        </p>
{{ endif }}
        <div>
            {nel_stext('S_FOOT')}
        </div>
{{ if $render->get('output_timer') }}
        This page was created in {$render->get_timer(4)} seconds.<br>
{{ endif }}
    </div>
</body>
</html>