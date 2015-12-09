<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Language file for English
//

// Page links
define('LANG_LINK_HOME', 'Home'); // Home page link
define('LANG_LINK_MANAGE', 'Manage'); // Management page link
define('LANG_LINK_ABOUT', 'About'); // About page link
define('LANG_LINK_RETURN', 'Return'); // Return to main page link
define('LANG_LINK_REPLY', 'Reply'); // Reply to a post
define('LANG_LINK_EXPAND', 'Expand thread'); // Expand thread
                                             
// Form buttons
define('LANG_FORM_SUBMIT', 'Submit'); // Submit button
define('LANG_FORM_RESET', 'Clear form'); // Clear form button
define('LANG_FORM_UPDATE', 'Update'); // Update button
define('LANG_FORM_EXPAND', 'Expand'); // Expand button
define('LANG_FORM_UPDATE_PAGES', 'Update all pages'); // Update pages
define('LANG_FORM_UPDATE_CACHE', 'Regenerate all caches'); // Regenerate caches
define('LANG_FORM_ADD_STAFF', 'Add staff'); // Add staff
define('LANG_FORM_EDIT_STAFF', 'Edit staff'); // Edit staff
define('LANG_FORM_UPDATE_STAFF', 'Update staff settings'); // Update staff settings
define('LANG_FORM_DELETE_STAFF', 'Delete staff'); // Delete staff
define('LANG_FORM_RETURN_THREAD', 'Return to thread list'); // Return to thread list
define('LANG_FORM_MOD_BAN', 'Modify ban'); // Modify ban
define('LANG_FORM_REMOVE_BAN', 'Remove ban'); // Remove ban
define('LANG_FORM_ADD_BAN', 'Add new ban'); // Add ban
define('LANG_DELETE_FILES_ONLY', 'Delete File Only'); // Prints text next to checkbox for file deletion (right)
                                                      
// Label text
define('LANG_LABEL_NAME', 'Name'); // Name field
define('LANG_LABEL_EMAIL', 'E-mail'); // E-mail field
define('LANG_LABEL_SUBJECT', 'Subject'); // Subject field
define('LANG_LABEL_COMMENT', 'Comment'); // Comment field
define('LANG_LABEL_FILE', 'File'); // File upload
define('LANG_LABEL_PASS', 'Password'); // Password for deleting post
define('LANG_LABEL_SOURCE', 'Source'); // Source field
define('LANG_LABEL_LICENSE', 'License'); // License field
                                         
// Other text
define('LANG_TEXT_REPLYMODE', 'Posting mode: Reply'); // Reply mode
define('LANG_TEXT_THREADMODE', 'Posting mode: New thread'); // New thread mode
define('LANG_TEXT_PASS_WAT', '(Password used for file deletion)'); // Explain wtf the password is for
define('LANG_TEXT_SPAMBOT_TRAP', 'Never put anything in this field!'); // Warning about the hidden spambot trap fields
define('LANG_TEXT_SPAMBOT_FIELD1', 'thename1'); // First anti-spambot hidden field
define('LANG_TEXT_SPAMBOT_FIELD2', 'theurl1'); // Second anti-spambot hidden field
define('LANG_TEXT_OMITTED_POSTS', ' posts omitted. Click Reply or Expand Post to view.'); // Prints text to be shown when replies are hidden
define('LANG_FAKE_STAFF_ATTEMPT', 'I\'m a faggot'); // Attempts to imitate staff in name field are filtered to this
                                                    
// Thread info
define('LANG_THREAD_ADMINPOST', '## Admin ##'); // Admin capcode
define('LANG_THREAD_MODPOST', '## Moderator ##'); // Mod capcode
define('LANG_THREAD_JANPOST', '## Janitor ##'); // Admin capcode
define('LANG_THREAD_NONAME', 'Anonymous'); // When there is no name
define('LANG_THREAD_NOTEXT', ''); // When there is no comment
define('LANG_THREAD_FILE', 'File: '); // Text before the filename
define('LANG_THREAD_STICKY', 'Sticky'); // Sticky
define('LANG_THREAD_STICKY_ICON', 'sticky.png'); // Sticky icon
define('LANG_THREAD_EXPAND', 'Expand Thread'); // Expand thread
define('LANG_THREAD_MOAR_DATA', 'Show more file info'); // Moar info
define('LANG_THREAD_LESS_DATA', 'Show less file info'); // Less info
                                                        
