{{ if $rendervar['main_page'] }}
            <table>
                <tr>
                    <td>
                        {$rendervar['prev_nav']}</td><td>{$rendervar['page_nav']}</td><td>{$rendervar['next_nav']}
                    </td>
                </tr>
            </table>
{{ endif }}
{{ if $rendervar['del'] }}
            <div class="clear"></div>
        </div>
        <table class="footer-form">
            <tr>
                <td>
    {{ if $rendervar['logged_in'] }}
                    <input type="checkbox" name="banpost" id="bpost"><label for="bpost">Ban posts</label><br>
                    <input type="hidden" name="adminmode" value="modmode">
    {{ endif }}
                <input type="hidden" name="mode" value="update">
    {{ if !BS1_USE_NEW_IMGDEL }}
                    <input type="checkbox" name="onlyimgdel" id="delfbox" value="on"><label for="delfbox">{stext('DELETE_FILES_ONLY')}</label><br>
    {{ endif }}
    {{ if $rendervar['logged_in'] }}
                    <input type="submit" value="{stext('FORM_SUBMIT')}">
    {{ else }}
                    <label for="delpass">{stext('LABEL_PASS')}</label><input type="password" name="sekrit" id="delpass" size="12" maxlength="16" value="">
                    <input type="submit" value="{stext('FORM_SUBMIT')}">
    {{ endif }}
                </td>
            </tr>
        </table>
    {{ if $rendervar['response'] }}
        </div>
    {{ endif }}
    </form>
{{ endif }}
{{ if $rendervar['styles_link'] }}
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
{{ if $rendervar['link'] }}
        <p class="text-center">
            <a href="http://validator.w3.org/check?uri=referer" rel="external"><img src="http://www.w3.org/Icons/valid-html401" style="border: 0;" alt="Valid HTML 4.01 Strict" height="31" width="88"></a>&nbsp;&nbsp;
            <a href="http://jigsaw.w3.org/css-validator/check/referer" rel="external"><img style="border: 0; width: 88px; height: 31px;" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!"></a>
        </p>
{{ endif }}
        <div>
            {stext('S_FOOT')}
        </div>
        This page was created in {$total_html} seconds.<br>
    </div>
</body>
</html>