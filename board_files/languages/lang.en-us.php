<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Language file for American English (en-us)
//


// This array is for text that is in singular form or that has no singular/plural form
$lang_singular = array(

'LANG_CODE' => 'en-us', // Language code
'LANG_NAME' => 'United States English', // Full language name
'LANG_FORM' => 'singular', 

'LINK_HOME' => 'Home', // Home page link
'LINK_MANAGE' => 'Manage', // Management page link
'LINK_ABOUT' => 'About', // About page link
'LINK_RETURN' => 'Return', // Return to main page link
'LINK_REPLY' => 'Reply', // Reply to a post
'LINK_EXPAND' => 'Expand thread', // Expand thread


'FORM_SUBMIT' => 'Submit', // Submit button
'FORM_DELETE' => 'Delete', // Delete button
'FORM_RESET' => 'Clear form', // Clear form button
'FORM_UPDATE' => 'Update', // Update button
'FORM_EXPAND' => 'Expand', // Expand button
'FORM_DELETE_POSTS' => 'Delete selected content', // Delete content
'FORM_BAN_POSTS' => 'Ban selected posts', // Ban content
'FORM_UPDATE_PAGES' => 'Update all pages', // Update pages
'FORM_UPDATE_CACHE' => 'Regenerate all caches', // Regenerate caches
'FORM_ADD_STAFF' => 'Add staff', // Add staff
'FORM_EDIT_STAFF' => 'Edit staff', // Edit staff
'FORM_UPDATE_STAFF' => 'Update staff settings', // Update staff settings
'FORM_DELETE_STAFF' => 'Delete staff', // Delete staff
'FORM_RETURN_THREAD' => 'Return to thread list', // Return to thread list
'FORM_MOD_BAN' => 'Modify ban', // Modify ban
'FORM_REMOVE_BAN' => 'Remove ban', // Remove ban
'FORM_ADD_BAN' => 'Add new ban', // Add ban
'FORM_LABEL_NAME' => 'Name', // Name field
'FORM_LABEL_EMAIL' => 'E-mail', // E-mail field
'FORM_LABEL_SUBJECT' => 'Subject', // Subject field
'FORM_LABEL_COMMENT' => 'Comment', // Comment field
'FORM_LABEL_FILE' => 'File', // File upload
'FORM_LABEL_PASS' => 'Password', // Password for deleting post
'FORM_LABEL_SOURCE' => 'Source', // Source field
'FORM_LABEL_LICENSE' => 'License', // License field


'TEXT_REPLYMODE' => 'Posting mode: Reply', // Reply mode
'TEXT_THREADMODE' => 'Posting mode: New thread', // New thread mode
'TEXT_PASS_WAT' => '(Password used for file deletion)', // Explain wtf the password is for
'TEXT_SPAMBOT_TRAP' => 'Never put anything in this field!', // Warning about the hidden spambot trap fields
'TEXT_SPAMBOT_FIELD1' => 'thename1', // First anti-spambot hidden field
'TEXT_SPAMBOT_FIELD2' => 'theurl1', // Second anti-spambot hidden field
'TEXT_OMITTED_POSTS' => ' posts omitted. Click Reply or Expand Post to view.', // Prints text to be shown when replies are hidden


'THREAD_ADMINPOST' => '## Admin ##', // Admin capcode
'THREAD_MODPOST' => '## Moderator ##', // Mod capcode
'THREAD_JANPOST' => '## Janitor ##', // Admin capcode
'THREAD_NONAME' => 'Anonymous', // When there is no name
'THREAD_NOTEXT' => '', // When there is no comment
'THREAD_FILE' => 'File: ', // Text before the filename
'THREAD_STICKY' => 'Sticky', // Sticky
'THREAD_STICKY_ICON' => 'sticky.png', // Sticky icon
'THREAD_EXPAND' => 'Expand Thread', // Expand thread
'THREAD_MOAR_DATA' => 'Show more file info', // Moar info
'THREAD_LESS_DATA' => 'Show less file info', // Less info


'ERROR_HEADER' => 'Oh god how did this get here.', // Header for error page
'ERROR_0' => 'I just don\'t know what went wrong!', // Error 0: No fucking clue
'ERROR_1' => 'Flood detected, slow the fuck down.', // Error 1: Flood detected
'ERROR_2' => 'Thread could not be found.', // Error 2: Thread not found
'ERROR_3' => 'Thread is locked.', // Error 3: Thread is locked
'ERROR_4' => 'Thread has reached maximum posts.', // Error 4: Thread is full
'ERROR_5' => 'File size is 0 or Candlejack stole your uplo', // Error 5: File does not exist/has size of 0
'ERROR_6' => 'Filetype is not allowed. File: ', // Error 6: Filetype not allowed
'ERROR_7' => 'Upload failed. Post fed to squirrels.', // Error 7: Upload failed
'ERROR_8' => 'Image or file required for posting.', // Error 8: Must upload file for post
'ERROR_9' => 'Image or file required to make new thread.', // Error 9: Must upload file for thread
'ERROR_10' => 'Post contains no content or file. Dumbass.', // Error 10: No comment or file
'ERROR_11' => 'Post is too long. Try looking up the word concise.', // Error 11: Post too long
'ERROR_12' => 'Duplicate file detected: ', // Error 12: Duplicate file
'ERROR_13' => 'Id of thread or post was non-numeric. How did you even do that?', // Error 13: Non-numeric id
'ERROR_14' => 'Thread is currently inaccessible or gone.', // Error 14: Thread inaccessible or gone
'ERROR_15' => 'That file is banned.', // Error 15: Banned file
'ERROR_16' => 'That name is banned.', // Error 16: Banned name
'ERROR_17' => 'Cancer detected in post: ', // Error 17: Banned text
'ERROR_18' => 'Incorrect file type detected (does not match extension). Possible Hax.', // Error 18: filetype/extension mismatch
'ERROR_19' => 'Spoon is too big.', // Error 19: File too big
'ERROR_20' => 'Password is wrong or you are not allowed to delete that.', // Error 20: Can't delete file/post/thread
'ERROR_100' => 'Username or password is incorrect.', // Error 100: Wrong password
'ERROR_101' => 'You are not allowed to modify or remove bans.', // Error 101: Not allowed to edit bans
'ERROR_102' => 'You are not allowed to edit board settings.', // Error 102: Not allowed to edit settings
'ERROR_103' => 'You are not allowed to edit threads', // Error 103: Not allowed to modify threads
'ERROR_104' => 'You are not allowed to ban users.', // Error 104: Not allowed to ban
'ERROR_105' => 'Session expired. Go get a new one.', // Error 105: Session expired
'ERROR_106' => 'Password for file deletion is incorrect.', // Error 106: Bad password for file deletion
'ERROR_107' => 'Not authorized. GTFO', // Error 107: Not authorized
'ERROR_108' => 'You are not allowed to delete posts.', // Error 108: Not allowed to dellete posts
'ERROR_150' => 'Staff member does not exist.', // Error 150: Invalid staff name
'ERROR_151' => 'Invalid staff type.', // Error 151: Invalid staff type
'ERROR_152' => 'No staff information available. Also could not create default authorization files.', // Error 152: No staff info
'ERROR_153' => 'Invalid option.', // Error 153: Invalid option
'ERROR_154' => 'Staff member already exists.', // Error 154: Staff member already exists


'MANAGE_MODE' => 'Management Mode', // Management
'MANAGE_OPTIONS' => 'Options', // Options
'MANAGE_SETTINGS' => 'Board Settings', // Settings
'MANAGE_BANS' => 'Bans', // Bans
'MANAGE_STAFF' => 'Staff', // Staff
'MANAGE_LOGIN' => 'Management login', // Login
'MANAGE_THREADS' => 'Threads', // Threads
'MANAGE_FILESIZE_TOTAL' => 'Space used:', // Total filesize
'MANAGE_OPT_SETTINGS' => 'Board settings', // Settings
'MANAGE_OPT_BAN' => 'Ban controls', // Bans
'MANAGE_OPT_STAFF' => 'Staff controls', // Staff
'MANAGE_OPT_THREAD' => 'Thread management', // Threads
'MANAGE_OPT_MMODE' => 'Enter Mod mode', // Threads
'MANAGE_UPDATE_WARN' => 'Forces an update of all pages. May cause heavy server load.', // May cause high load
'MANAGE_UPDATE_CACHE_WARN' => 'Regenerates all the internal caches.', // May cause high load


'MANAGE_BAN_ID' => 'Ban ID', // Ban ID
'MANAGE_BAN_BOARD' => 'Board', // Ban board
'MANAGE_BAN_TYPE' => 'Type', // Ban type
'MANAGE_BAN_HOST' => 'Host', // Ban host
'MANAGE_BAN_NAME' => 'Name', // Ban name
'MANAGE_BAN_REASON' => 'Reason', // Ban reason
'MANAGE_BAN_EXPIRE' => 'Expiration', // Ban expiration
'MANAGE_BAN_APPEAL' => 'Appeal', // Ban appeal
'MANAGE_BAN_APPEAL_RESPONSE' => 'Appeal Response', // Ban appeal response
'MANAGE_BAN_STATUS' => 'Status', // Ban status
'MANAGE_BAN_MODIFY' => 'Modify', // Modify ban
'MANAGE_BAN_REMOVE' => 'Remove', // Modify ban
'MANAGE_BANMOD_IP' => 'IP to ban:', // Ban ip
'MANAGE_BANMOD_GEN' => 'Ban generated on:', // Ban date
'MANAGE_BANMOD_EXP' => 'Ban expires on:', // Ban expiration
'MANAGE_BANMOD_LENGTH' => 'Ban length:', // Ban length
'MANAGE_BANMOD_NAME' => 'Name used:', // Name used
'MANAGE_BANMOD_RSN' => 'B& reason (optional):', // B& reason
'MANAGE_BANMOD_APPLRES' => 'Appeal response:', // Appeal response
'MANAGE_BANMOD_MRKAPPL' => 'Mark appeal as reviewed', // Mark appeal as reviews
'MANAGE_BANMOD_DAY' => 'Days:', // Mark appeal as reviews
'MANAGE_BANMOD_HOUR' => 'Hours:', // Mark appeal as reviews


'MANAGE_THREAD_EXPAND' => 'Expand', // Expand
'MANAGE_THREAD_POST_NUM' => 'Post no.', // Post #
'MANAGE_THREAD_DELETE' => 'Delete', // Delete
'MANAGE_THREAD_STICKY' => 'Sticky', // Sticky
'MANAGE_THREAD_UNSTICKY' => 'Unsticky', // Unsticky
'MANAGE_THREAD_TIME' => 'Time', // Time
'MANAGE_THREAD_SUBJECT' => 'Subject', // Subject
'MANAGE_THREAD_NAME' => 'Name', // Name
'MANAGE_THREAD_COMMENT' => 'Comment', // Comment
'MANAGE_THREAD_HOST' => 'Host', // Host
'MANAGE_THREAD_FILE' => 'Filename (Filesize [Bytes])<br>md5', // File info


'MANAGE_STAFF_UNAME' => 'Username', // Username
'MANAGE_STAFF_PASS' => 'New password (leave blank to keep current password)', // Password
'MANAGE_STAFF_CHANGE_PASS' => 'Check this to change current password to the one entered above', // Checkbox to change password
'MANAGE_STAFF_PNAME' => 'Posting name', // Posting name
'MANAGE_STAFF_PTRIP' => 'Secure tripcode for posting with capcode', // Posting tripcode
'MANAGE_STAFF_STYPE' => 'Staff type (will not modify permissions)', // Update staff type
'MANAGE_STAFF_TYPE' => 'Staff permission template (permissions can be edited later)', // Staff type
'MANAGE_STAFF_ACC_SET' => 'Access Settings panel', // Access settings
'MANAGE_STAFF_ACC_STAFF' => 'Access Staff panel', // Access staff
'MANAGE_STAFF_ACC_BAN' => 'Access Ban panel', // Access bans
'MANAGE_STAFF_ACC_THREAD' => 'Access Threads panel', // Access threads
'MANAGE_STAFF_ACC_MMODE' => 'Allow to enter Mod Mode', // Allow Mod Mode
'MANAGE_STAFF_PERMBAN' => 'Permission to ban', // Permission to ban
'MANAGE_STAFF_PERMDEL' => 'Permission to delete posts', // Permission to delete
'MANAGE_STAFF_PERMPOST' => 'Permission to post as staff', // Permission to post as staff
'MANAGE_STAFF_PERMANON' => 'Force to post as anonymous staff', // Force anonymous staff
'MANAGE_STAFF_PERMSTICK' => 'Permission to sticky/unsticky', // Permission to sticky/unsticky
'MANAGE_STAFF_PERMGEN' => 'Permission to update all pages', // Permission update all pages
'MANAGE_STAFF_PERMCACHE' => 'Permission to update all caches', // Permission update all caches
'MANAGE_STAFF_WARNDEL' => 'WARNING: Cannot undelete! Use with caution, etc.', // Warning
'MANAGE_STAFF_TADMIN' => 'Admin', // Admin
'MANAGE_STAFF_TMOD' => 'Moderator', // Moderator
'MANAGE_STAFF_TJAN' => 'Janitor', // Janitor
'MANAGE_STAFF_ADD' => 'Username of staff to add', // Add staff
'MANAGE_STAFF_EDIT' => 'Username of staff to edit', // Edit staff


'MANAGE_SET_BOARD_NAME' => 'Board Name', // Board name
'MANAGE_SET_SHOW_NAME' => 'Show board name at top', // Board name at top
'MANAGE_SET_FAVICON' => 'Favicon', // Favicon
'MANAGE_SET_SHOW_FAVICON' => 'Show Favicon', // Show favicon
'MANAGE_SET_LOGO' => 'Board Logo URL (image)', // Logo
'MANAGE_SET_SHOW_LOGO' => 'Show Logo', // Show logo
'MANAGE_SET_ISO_DATE' => 'Asian/ISO-8601 (yyyy mm dd)', // Asian/ISO date
'MANAGE_SET_COMMON_DATE' => 'Common (dd mm yyyy)', // Common date
'MANAGE_SET_US_DATE' => 'U.S. (mm dd yyyy)', // U.S. date
'MANAGE_SET_DATE_SEPARATOR' => 'Separator to use in dates', // Separator to use in dates
'MANAGE_SET_THREAD_DELAY' => 'Delay between new threads (seconds)', // Thread delay
'MANAGE_SET_POST_DELAY' => 'Delay between new posts (seconds)', // Post delay
'MANAGE_SET_ABBREVIATE_THREAD' => 'How many posts in a thread before abbreviating it on the main page', // Thread abbreviation
'MANAGE_SET_TRIP' => 'Allow use of tripcodes', // Allow tripcodes
'MANAGE_SET_TRIPMARK' => 'Tripkey marker', // Tripkey
'MANAGE_SET_ALLOW_MULTIFILE' => 'Allow mutiple files per post', // Can haz multiple files
'MANAGE_SET_ALLOW_OP_MULTIFILE' => 'Allow first post in thread to have multiple files', // Op can haz multiple files
'MANAGE_SET_MAXPF' => 'Maximum number of files per post', // Max files per post
'MANAGE_SET_MAXFR' => 'Number of files to display in each row of post', // Max files per row
'MANAGE_SET_IMGMAX_MW' => 'Maximum width when post has multiple files', // Thumbnail width (multifile)
'MANAGE_SET_IMGMAX_MH' => 'Maximum height when post has multiple files', // Thumbnail height (multifile)
'MANAGE_SET_MAXFS' => 'Max file size (KB)', // Max filesize
'MANAGE_SET_USE_THUMB' => 'Use thumbnails', // Use thumbnails
'MANAGE_SET_USE_MAGICK' => 'Use ImageMagick when available', // Use ImageMagick
'MANAGE_SET_USE_FILE_ICON' => 'Use filetype icon for non-images', // Use filetype icon
'MANAGE_SET_USE_PNG_THUMB' => 'Use PNG for thumbnails instead of JPEG', // Use PNG for thumbnails
'MANAGE_SET_JPG_QUALITY' => 'JPEG thumbnail quality (0 - 100)', // JPEG Quality
'MANAGE_SET_NEW_IMGDEL' => 'Use newer, individual file delete', // New delete
'MANAGE_SET_IMGMAX' => 'Maximum dimensions before an image is thumbnailed:', // Dimensions before thumbnailed
'MANAGE_SET_IMGMAX_W' => 'Maximum width', // Thumbnail width
'MANAGE_SET_IMGMAX_H' => 'Maximum height', // Thumbnail height
'MANAGE_SET_USE_SPAMBOT_TRAP' => 'Use built-in trap for spambots.', // Spambot trap
'MANAGE_SET_MAX_NLENGTH' => 'Maximum length of name (255 or less)', // Name length
'MANAGE_SET_MAX_ELENGTH' => 'Maximum length of e-mail (255 or less)', // E-mail length
'MANAGE_SET_MAX_SLENGTH' => 'Maximum length of subject (255 or less)', // Subject length
'MANAGE_SET_MAX_CLENGTH' => 'Maximum length of comment (# of characters)', // Comment length
'MANAGE_SET_MAX_CLINE' => 'Maximum number of lines in comment', // Comment lines
'MANAGE_SET_MAX_SRCLENGTH' => 'Maximum length of source field (255 or less)', // Source length
'MANAGE_SET_MAX_LLENGTH' => 'Maximum length of license field (255 or less)', // License length
'MANAGE_SET_IMGREQ_T' => 'Require image or file to start new thread', // Require file new thread
'MANAGE_SET_IMGREQ_P' => 'Always require an image or file', // Always require file
'MANAGE_SET_HANDLE_OLD' => 'How to handle old threads:', // Handling old threads
'MANAGE_SET_OLD_A' => 'Archive', // Archive
'MANAGE_SET_OLD_P' => 'Prune', // Prune
'MANAGE_SET_OLD_N' => 'Nothing', // Nothing
'MANAGE_SET_TPP' => 'Threads per page', // Threads per page
'MANAGE_SET_MAXPAGE' => 'Maximum number of pages', // Max pages
'MANAGE_SET_BUFFER' => 'Page buffer before archiving or pruning', // Buffer size
'MANAGE_SET_MAXPPT' => 'Maximum posts per thread', // Max posts per thread
'MANAGE_SET_MAXBUMP' => 'Maximum thread bumps', // Max bumps
'MANAGE_SET_FORCEANON' => 'Force Anonymous posting', // Forced anonymous
'MANAGE_SET_GFORMAT' => 'Enable/disable graphics files', // Enable graphics
'MANAGE_SET_FGSFDS' => 'Use FGSFDS field for commands (noko, sage, etc) instead of the e-mail field', // Use the FGSFDS field
'MANAGE_SET_FGSFDS_NAME' => 'Name of the FGSFDS field', // Name of the FGSFDS field
'MANAGE_SET_LANGUAGE' => 'Set language for the board', // Board language


'MANAGE_SET_ALW_GF' => 'Graphics files', // Allow graphics files
'MANAGE_SET_ALW_JPEG' => 'JPEG - .jpg, .jpe, .jpeg', // JPEG
'MANAGE_SET_ALW_GIF' => 'GIF - .gif', // GIF
'MANAGE_SET_ALW_PNG' => 'PNG - .png', // PNG
'MANAGE_SET_ALW_J2K' => 'JPEG-2000 - .jp2', // JPEG2000
'MANAGE_SET_ALW_TIFF' => 'TIFF - .tiff, .tif', // TIFF
'MANAGE_SET_ALW_BMP' => 'BMP - .bmp', // BMP
'MANAGE_SET_ALW_ICO' => 'ICO/Icon - .ico', // ICO
'MANAGE_SET_ALW_PSD' => 'PSD(Photoshop) - .psd', // PSD
'MANAGE_SET_ALW_TGA' => 'TGA - .tga', // TGA
'MANAGE_SET_ALW_PICT' => 'PICT - .pict', // PICT
'MANAGE_SET_AFORMAT' => 'Audio files', // Enable audio
'MANAGE_SET_ALW_AF' => 'Allow audio files', // Allow audio files
'MANAGE_SET_ALW_WAV' => 'WAV - .wav', // WAV
'MANAGE_SET_ALW_AIFF' => 'AIFF - .aif, .aiff', // AIFF
'MANAGE_SET_ALW_MP3' => 'MPEG Layer-3 - .mp3', // MP3
'MANAGE_SET_ALW_M4A' => 'MPEG-4 Audio - .m4a', // M4A
'MANAGE_SET_ALW_FLAC' => 'FLAC - .flac', // FLAC
'MANAGE_SET_ALW_AAC' => 'AAC - .aac', // AAC
'MANAGE_SET_ALW_OGG' => 'OGG Vorbis - .ogg', // OGG
'MANAGE_SET_ALW_AU' => 'AU - .au', // AU
'MANAGE_SET_ALW_AC3' => 'AC-3 Audio - .ac3', // AC-3
'MANAGE_SET_ALW_WMA' => 'Windows Media Audio - .wma', // WMA
'MANAGE_SET_ALW_MIDI' => 'MIDI - .mid, .midi', // MIDI
'MANAGE_SET_VFORMAT' => 'Video files', // Enable video
'MANAGE_SET_ALW_VF' => 'Allow video files', // Allow video files
'MANAGE_SET_ALW_MPEG' => 'MPEG - .mpg, .mpeg, .mpe', // MPEG
'MANAGE_SET_ALW_MOV' => 'MOV - .mov', // MOV
'MANAGE_SET_ALW_AVI' => 'AVI - .avi', // AVI
'MANAGE_SET_ALW_WMV' => 'WMV - .wmv', // WMV
'MANAGE_SET_ALW_MP4' => 'MP4 - .mp4', // MP4
'MANAGE_SET_ALW_MKV' => 'MKV - .mkv', // MKV
'MANAGE_SET_ALW_FLV' => 'FLV - .flv', // FLV
'MANAGE_SET_DFORMAT' => 'Text/document files', // Enable documents
'MANAGE_SET_ALW_DF' => 'Allow general text and document files', // Allow document/text files
'MANAGE_SET_ALW_RTF' => 'Rich Text - .rtf', // RTF
'MANAGE_SET_ALW_PDF' => 'PDF - .pdf', // PDF
'MANAGE_SET_ALW_DOC' => 'MS Word - .doc', // DOC
'MANAGE_SET_ALW_PPT' => 'Powerpoint - .ppt', // PPT
'MANAGE_SET_ALW_XLS' => 'Excel spreadsheet - .xls', // XLS
'MANAGE_SET_ALW_TXT' => 'Plain text - .txt', // TXT
'MANAGE_SET_OFORMAT' => 'Other files', // Enable multimedia
'MANAGE_SET_ALW_OF' => 'Allow other files', // Allow mother files
'MANAGE_SET_ALW_SWF' => 'Flash/Shockwave - .swf', // SWF
'MANAGE_SET_ALW_BLORB' => 'Blorb/Gblorb - .blorb, .gblorb', // Blorb
'MANAGE_SET_RFORMAT' => 'Archive files', // Enable archive
'MANAGE_SET_ALW_RF' => 'Allow archive files', // Allow archive files
'MANAGE_SET_ALW_GZIP' => 'GZip/Tar-Gzip - .gz, .gzip, .tgz', // GZip
'MANAGE_SET_ALW_BZ2' => 'BZip 2 - .bz2', // BZip 2
'MANAGE_SET_ALW_BINHEX' => 'BinHex Archive - .hqx', // BinHex
'MANAGE_SET_ALW_LZH' => 'LZH - .lzh, .lha', // LZH
'MANAGE_SET_ALW_ZIP' => 'Zip Archive - .zip', // Zip
'MANAGE_SET_ALW_TAR' => 'Tar Archive - .tar', // TAR
'MANAGE_SET_ALW_7Z' => '7Zip - .7z', // 7Zip
'MANAGE_SET_ALW_RAR' => 'WinRAR - .rar', // WinRAR
'MANAGE_SET_ALW_STUFFIT' => 'StuffIt - .sit', // StuffIt
'MANAGE_SET_ALW_ISO' => 'CD Image - .iso', // ISO/CD
'MANAGE_SET_ALW_DMG' => 'Disk Image - .dmg', // Disk Image
'MANAGE_FORM_UPDSET' => 'Update board settings', // Update settings


'FILES_GRAPHICS' => 'Supported graphic types: ', // Prints graphics filetypes supported
'FILES_AUDIO' => 'Supported audio types: ', // Prints audio filetypes supported
'FILES_VIDEO' => 'Supported video types: ', // Prints video filetypes supported
'FILES_DOCUMENT' => 'Supported document types: ', // Prints document filetypes supported
'FILES_ARCHIVE' => 'Supported archive types: ', // Prints archive filetypes supported
'FILES_OTHER' => 'Other supported file types: ', // Prints other filetypes supported
'POSTING_RULES1_1' => 'Maximum file size allowed is ', // Rules line 1 first half
'POSTING_RULES1_2' => ' KB.', // Rules line 1 second half
'POSTING_RULES2_1' => 'Images greater than ', // Rules line 2 first half
'POSTING_RULES2_2' => ' pixels will be thumbnailed.', // Rules line 2 second half


'DELETE_FILES_ONLY' => 'Delete File Only', // Prints text next to checkbox for file deletion (right)
'FAKE_STAFF_ATTEMPT' => 'I\'m a faggot', // Attempts to imitate staff in name field are filtered to this


'BAN_APPEAL_RESPONSE' => 'This is the response to your appeal: ', // Response to appealed ban
'BAN_NO_RESPONSE' => 'No response has been given.', // No response yet
'BAN_RESPONSE_PENDING' => 'You have already appealed this ban but the appeal has not been reviewed yet.', // Appeal already filed
'ABOUT_APPEALS' => 'If you wish, you may appeal this ban. Enter your reason(s) why you should get unbanned in the box below. A staff member will (probably) review it.', // Message about appealing a ban
'APPEAL_REVIEWED' => 'You appeal has been reviewed. You cannot appeal again.', // Appeal has been reviewed
'BAN_ALTERED' => 'Your appeal has been reviewed and the ban has been altered.', // Appeal accepted but ban changed, not removed


// These are holdovers from the original Nelliel that are still in use. I need to clean them up.
'S_FOOT' => '- <a href="http://www.1chan.net/futallaby/" rel="external">futallaby</a> + <a href="http://www.nelliel.com" rel="external" title="Nelliel Imageboard Software">Nelliel ' . NELLIEL_VERSION . '</a> -', // Prints footer (leave these credits)
'S_DELPICONLY' => 'Delete File Only', // Prints text next to checkbox for file deletion (right)
'S_SHORT_MENU' => 'fff', // Other boards menu


'LANG_END' => '');

// This array is for the plural form of text in the previous array
// If there is no plural form you don't need to use this
$lang_plural = array(

'LANG_CODE' => 'en-us', // Language code
'LANG_NAME' => 'United States English', // Full language name
'LANG_FORM' => 'plural',


'LANG_END' => '')

;
;
?>