// Errors
define('LANG_ERROR_HEADER', 'Oh god how did this get here.'); // Header for error page
define('LANG_ERROR_0', 'I just don\'t know what went wrong!'); // Error 0: No fucking clue
define('LANG_ERROR_1', 'Flood detected, slow the fuck down.'); // Error 1: Flood detected
define('LANG_ERROR_2', 'Thread could not be found.'); // Error 2: Thread not found
define('LANG_ERROR_3', 'Thread is locked.'); // Error 3: Thread is locked
define('LANG_ERROR_4', 'Thread has reached maximum posts.'); // Error 4: Thread is full
define('LANG_ERROR_5', 'File size is 0 or Candlejack stole your uplo'); // Error 5: File does not exist/has size of 0
define('LANG_ERROR_6', 'Filetype is not allowed. File: '); // Error 6: Filetype not allowed
define('LANG_ERROR_7', 'Upload failed. Post fed to squirrels.'); // Error 7: Upload failed
define('LANG_ERROR_8', 'Image or file required for posting.'); // Error 8: Must upload file for post
define('LANG_ERROR_9', 'Image or file required to make new thread.'); // Error 9: Must upload file for thread
define('LANG_ERROR_10', 'Post contains no content or file. Dumbass.'); // Error 10: No comment or file
define('LANG_ERROR_11', 'Post is too long. Try looking up the word concise.'); // Error 11: Post too long
define('LANG_ERROR_12', 'Duplicate file detected: '); // Error 12: Duplicate file
define('LANG_ERROR_13', 'Id of thread or post was non-numeric. How did you even do that?'); // Error 13: Non-numeric id
define('LANG_ERROR_14', 'Thread is currently inaccessible or gone.'); // Error 14: Thread inaccessible or gone
define('LANG_ERROR_15', 'That file is banned.'); // Error 15: Banned file
define('LANG_ERROR_16', 'That name is banned.'); // Error 16: Banned name
define('LANG_ERROR_17', 'Cancer detected in post: '); // Error 17: Banned text
define('LANG_ERROR_18', 'Incorrect file type detected (does not match extension). Possible Hax.'); // Error 18: filetype/extension mismatch
define('LANG_ERROR_19', 'Spoon is too big.'); // Error 19: File too big
define('LANG_ERROR_20', 'Password is wrong or you are not allowed to delete that.'); // Error 20: Can't delete file/post/thread
define('LANG_ERROR_100', 'Username or password is incorrect.'); // Error 100: Wrong password
define('LANG_ERROR_101', 'You are not allowed to modify or remove bans.'); // Error 101: Not allowed to edit bans
define('LANG_ERROR_102', 'You are not allowed to edit board settings.'); // Error 102: Not allowed to edit settings
define('LANG_ERROR_103', 'You are not allowed to edit threads'); // Error 103: Not allowed to modify threads
define('LANG_ERROR_104', 'You are not allowed to ban users.'); // Error 104: Not allowed to ban
define('LANG_ERROR_105', 'Session expired. Go get a new one.'); // Error 105: Session expired
define('LANG_ERROR_106', 'Password for file deletion is incorrect.'); // Error 106: Bad password for file deletion
define('LANG_ERROR_107', 'Not authorized. GTFO'); // Error 107: Not authorized
define('LANG_ERROR_150', 'Staff member does not exist.'); // Error 150: Invalid staff name
define('LANG_ERROR_151', 'Invalid staff type.'); // Error 151: Invalid staff type
define('LANG_ERROR_152', 'No staff information available. Also could not create default authorization files.'); // Error 152: No staff info
define('LANG_ERROR_153', 'Invalid option.'); // Error 153: Invalid option
                                             
