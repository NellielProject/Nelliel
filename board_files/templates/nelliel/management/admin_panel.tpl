    <div class="pass-valid">
        {nel_stext('MANAGE_MODE')}
    </div>
    <div class="del-list">
        {nel_stext('MANAGE_SETTINGS')}
    </div>
    <form accept-charset="utf-8" action="imgboard.php" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td>
                    <input type="hidden" name="mode" value="admin->settings->update">
                </td>
            </tr>
            <tr>
                <td>
                    <br><b>Basic settings</b>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bname">{nel_stext('MANAGE_SET_BOARD_NAME')}</label>
                </td>
                <td>
                    <input type="text" name="board_name" id="bname" size="50" value="{$render->retrieve_data('board_name')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="show_title" id="stitle" value=1 {$render->retrieve_data('show_title')}><label for="stitle">{nel_stext('MANAGE_SET_SHOW_NAME')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bfavico">{nel_stext('MANAGE_SET_FAVICON')}</label>
                </td>
                <td>
                    <input type="text" name="board_favicon" id="bfavico" size="30" value="{$render->retrieve_data('board_favicon')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="show_favicon" id="sfavico" value=1 {$render->retrieve_data('show_favicon')}><label for="sfavico">{nel_stext('MANAGE_SET_SHOW_FAVICON')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="blogo">{nel_stext('MANAGE_SET_LOGO')}</label>
                </td>
                <td>
                    <input type="text" name="board_logo" id="blogo" size="30" value="{$render->retrieve_data('board_logo')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="show_logo" id="slogo" value=1 {$render->retrieve_data('show_logo')}><label for="slogo">{nel_stext('MANAGE_SET_SHOW_LOGO')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    Date Format:
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" name="date_format" id="iso" value="ISO" {$render->retrieve_data('iso')}> <label for="iso">{nel_stext('MANAGE_SET_ISO_DATE')}</label><br>
                    <input type="radio" name="date_format" id="com" value="COM" {$render->retrieve_data('com')}> <label for="com">{nel_stext('MANAGE_SET_COMMON_DATE')}</label><br>
                    <input type="radio" name="date_format" id="us" value="US" {$render->retrieve_data('us')}> <label for="us">{nel_stext('MANAGE_SET_US_DATE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="datesep">{nel_stext('MANAGE_SET_DATE_SEPARATOR')}</label>
                </td>
                <td>
                    <input type="text" name="date_separator" id="datesep" size="8" value="{$render->retrieve_data('date_separator')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="tdelay">{nel_stext('MANAGE_SET_THREAD_DELAY')}</label>
                </td>
                <td>
                    <input type="text" name="thread_delay" id="tdelay" size="8" value="{$render->retrieve_data('thread_delay')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="pdelay">{nel_stext('MANAGE_SET_POST_DELAY')}</label>
                </td>
                <td>
                    <input type="text" name="reply_delay" id="pdelay" size="8" value="{$render->retrieve_data('reply_delay')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="pabbreviate">{nel_stext('MANAGE_SET_ABBREVIATE_THREAD')}</label>
                </td>
                <td>
                    <input type="text" name="abbreviate_thread" id="pabbreviate" size="8" value="{$render->retrieve_data('abbreviate_thread')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="threadpage">{nel_stext('MANAGE_SET_TPP')}</label>
                </td>
                <td>
                    <input type="text" name="threads_per_page" id="threadpage" size="8" value="{$render->retrieve_data('threads_per_page')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="pagelimit">{nel_stext('MANAGE_SET_MAXPAGE')}</label>
                </td>
                <td>
                    <input type="text" name="page_limit" id="pagelimit" size="8" value="{$render->retrieve_data('page_limit')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="pagebuffer">{nel_stext('MANAGE_SET_BUFFER')}</label>
                </td>
                <td>
                    <input type="text" name="page_buffer" id="pagebuffer" size="8" value="{$render->retrieve_data('page_buffer')}">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_SET_HANDLE_OLD')}
                </td>
            </tr>
            <tr>
                <td>
                    <input type="radio" name="old_threads" id="tarch" value="ARCHIVE" {$render->retrieve_data('archive')}> <label for="tarch">{nel_stext('MANAGE_SET_OLD_A')}</label><br>
                    <input type="radio" name="old_threads" id="tprun" value="PRUNE" {$render->retrieve_data('prune')}> <label for="tprun">{nel_stext('MANAGE_SET_OLD_P')}</label><br>
                    <input type="radio" name="old_threads" id="tnone" value="NOTHING" {$render->retrieve_data('nothing')}> <label for="tnone">{nel_stext('MANAGE_SET_OLD_N')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxpost">{nel_stext('MANAGE_SET_MAXPPT')}</label>
                </td>
                <td>
                    <input type="text" name="max_posts" id="maxpost" size="8" value="{$render->retrieve_data('max_posts')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxbump">{nel_stext('MANAGE_SET_MAXBUMP')}</label>
                </td>
                <td>
                    <input type="text" name="max_bumps" id="maxbump" size="8" value="{$render->retrieve_data('max_bumps')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="force_anonymous" id="box_anon" value=1 {$render->retrieve_data('force_anonymous')}><label for="box_anon">{nel_stext('MANAGE_SET_FORCEANON')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="allow_tripkeys" id="box_trip" value=1 {$render->retrieve_data('allow_tripkeys')}><label for="box_trip">{nel_stext('MANAGE_SET_TRIP')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="tripkey">{nel_stext('MANAGE_SET_TRIPMARK')}</label>
                </td>
                <td>
                    <input type="text" name="tripkey_marker" id="tripkey" size="8" value="{$render->retrieve_data('tripkey_marker')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_fgsfds" id="use_fgsfds" value=1 {$render->retrieve_data('use_fgsfds')}><label for="use_fgsfds">{nel_stext('MANAGE_SET_FGSFDS')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="fgsfds_name">{nel_stext('MANAGE_SET_FGSFDS_NAME')}</label>
                </td>
                <td>
                    <input type="text" name="fgsfds_name" id="fgsfds_name" size="8" value="{$render->retrieve_data('fgsfds_name')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="require_image_start" id="imgstart" value=1 {$render->retrieve_data('require_image_start')}><label for="imgstart">{nel_stext('MANAGE_SET_IMGREQ_T')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="require_image_always" id="imgalways" value=1 {$render->retrieve_data('require_image_always')}><label for="imgalways">{nel_stext('MANAGE_SET_IMGREQ_P')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_spambot_trap" id="spamtrap" value=1 {$render->retrieve_data('use_spambot_trap')}><label for="spamtrap">{nel_stext('MANAGE_SET_USE_SPAMBOT_TRAP')}</label>
                </td>
            </tr>
            <tr>
                <td>
                <hr>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxnl">{nel_stext('MANAGE_SET_MAX_NLENGTH')}</label>
                </td>
                <td>
                    <input type="text" name="max_name_length" id="maxnl" size="8" value="{$render->retrieve_data('max_name_length')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxel">{nel_stext('MANAGE_SET_MAX_ELENGTH')}</label>
                </td>
                <td>
                    <input type="text" name="max_email_length" id="maxel" size="8" value="{$render->retrieve_data('max_email_length')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxsl">{nel_stext('MANAGE_SET_MAX_SLENGTH')}</label>
                </td>
                <td>
                    <input type="text" name="max_subject_length" id="maxsl" size="8" value="{$render->retrieve_data('max_subject_length')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxcl">{nel_stext('MANAGE_SET_MAX_CLENGTH')}</label>
                </td>
                <td>
                    <input type="text" name="max_comment_length" id="maxcl" size="8" value="{$render->retrieve_data('max_comment_length')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxcll">{nel_stext('MANAGE_SET_MAX_CLINE')}</label>
                </td>
                <td>
                    <input type="text" name="max_comment_lines" id="maxcll" size="8" value="{$render->retrieve_data('max_comment_lines')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxsrcl">{nel_stext('MANAGE_SET_MAX_SRCLENGTH')}</label>
                </td>
                <td>
                    <input type="text" name="max_source_length" id="maxsrcl" size="8" value="{$render->retrieve_data('max_source_length')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxll">{nel_stext('MANAGE_SET_MAX_LLENGTH')}</label>
                </td>
                <td>
                    <input type="text" name="max_license_length" id="maxll" size="8" value="{$render->retrieve_data('max_license_length')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="sizemax">{nel_stext('MANAGE_SET_MAXFS')}</label>
                </td>
                <td>
                    <input type="text" name="max_filesize" id="sizemax" size="8" value="{$render->retrieve_data('max_filesize')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="allow_multifile" id="amult" value=1 {$render->retrieve_data('allow_multifile')}><label for="amult">{nel_stext('MANAGE_SET_ALLOW_MULTIFILE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="allow_op_multifile" id="opmult" value=1 {$render->retrieve_data('allow_op_multifile')}><label for="opmult">{nel_stext('MANAGE_SET_ALLOW_OP_MULTIFILE')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="filemax">{nel_stext('MANAGE_SET_MAXPF')}</label>
                </td>
                <td>
                    <input type="text" name="max_post_files" id="filemax" size="8" value="{$render->retrieve_data('max_post_files')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="filermax">{nel_stext('MANAGE_SET_MAXFR')}</label>
                </td>
                <td>
                    <input type="text" name="max_files_row" id="filermax" size="8" value="{$render->retrieve_data('max_files_row')}">
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_thumb" id="uthumb" value=1 {$render->retrieve_data('use_thumb')}><label for="uthumb">{nel_stext('MANAGE_SET_USE_THUMB')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_magick" id="umag" value=1 {$render->retrieve_data('use_magick')}><label for="umag">{nel_stext('MANAGE_SET_USE_MAGICK')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_file_icon" id="uficon" value=1 {$render->retrieve_data('use_file_icon')}><label for="uficon">{nel_stext('MANAGE_SET_USE_FILE_ICON')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_new_imgdel" id="nwdel" value=1 {$render->retrieve_data('use_new_imgdel')}><label for="nwdel">{nel_stext('MANAGE_SET_NEW_IMGDEL')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="use_png_thumb" id="upng" value=1 {$render->retrieve_data('use_png_thumb')}><label for="upng">{nel_stext('MANAGE_SET_USE_PNG_THUMB')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="jq">{nel_stext('MANAGE_SET_JPG_QUALITY')}</label>
                </td>
                <td>
                    <input type="text" name="jpeg_quality" id="jq" size="8" value="{$render->retrieve_data('jpeg_quality')}">
                </td>
            </tr>
            <tr>
                <td>
                    {nel_stext('MANAGE_SET_IMGMAX')}
                </td>
                <td>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxw">{nel_stext('MANAGE_SET_IMGMAX_W')}</label>
                </td>
                <td>
                    <input type="text" name="max_width" id="maxw" size="8" value="{$render->retrieve_data('max_width')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxh">{nel_stext('MANAGE_SET_IMGMAX_H')}</label>
                </td>
                <td>
                    <input type="text" name="max_height" id="maxh" size="8" value="{$render->retrieve_data('max_height')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxmw">{nel_stext('MANAGE_SET_IMGMAX_MW')}</label>
                </td>
                <td>
                    <input type="text" name="max_multi_width" id="maxmw" size="8" value="{$render->retrieve_data('max_multi_width')}">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="maxmh">{nel_stext('MANAGE_SET_IMGMAX_MH')}</label>
                </td>
                <td>
                    <input type="text" name="max_multi_height" id="maxmh" size="8" value="{$render->retrieve_data('max_multi_height')}">
                </td>
            </tr>
            <tr>
                <td>
                    <hr>
                </td>
            </tr>
            <tr>
                <td>
                    <br><b>{nel_stext('MANAGE_SET_GFORMAT')}</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="graphics_enable" id="box_graphics" value=1 {$render->retrieve_data('graphics_enable')}> <label for="box_graphics">{nel_stext('MANAGE_SET_ALW_GF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="graphics_jpeg" id="box_jpeg" value=1 {$render->retrieve_data('graphics_jpeg')}> <label for="box_jpeg">{nel_stext('MANAGE_SET_ALW_JPEG')}</label>
                </td>
                <td>
                    <input type="checkbox" name="graphics_gif" id="box_gif" value=1 {$render->retrieve_data('graphics_gif')}> <label for="box_gif">{nel_stext('MANAGE_SET_ALW_GIF')}</label>
                </td>
                <td>
                    <input type="checkbox" name="graphics_png" id="box_png" value=1 {$render->retrieve_data('graphics_png')}> <label for="box_png">{nel_stext('MANAGE_SET_ALW_PNG')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="graphics_jpeg2000" id="box_jpeg2000" value=1 {$render->retrieve_data('graphics_jpeg2000')}> <label for="box_jpeg2000">{nel_stext('MANAGE_SET_ALW_J2K')}</label>
                </td>
                <td>
                    <input type="checkbox" name="graphics_tiff" id="box_tiff" value=1 {$render->retrieve_data('graphics_tiff')}> <label for="box_tiff">{nel_stext('MANAGE_SET_ALW_TIFF')}</label>
                </td>
                <td>
                    <input type="checkbox" name="graphics_bmp" id="box_bmp" value=1 {$render->retrieve_data('graphics_bmp')}> <label for="box_bmp">{nel_stext('MANAGE_SET_ALW_BMP')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="graphics_ico" id="box_ico" value=1 {$render->retrieve_data('graphics_ico')}> <label for="box_ico">{nel_stext('MANAGE_SET_ALW_ICO')}</label>
                </td>
                <td>
                    <input type="checkbox" name="graphics_psd" id="box_psd" value=1 {$render->retrieve_data('graphics_psd')}> <label for="box_psd">{nel_stext('MANAGE_SET_ALW_PSD')}</label>
                </td>
                <td>
                    <input type="checkbox" name="graphics_tga" id="box_tga" value=1 {$render->retrieve_data('graphics_tga')}> <label for="box_tga">{nel_stext('MANAGE_SET_ALW_TGA')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="graphics_pict" id="box_pict" value=1 {$render->retrieve_data('graphics_pict')}> <label for="box_pict">{nel_stext('MANAGE_SET_ALW_PICT')}</label>
                </td>
            </tr>
            <tr>
                <td>
                <br><b>{nel_stext('MANAGE_SET_AFORMAT')}</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="audio_enable" id="box_audio" value=1 {$render->retrieve_data('audio_enable')}> <label for="box_audio">{nel_stext('MANAGE_SET_ALW_AF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="audio_wav" id="box_wav" value=1 {$render->retrieve_data('audio_wav')}> <label for="box_wav">{nel_stext('MANAGE_SET_ALW_WAV')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_aiff" id="box_aiff" value=1 {$render->retrieve_data('audio_aiff')}> <label for="box_aiff">{nel_stext('MANAGE_SET_ALW_AIFF')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_mp3" id="box_mp3" value=1 {$render->retrieve_data('audio_mp3')}> <label for="box_mp3">{nel_stext('MANAGE_SET_ALW_MP3')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="audio_m4a" id="box_m4a" value=1 {$render->retrieve_data('audio_m4a')}> <label for="box_m4a">{nel_stext('MANAGE_SET_ALW_M4A')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_flac" id="box_flac" value=1 {$render->retrieve_data('audio_flac')}> <label for="box_flac">{nel_stext('MANAGE_SET_ALW_FLAC')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_aac" id="box_aac" value=1 {$render->retrieve_data('audio_aac')}> <label for="box_aac">{nel_stext('MANAGE_SET_ALW_AAC')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="audio_ogg" id="box_ogg" value=1 {$render->retrieve_data('audio_ogg')}> <label for="box_ogg">{nel_stext('MANAGE_SET_ALW_OGG')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_au" id="box_au" value=1 {$render->retrieve_data('audio_au')}> <label for="box_au">{nel_stext('MANAGE_SET_ALW_AU')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_ac3" id="box_ac3" value=1 {$render->retrieve_data('audio_ac3')}> <label for="box_ac3">{nel_stext('MANAGE_SET_ALW_AC3')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="audio_wma" id="box_wma" value=1 {$render->retrieve_data('audio_wma')}> <label for="box_wma">{nel_stext('MANAGE_SET_ALW_WMA')}</label>
                </td>
                <td>
                    <input type="checkbox" name="audio_midi" id="box_midi" value=1 {$render->retrieve_data('audio_midi')}>  <label for="box_midi">{nel_stext('MANAGE_SET_ALW_MIDI')}</label>
                </td>
            </tr>

            <tr>
                <td>
                    <br><b>{nel_stext('MANAGE_SET_VFORMAT')}</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="video_enable" id="box_video" value=1 {$render->retrieve_data('video_enable')}> <label for="box_video">{nel_stext('MANAGE_SET_ALW_VF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="video_mpeg" id="box_mpeg" value=1 {$render->retrieve_data('video_mpeg')}> <label for="box_mpeg">{nel_stext('MANAGE_SET_ALW_MPEG')}</label>
                </td>
                <td>
                    <input type="checkbox" name="video_mov" id="box_mov" value=1 {$render->retrieve_data('video_mov')}> <label for="box_mov">{nel_stext('MANAGE_SET_ALW_MOV')}</label>
                </td>
                <td>
                    <input type="checkbox" name="video_avi" id="box_avi" value=1 {$render->retrieve_data('video_avi')}> <label for="box_avi">{nel_stext('MANAGE_SET_ALW_AVI')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="video_wmv" id="box_wmv" value=1 {$render->retrieve_data('video_wmv')}> <label for="box_wmv">{nel_stext('MANAGE_SET_ALW_WMV')}</label>
                </td>
                <td>
                    <input type="checkbox" name="video_mp4" id="box_mp4" value=1 {$render->retrieve_data('video_mp4')}> <label for="box_mp4">{nel_stext('MANAGE_SET_ALW_MP4')}</label>
                </td>
                <td>
                    <input type="checkbox" name="video_mkv" id="box_mkv" value=1 {$render->retrieve_data('video_mkv')}> <label for="box_mkv">{nel_stext('MANAGE_SET_ALW_MKV')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="enable_flv" id="box_flv" value=1 {$render->retrieve_data('video_flv')}> <label for="box_flv">{nel_stext('MANAGE_SET_ALW_FLV')}</label>
                </td>
            </tr>

            <tr>
                <td>
                    <br><b>{nel_stext('MANAGE_SET_OFORMAT')}</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="other_enable" id="box_other" value=1 {$render->retrieve_data('other_enable')}> <label for="box_other">{nel_stext('MANAGE_SET_ALW_OF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="other_swf" id="box_swf" value=1 {$render->retrieve_data('other_swf')}> <label for="box_swf">{nel_stext('MANAGE_SET_ALW_SWF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="other_blorb" id="box_blorb" value=1 {$render->retrieve_data('other_blorb')}> <label for="box_blorb">{nel_stext('MANAGE_SET_ALW_BLORB')}</label>
                </td>
            </tr>

            <tr>
                <td>
                    <br><b>{nel_stext('MANAGE_SET_DFORMAT')}</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="document_enable" id="box_document" value=1 {$render->retrieve_data('document_enable')}> <label for="box_document">{nel_stext('MANAGE_SET_ALW_DF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="document_rtf" id="box_rtf" value=1 {$render->retrieve_data('document_rtf')}> <label for="box_rtf">{nel_stext('MANAGE_SET_ALW_RTF')}</label>
                </td>
                <td>
                    <input type="checkbox" name="document_pdf" id="box_pdf" value=1 {$render->retrieve_data('document_pdf')}> <label for="box_pdf">{nel_stext('MANAGE_SET_ALW_PDF')}</label>
                </td>
                <td>
                    <input type="checkbox" name="document_txt" id="box_txt" value=1 {$render->retrieve_data('document_txt')}> <label for="box_txt">{nel_stext('MANAGE_SET_ALW_TXT')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="document_doc" id="box_doc" value=1 {$render->retrieve_data('document_doc')}> <label for="box_doc">{nel_stext('MANAGE_SET_ALW_DOC')}</label>
                </td>
                <td>
                    <input type="checkbox" name="document_ppt" id="box_ppt" value=1 {$render->retrieve_data('document_ppt')}> <label for="box_ppt">{nel_stext('MANAGE_SET_ALW_PPT')}</label>
                </td>
                <td>
                    <input type="checkbox" name="document_xls" id="box_xls" value=1 {$render->retrieve_data('document_xls')}> <label for="box_xls">{nel_stext('MANAGE_SET_ALW_XLS')}</label>
                </td>
            <tr>
                <td>
                    <br><b>{nel_stext('MANAGE_SET_RFORMAT')}</b>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="archive_enable" id="box_archive" value=1 {$render->retrieve_data('archive_enable')}> <label for="box_archive">{nel_stext('MANAGE_SET_ALW_RF')}</label>
                </td>
            <tr>
                <td>
                    <input type="checkbox" name="archive_gzip" id="box_gzip" value=1 {$render->retrieve_data('archive_gzip')}> <label for="box_gzip">{nel_stext('MANAGE_SET_ALW_GZIP')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_bz2" id="box_bz2" value=1 {$render->retrieve_data('archive_bz2')}> <label for="box_bz2">{nel_stext('MANAGE_SET_ALW_BZ2')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_lzh" id="box_lzh" value=1 {$render->retrieve_data('archive_lzh')}> <label for="box_lzh">{nel_stext('MANAGE_SET_ALW_LZH')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="archive_zip" id="box_zip" value=1 {$render->retrieve_data('archive_zip')}> <label for="box_zip">{nel_stext('MANAGE_SET_ALW_ZIP')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_rar" id="box_rar" value=1 {$render->retrieve_data('archive_rar')}> <label for="box_rar">{nel_stext('MANAGE_SET_ALW_RAR')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_sit" id="box_sit" value=1 {$render->retrieve_data('archive_stuffit')}> <label for="box_sit">{nel_stext('MANAGE_SET_ALW_STUFFIT')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="archive_hqx" id="box_hqx" value=1 {$render->retrieve_data('archive_binhex')}> <label for="box_hqx">{nel_stext('MANAGE_SET_ALW_BINHEX')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_tar" id="box_tar" value=1 {$render->retrieve_data('archive_tar')}> <label for="box_tar">{nel_stext('MANAGE_SET_ALW_TAR')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_7z" id="box_7z" value=1 {$render->retrieve_data('archive_7z')}> <label for="box_7z">{nel_stext('MANAGE_SET_ALW_7Z')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="archive_iso" id="box_iso" value=1 {$render->retrieve_data('archive_iso')}> <label for="box_iso">{nel_stext('MANAGE_SET_ALW_ISO')}</label>
                </td>
                <td>
                    <input type="checkbox" name="archive_dmg" id="box_dmg" value=1 {$render->retrieve_data('archive_dmg')}> <label for="box_dmg">{nel_stext('MANAGE_SET_ALW_DMG')}</label>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" value="{nel_stext('MANAGE_FORM_UPDSET')}">
                </td>
            </tr>
        </table>
    </form>
