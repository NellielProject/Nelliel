<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// The content this function presents must remain intact and be accessible to users
//
function nel_about_nelliel_screen()
{
    $render = new NellielTemplates\RenderCore();
    $render->getTemplateInstance()->setTemplatePath(TEMPLATE_PATH);
    nel_render_header(array(), $render, array(), 'ABOUT');
    echo $render->outputRenderSet();

    echo '
    <div class="text-center">
        <p>
            <span style="font-weight: bold; font-size: 1.25em; color: blue;">Nelliel Imageboard</span><br>
            Version: ' . NELLIEL_VERSION . '
        </p>
        <p class="text-center">
            Copyright (c) 2010-2018, <a href="http://www.nelliel.com">Nelliel Project</a><br>
            All rights reserved.
        </p>
		<div class="nelliel-license-div">
            <p style="max-width: 40em;">
                Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
            </p>
            <p>
                1) Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
            </p>
            <p>
                2) Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation
                and/or other materials provided with the distribution.
            </p>
            <p>
                3) Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without
                specific prior written permission.
            </p>
            <div>
                <img src="' . IMAGES_DIR . '/luna_canterlot_disclaimer.png" alt="Canterlot Voice Disclaimer" width="320" height="180" style="padding-right: 10px; width: 330px; float: left;">
                <p style="overflow: hidden; min-width: 15em;">
                    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
                    THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
                    FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
                    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
                    NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
                </p>
            </div>
            <hr class="clear">
            <p>
                Default filetype icons are from the Soft Scraps pack made by <a href="http://deleket.deviantart.com/" title="Deleket">Deleket</a>
            </p>
            <p>
                <a href="http://pageconfig.com/post/portable-utf8" title="Portable UTF-8">Portable UTF-8</a> library created by <a href="http://pageconfig.com">Hamid Sarfraz</a>.
            </p>
            <p>
                <a href="https://github.com/ircmaxell/password_compat" title="password_compat">password_compat</a> library created by <a href="https://blog.ircmaxell.com/">Anthony Ferrara</a>.
            </p>
            <p class="text-center">
                <a href="' . INPUT_BOARD_ID . '/' . PHP_SELF2 . PHP_EXT . '">' . nel_stext('LINK_RETURN') . '</a>
            </p>
        </div>
	</div>
    <hr>
</body>
</html>';
    die();
}