// Management
define('LANG_MANAGE_MODE', 'Management Mode'); // Management
define('LANG_MANAGE_OPTIONS', 'Options'); // Options
define('LANG_MANAGE_SETTINGS', 'Board Settings'); // Settings
define('LANG_MANAGE_BANS', 'Bans'); // Bans
define('LANG_MANAGE_STAFF', 'Staff'); // Staff
define('LANG_MANAGE_LOGIN', 'Management login'); // Login
define('LANG_MANAGE_THREADS', 'Threads'); // Threads
define('LANG_MANAGE_FILESIZE_TOTAL', 'Space used:'); // Total filesize
define('LANG_MANAGE_OPT_SETTINGS', 'Board settings'); // Settings
define('LANG_MANAGE_OPT_BAN', 'Ban controls'); // Bans
define('LANG_MANAGE_OPT_STAFF', 'Staff controls'); // Staff
define('LANG_MANAGE_OPT_THREAD', 'Thread management'); // Threads
define('LANG_MANAGE_OPT_MMODE', 'Enter Mod mode'); // Threads
define('LANG_MANAGE_UPDATE_WARN', 'Forces an update of all pages. May cause heavy server load.'); // May cause high load
define('LANG_MANAGE_UPDATE_CACHE_WARN', 'Regenerates all the internal caches.'); // May cause high load
                                                                                 
// Manage bans
define('LANG_MANAGE_BAN_ID', 'Ban ID'); // Ban ID
define('LANG_MANAGE_BAN_BOARD', 'Board'); // Ban board
define('LANG_MANAGE_BAN_TYPE', 'Type'); // Ban type
define('LANG_MANAGE_BAN_HOST', 'Host'); // Ban host
define('LANG_MANAGE_BAN_NAME', 'Name'); // Ban name
define('LANG_MANAGE_BAN_REASON', 'Reason'); // Ban reason
define('LANG_MANAGE_BAN_EXPIRE', 'Expiration'); // Ban expiration
define('LANG_MANAGE_BAN_APPEAL', 'Appeal'); // Ban appeal
define('LANG_MANAGE_BAN_APPEAL_RESPONSE', 'Appeal Response'); // Ban appeal response
define('LANG_MANAGE_BAN_STATUS', 'Status'); // Ban status
define('LANG_MANAGE_BAN_MODIFY', 'Modify'); // Modify ban
define('LANG_MANAGE_BAN_REMOVE', 'Remove'); // Modify ban
define('LANG_MANAGE_BANMOD_IP', 'IP to ban:'); // Ban ip
define('LANG_MANAGE_BANMOD_GEN', 'Ban generated on:'); // Ban date
define('LANG_MANAGE_BANMOD_EXP', 'Ban expires on:'); // Ban expiration
define('LANG_MANAGE_BANMOD_LENGTH', 'Ban length:'); // Ban length
define('LANG_MANAGE_BANMOD_NAME', 'Name used:'); // Name used
define('LANG_MANAGE_BANMOD_RSN', 'B& reason (optional):'); // B& reason
define('LANG_MANAGE_BANMOD_APPLRES', 'Appeal response:'); // Appeal response
define('LANG_MANAGE_BANMOD_MRKAPPL', 'Mark appeal as reviewed'); // Mark appeal as reviews
define('LANG_MANAGE_BANMOD_DAY', 'Days:'); // Mark appeal as reviews
define('LANG_MANAGE_BANMOD_HOUR', 'Hours:'); // Mark appeal as reviews
                                             
// Manage threads
define('LANG_MANAGE_THREAD_EXPAND', 'Expand'); // Expand
define('LANG_MANAGE_THREAD_POST_NUM', 'Post no.'); // Post #
define('LANG_MANAGE_THREAD_DELETE', 'Delete'); // Delete
define('LANG_MANAGE_THREAD_STICKY', 'Sticky'); // Sticky
define('LANG_MANAGE_THREAD_UNSTICKY', 'Unsticky'); // Unsticky
define('LANG_MANAGE_THREAD_TIME', 'Time'); // Time
define('LANG_MANAGE_THREAD_SUBJECT', 'Subject'); // Subject
define('LANG_MANAGE_THREAD_NAME', 'Name'); // Name
define('LANG_MANAGE_THREAD_COMMENT', 'Comment'); // Comment
define('LANG_MANAGE_THREAD_HOST', 'Host'); // Host
define('LANG_MANAGE_THREAD_FILE', 'Filename (Filesize [Bytes])<br>md5'); // File info
                                                                         
// Manage staff
define('LANG_MANAGE_STAFF_UNAME', 'Username'); // Username
define('LANG_MANAGE_STAFF_PASS', 'New password (leave blank to keep current password)'); // Password
define('LANG_MANAGE_STAFF_CHANGE_PASS', 'Check this to change current password to the one entered above'); // Checkbox to change password
define('LANG_MANAGE_STAFF_PNAME', 'Posting name'); // Posting name
define('LANG_MANAGE_STAFF_PTRIP', 'Secure tripcode for posting with capcode'); // Posting tripcode
define('LANG_MANAGE_STAFF_STYPE', 'Staff type (will not modify permissions)'); // Update staff type
define('LANG_MANAGE_STAFF_TYPE', 'Staff permission template (permissions can be edited later)'); // Staff type
define('LANG_MANAGE_STAFF_ACC_SET', 'Access Settings panel'); // Access settings
define('LANG_MANAGE_STAFF_ACC_STAFF', 'Access Staff panel'); // Access staff
define('LANG_MANAGE_STAFF_ACC_BAN', 'Access Ban panel'); // Access bans
define('LANG_MANAGE_STAFF_ACC_THREAD', 'Access Threads panel'); // Access threads
define('LANG_MANAGE_STAFF_ACC_MMODE', 'Allow to enter Mod Mode'); // Allow Mod Mode
define('LANG_MANAGE_STAFF_PERMBAN', 'Permission to ban'); // Permission to ban
define('LANG_MANAGE_STAFF_PERMDEL', 'Permission to delete posts'); // Permission to delete
define('LANG_MANAGE_STAFF_PERMPOST', 'Permission to post as staff'); // Permission to post as staff
define('LANG_MANAGE_STAFF_PERMANON', 'Force to post as anonymous staff'); // Force anonymous staff
define('LANG_MANAGE_STAFF_PERMSTICK', 'Permission to sticky/unsticky'); // Permission to sticky/unsticky
define('LANG_MANAGE_STAFF_PERMGEN', 'Permission to update all pages'); // Permission update all pages
define('LANG_MANAGE_STAFF_PERMCACHE', 'Permission to update all caches'); // Permission update all caches
define('LANG_MANAGE_STAFF_WARNDEL', 'WARNING: Cannot undelete! Use with caution, etc.'); // Warning
define('LANG_MANAGE_STAFF_TADMIN', 'Admin'); // Admin
define('LANG_MANAGE_STAFF_TMOD', 'Moderator'); // Moderator
define('LANG_MANAGE_STAFF_TJAN', 'Janitor'); // Janitor
define('LANG_MANAGE_STAFF_ADD', 'Username of staff to add'); // Add staff
define('LANG_MANAGE_STAFF_EDIT', 'Username of staff to edit'); // Edit staff
                                                               
// Manage board settings
define('LANG_MANAGE_SET_BOARD_NAME', 'Board Name'); // Board name
define('LANG_MANAGE_SET_SHOW_NAME', 'Show board name at top'); // Board name at top
define('LANG_MANAGE_SET_FAVICON', 'Favicon'); // Favicon
define('LANG_MANAGE_SET_SHOW_FAVICON', 'Show Favicon'); // Show favicon
define('LANG_MANAGE_SET_LOGO', 'Board Logo URL (image)'); // Logo
define('LANG_MANAGE_SET_SHOW_LOGO', 'Show Logo'); // Show logo
define('LANG_MANAGE_SET_ISO_DATE', 'Asian/ISO-8601 (yyyy mm dd)'); // Asian/ISO date
define('LANG_MANAGE_SET_COMMON_DATE', 'Common (dd mm yyyy)'); // Common date
define('LANG_MANAGE_SET_US_DATE', 'U.S. (mm dd yyyy)'); // U.S. date
define('LANG_MANAGE_SET_DATE_SEPARATOR', 'Separator to use in dates'); // Separator to use in dates
define('LANG_MANAGE_SET_THREAD_DELAY', 'Delay between new threads (seconds)'); // Thread delay
define('LANG_MANAGE_SET_POST_DELAY', 'Delay between new posts (seconds)'); // Post delay
define('LANG_MANAGE_SET_ABBREVIATE_THREAD', 'How many posts in a thread before abbreviating it on the main page'); // Thread abbreviation
define('LANG_MANAGE_SET_TRIP', 'Allow use of tripcodes'); // Allow tripcodes
define('LANG_MANAGE_SET_TRIPMARK', 'Tripkey marker'); // Tripkey
define('LANG_MANAGE_SET_ALLOW_MULTIFILE', 'Allow mutiple files per post'); // Can haz multiple files
define('LANG_MANAGE_SET_ALLOW_OP_MULTIFILE', 'Allow first post in thread to have multiple files'); // Op can haz multiple files
define('LANG_MANAGE_SET_MAXPF', 'Maximum number of files per post'); // Max files per post
define('LANG_MANAGE_SET_MAXFR', 'Number of files to display in each row of post'); // Max files per row
define('LANG_MANAGE_SET_IMGMAX_MW', 'Maximum width when multiple files'); // Thumbnail width (multifile)
define('LANG_MANAGE_SET_IMGMAX_MH', 'Maximum height when multiple files'); // Thumbnail height (multifile)
define('LANG_MANAGE_SET_MAXFS', 'Max file size (KB)'); // Max filesize
define('LANG_MANAGE_SET_USE_THUMB', 'Use thumbnails'); // Use thumbnails
define('LANG_MANAGE_SET_USE_MAGICK', 'Use ImageMagick when available'); // Use ImageMagick
define('LANG_MANAGE_SET_USE_FILE_ICON', 'Use filetype icon for non-images'); // Use filetype icon
define('LANG_MANAGE_SET_USE_PNG_THUMB', 'Use PNG for thumbnails instead of JPEG'); // Use PNG for thumbnails
define('LANG_MANAGE_SET_JPG_QUALITY', 'JPEG thumbnail quality (0 - 100)'); // JPEG Quality
define('LANG_MANAGE_SET_NEW_IMGDEL', 'Use newer, individual file delete'); // New delete
define('LANG_MANAGE_SET_IMGMAX', 'Maximum dimensions before an image is thumbnailed:'); // Dimensions before thumbnailed
define('LANG_MANAGE_SET_IMGMAX_W', 'Maximum width'); // Thumbnail width
define('LANG_MANAGE_SET_IMGMAX_H', 'Maximum height'); // Thumbnail height
define('LANG_MANAGE_SET_USE_SPAMBOT_TRAP', 'Use built-in trap for spambots.'); // Spambot trap
define('LANG_MANAGE_SET_MAX_NLENGTH', 'Maximum length of name (255 or less)'); // Name length
define('LANG_MANAGE_SET_MAX_ELENGTH', 'Maximum length of e-mail (255 or less)'); // E-mail length
define('LANG_MANAGE_SET_MAX_SLENGTH', 'Maximum length of subject (255 or less)'); // Subject length
define('LANG_MANAGE_SET_MAX_CLENGTH', 'Maximum length of comment (# of characters)'); // Comment length
define('LANG_MANAGE_SET_MAX_CLINE', 'Maximum number of lines in comment'); // Comment lines
define('LANG_MANAGE_SET_MAX_SRCLENGTH', 'Maximum length of source field (255 or less)'); // Source length
define('LANG_MANAGE_SET_MAX_LLENGTH', 'Maximum length of license field (255 or less)'); // License length
define('LANG_MANAGE_SET_IMGREQ_T', 'Require image or file to start new thread'); // Require file new thread
define('LANG_MANAGE_SET_IMGREQ_P', 'Always require an image or file'); // Always require file
define('LANG_MANAGE_SET_HANDLE_OLD', 'How to handle old threads:'); // Handling old threads
define('LANG_MANAGE_SET_OLD_A', 'Archive'); // Archive
define('LANG_MANAGE_SET_OLD_P', 'Prune'); // Prune
define('LANG_MANAGE_SET_OLD_N', 'Nothing'); // Nothing
define('LANG_MANAGE_SET_TPP', 'Threads per page'); // Threads per page
define('LANG_MANAGE_SET_MAXPAGE', 'Maximum number of pages'); // Max pages
define('LANG_MANAGE_SET_BUFFER', 'Page buffer before archiving or pruning'); // Buffer size
define('LANG_MANAGE_SET_MAXPPT', 'Maximum posts per thread'); // Max posts per thread
define('LANG_MANAGE_SET_MAXBUMP', 'Maximum thread bumps'); // Max bumps
define('LANG_MANAGE_SET_FORCEANON', 'Force Anonymous posting'); // Forced anonymous
define('LANG_MANAGE_SET_GFORMAT', 'Enable/disable graphics files'); // Enable graphics
define('LANG_MANAGE_SET_FGSFDS', 'Use FGSFDS field for commands (noko, sage, etc) instead of the e-mail field'); // Use the FGSFDS field
define('LANG_MANAGE_SET_FGSFDS_NAME', 'Name of the FGSFDS field'); // Name of the FGSFDS field
                                                                   
// Manage filetypes
define('LANG_MANAGE_SET_ALW_GF', 'Graphics files'); // Allow graphics files
define('LANG_MANAGE_SET_ALW_JPEG', 'JPEG - .jpg, .jpe, .jpeg'); // JPEG
define('LANG_MANAGE_SET_ALW_GIF', 'GIF - .gif'); // GIF
define('LANG_MANAGE_SET_ALW_PNG', 'PNG - .png'); // PNG
define('LANG_MANAGE_SET_ALW_J2K', 'JPEG-2000 - .jp2'); // JPEG2000
define('LANG_MANAGE_SET_ALW_TIFF', 'TIFF - .tiff, .tif'); // TIFF
define('LANG_MANAGE_SET_ALW_BMP', 'BMP - .bmp'); // BMP
define('LANG_MANAGE_SET_ALW_ICO', 'ICO/Icon - .ico'); // ICO
define('LANG_MANAGE_SET_ALW_PSD', 'PSD(Photoshop) - .psd'); // PSD
define('LANG_MANAGE_SET_ALW_TGA', 'TGA - .tga'); // TGA
define('LANG_MANAGE_SET_ALW_PICT', 'PICT - .pict'); // PICT
define('LANG_MANAGE_SET_AFORMAT', 'Audio files'); // Enable audio
define('LANG_MANAGE_SET_ALW_AF', 'Allow audio files'); // Allow audio files
define('LANG_MANAGE_SET_ALW_WAV', 'WAV - .wav'); // WAV
define('LANG_MANAGE_SET_ALW_AIFF', 'AIFF - .aif, .aiff'); // AIFF
define('LANG_MANAGE_SET_ALW_MP3', 'MPEG Layer-3 - .mp3'); // MP3
define('LANG_MANAGE_SET_ALW_M4A', 'MPEG-4 Audio - .m4a'); // M4A
define('LANG_MANAGE_SET_ALW_FLAC', 'FLAC - .flac'); // FLAC
define('LANG_MANAGE_SET_ALW_AAC', 'AAC - .aac'); // AAC
define('LANG_MANAGE_SET_ALW_OGG', 'OGG Vorbis - .ogg'); // OGG
define('LANG_MANAGE_SET_ALW_AU', 'AU - .au'); // AU
define('LANG_MANAGE_SET_ALW_AC3', 'AC-3 Audio - .ac3'); // AC-3
define('LANG_MANAGE_SET_ALW_WMA', 'Windows Media Audio - .wma'); // WMA
define('LANG_MANAGE_SET_ALW_MIDI', 'MIDI - .mid, .midi'); // MIDI
define('LANG_MANAGE_SET_VFORMAT', 'Video files'); // Enable video
define('LANG_MANAGE_SET_ALW_VF', 'Allow video files'); // Allow video files
define('LANG_MANAGE_SET_ALW_MPEG', 'MPEG - .mpg, .mpeg, .mpe'); // MPEG
define('LANG_MANAGE_SET_ALW_MOV', 'MOV - .mov'); // MOV
define('LANG_MANAGE_SET_ALW_AVI', 'AVI - .avi'); // AVI
define('LANG_MANAGE_SET_ALW_WMV', 'WMV - .wmv'); // WMV
define('LANG_MANAGE_SET_ALW_MP4', 'MP4 - .mp4'); // MP4
define('LANG_MANAGE_SET_ALW_MKV', 'MKV - .mkv'); // MKV
define('LANG_MANAGE_SET_ALW_FLV', 'FLV - .flv'); // FLV
define('LANG_MANAGE_SET_DFORMAT', 'Text/document files'); // Enable documents
define('LANG_MANAGE_SET_ALW_DF', 'Allow general text and document files'); // Allow document/text files
define('LANG_MANAGE_SET_ALW_RTF', 'Rich Text - .rtf'); // RTF
define('LANG_MANAGE_SET_ALW_PDF', 'PDF - .pdf'); // PDF
define('LANG_MANAGE_SET_ALW_DOC', 'MS Word - .doc'); // DOC
define('LANG_MANAGE_SET_ALW_PPT', 'Powerpoint - .ppt'); // PPT
define('LANG_MANAGE_SET_ALW_XLS', 'Excel spreadsheet - .xls'); // XLS
define('LANG_MANAGE_SET_ALW_TXT', 'Plain text - .txt'); // TXT
define('LANG_MANAGE_SET_OFORMAT', 'Other files'); // Enable multimedia
define('LANG_MANAGE_SET_ALW_OF', 'Allow other files'); // Allow mother files
define('LANG_MANAGE_SET_ALW_SWF', 'Flash/Shockwave - .swf'); // SWF
define('LANG_MANAGE_SET_ALW_BLORB', 'Blorb/Gblorb - .blorb, .gblorb'); // Blorb
define('LANG_MANAGE_SET_RFORMAT', 'Archive files'); // Enable archive
define('LANG_MANAGE_SET_ALW_RF', 'Allow archive files'); // Allow archive files
define('LANG_MANAGE_SET_ALW_GZIP', 'GZip/Tar-Gzip - .gz, .gzip, .tgz'); // GZip
define('LANG_MANAGE_SET_ALW_BZ2', 'BZip 2 - .bz2'); // BZip 2
define('LANG_MANAGE_SET_ALW_BINHEX', 'BinHex Archive - .hqx'); // BinHex
define('LANG_MANAGE_SET_ALW_LZH', 'LZH - .lzh, .lha'); // LZH
define('LANG_MANAGE_SET_ALW_ZIP', 'Zip Archive - .zip'); // Zip
define('LANG_MANAGE_SET_ALW_TAR', 'Tar Archive - .tar'); // TAR
define('LANG_MANAGE_SET_ALW_7Z', '7Zip - .7z'); // 7Zip
define('LANG_MANAGE_SET_ALW_RAR', 'WinRAR - .rar'); // WinRAR
define('LANG_MANAGE_SET_ALW_STUFFIT', 'StuffIt - .sit'); // StuffIt
define('LANG_MANAGE_SET_ALW_ISO', 'CD Image - .iso'); // ISO/CD
define('LANG_MANAGE_SET_ALW_DMG', 'Disk Image - .dmg'); // Disk Image
define('LANG_MANAGE_FORM_UPDSET', 'Update board settings'); // Update settings
                                                            
// Rules and posting limits
define('LANG_FILES_GRAPHICS', 'Supported graphics types: '); // Prints graphics filetypes supported
define('LANG_FILES_AUDIO', 'Supported audio types: '); // Prints audio filetypes supported
define('LANG_FILES_VIDEO', 'Supported video types: '); // Prints video filetypes supported
define('LANG_FILES_DOCUMENT', 'Supported document types: '); // Prints document filetypes supported
define('LANG_FILES_ARCHIVE', 'Supported archive types: '); // Prints archive filetypes supported
define('LANG_FILES_OTHER', 'Other supported file types: '); // Prints other filetypes supported
define('LANG_POSTING_RULES1', 'Maximum file size allowed is ' . BS_MAX_FILESIZE . ' KB.'); // Rules 1
define('LANG_POSTING_RULES2', 'Images greater than ' . BS_MAX_WIDTH . 'x' . BS_MAX_HEIGHT . ' pixels will be thumbnailed.'); // Rules 2
                                                                                                                             
// Ban page
define('LANG_BAN_APPEAL_RESPONSE', 'This is the response to your appeal: '); // Response to appealed ban
define('LANG_BAN_NO_RESPONSE', 'No response has been given.'); // No response yet
define('LANG_BAN_RESPONSE_PENDING', 'You have already appealed this ban but the appeal has not been reviewed yet.'); // Appeal already filed
define('LANG_ABOUT_APPEALS', 'If you wish, you may appeal this ban. Enter your reason(s) why you should get unbanned in the box below. A staff member will (probably) review it.'); // Message about appealing a ban
define('LANG_APPEAL_REVIEWED', 'You appeal has been reviewed. You cannot appeal again.'); // Appeal has been reviewed
define('LANG_BAN_ALTERED', 'Your appeal has been reviewed and the ban has been altered.'); // Appeal accepted but ban changed, not removed
                                                                                           
// These are holdovers from the original Nelliel that are still in use. I need to clean them up.
define('S_FOOT', '- <a href="http://www.1chan.net/futallaby/" rel="external">futallaby</a> + <a href="http://www.nelliel.com" rel="external" title="Nelliel Imageboard Software">Nelliel ' . NELLIEL_VERSION . '</a> -'); // Prints footer (leave these credits)
define('S_DELPICONLY', 'Delete File Only'); // Prints text next to checkbox for file deletion (right)
define('S_SHORT_MENU', 'fff'); // Other boards menu
?>
