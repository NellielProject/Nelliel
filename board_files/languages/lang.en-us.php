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

    'LANG_CODE' => 'en-us',  // Language code
    'LANG_NAME' => 'United States English',  // Full language name
    'LANG_FORM' => 'singular',


    'LINK_HOME' => 'Home',  // Home page link
    'LINK_MANAGE' => 'Manage',  // Management page link
    'LINK_ABOUT_NELLIEL' => 'About Nelliel',  // About page link
    'LINK_RETURN' => 'Return',  // Return to main page link
    'LINK_REPLY' => 'Reply',  // Reply to a post
    'LINK_EXPAND' => 'Expand thread',  // Expand thread
    'LINK_LOGOUT' => 'Logout',  // Logout
    'LINK_POST_NUM_REF' => 'Link', // Reference post number


    'FORM_SUBMIT' => 'Submit',  // Submit button
    'FORM_DELETE' => 'Delete',  // Delete button
    'FORM_RESET' => 'Clear form',  // Clear form button
    'FORM_UPDATE' => 'Update',  // Update button
    'FORM_EXPAND' => 'Expand',  // Expand button
    'FORM_DELETE_POSTS' => 'Delete selected content',  // Delete content
    'FORM_BAN_POSTS' => 'Ban selected posts',  // Ban content
    'FORM_UPDATE_PAGES' => 'Update all pages',  // Update pages
    'FORM_UPDATE_CACHE' => 'Regenerate all caches',  // Regenerate caches
    'FORM_STAFF_USER_EDIT' => 'Edit User', // Edit user button
    'FORM_STAFF_USER_UPDATE' => 'Update User', // Edit user button
    'FORM_STAFF_ROLE_EDIT' => 'Edit Role', // Edit role button
    'FORM_STAFF_ROLE_UPDATE' => 'Update Role', // Edit role button
    'FORM_UPDATE_STAFF' => 'Update staff settings',  // Update staff settings
    'FORM_DELETE_STAFF' => 'Delete staff',  // Delete staff
    'FORM_RETURN_THREAD' => 'Return to thread list',  // Return to thread list
    'FORM_MOD_BAN' => 'Modify ban',  // Modify ban
    'FORM_REMOVE_BAN' => 'Remove ban',  // Remove ban
    'FORM_ADD_BAN' => 'Add new ban',  // Add ban
    'FORM_LABEL_NAME' => 'Name',  // Name field
    'FORM_LABEL_EMAIL' => 'E-mail',  // E-mail field
    'FORM_LABEL_SUBJECT' => 'Subject',  // Subject field
    'FORM_LABEL_COMMENT' => 'Comment',  // Comment field
    'FORM_LABEL_FILE #' => 'File',  // File upload
    'FORM_LABEL_PASS' => 'Password',  // Password for deleting post
    'FORM_LABEL_SOURCE' => 'Source',  // Source field
    'FORM_LABEL_LICENSE' => 'License',  // License field
    'FORM_LABEL_ALT_TEXT' => 'Alt Text', // Alt text


    'TEXT_REPLYMODE' => 'Posting mode: Reply',  // Reply mode
    'TEXT_THREADMODE' => 'Posting mode: New thread',  // New thread mode
    'TEXT_PASS_WAT' => '(Password used for file deletion)',  // Explain wtf the password is for
    'TEXT_SPAMBOT_TRAP' => 'Never put anything in this field!',  // Warning about the hidden spambot trap fields
    'TEXT_OMITTED_POSTS' => ' posts omitted. Click Reply or Expand Post to view.',  // Prints text to be shown when replies are hidden


    'THREAD_ADMINPOST' => '## Admin ##',  // Admin capcode
    'THREAD_MODPOST' => '## Moderator ##',  // Mod capcode
    'THREAD_JANPOST' => '## Janitor ##',  // Admin capcode
    'THREAD_NONAME' => 'Anonymous',  // When there is no name
    'THREAD_NOTEXT' => '',  // When there is no comment
    'THREAD_FILE' => 'File: ',  // Text before the filename
    'THREAD_STICKY' => 'Sticky',  // Sticky
    'THREAD_STICKY_ICON' => 'sticky.png',  // Sticky icon
    'THREAD_EXPAND' => 'Expand Thread',  // Expand thread
    'THREAD_COLLAPSE' => 'Collapse Thread',  // Collapse thread
    'THREAD_MOAR_DATA' => 'Show more file info',  // Moar info
    'THREAD_LESS_DATA' => 'Show less file info',  // Less info


    'ERROR_HEADER' => 'oh god how did this get in here',  // Header for error page
    'ERROR_0' => 'I just don\'t know what went wrong!',  // Error 0: No fucking clue
    'ERROR_1' => 'Flood detected! You\'re posting too fast, slow the fuck down.',  // Error 1: Flood detected
    'ERROR_2' => 'This thread is locked.',  // Error 2: Thread is locked
    'ERROR_3' => 'The thread you have tried posting in is currently inaccessible or archived.',  // Error 3: Thread inaccessible or gone
    'ERROR_4' => 'The thread you have tried posting in could not be found.',  // Error 4: Thread not found
    'ERROR_5' => 'The thread has reached maximum posts.',  // Error 5: Thread is full
    'ERROR_6' => 'The thread is archived or buffered and cannot be posted to.',  // Error 6: Archived thread
    'ERROR_10' => 'Post contains no content or file. Dumbass.',  // Error 10: No comment or file
    'ERROR_11' => 'Image or file required when making a new post.',  // Error 11: Must upload file for post
    'ERROR_12' => 'Image or file required to make new thread.',  // Error 12: Must upload file for thread
    'ERROR_13' => 'Post is too long. Try looking up the word concise.',  // Error 13: Post too long
    'ERROR_30' => 'Id of thread or post was non-numeric. How did you even do that?',  // Error 30: Non-numeric id
    'ERROR_31' => 'Password is wrong or you are not allowed to delete that.',  // Error 31: Can't delete file/post/thread
    'ERROR_100' => 'Spoon is too big.',  // Error 100: File too big
    'ERROR_101' => 'File is bigger than the server allows.',  // Error 101: File too big (.ini limit)
    'ERROR_102' => 'File is bigger than submission form allows.',  // Error 102: File too big (form limit)
    'ERROR_103' => 'Only part of the file was uploaded.',  // Error 103: Partial upload
    'ERROR_104' => 'File size is 0 or Candlejack stole your uplo',  // Error 104: File was not uploaded/has size of 0
    'ERROR_105' => 'Cannot save uploaded files to server for some reason.',  // Error 105: Can't save files to server
    'ERROR_106' => 'The uploaded file just ain\'t right. That\'s all I know.',  // Error 106: Unknown upload problem
    'ERROR_107' => 'Unrecognized file type.',  // Error 107: Unrecognized file type
    'ERROR_108' => 'Filetype is not allowed.',  // Error 108: Filetype not allowed
    'ERROR_109' => 'Incorrect file type detected (does not match extension). Possible Hax.',  // Error 109: filetype/extension mismatch
    'ERROR_110' => 'Duplicate file detected.',  // Error 110: Duplicate file
    'ERROR_150' => 'That file is banned.',  // Error 150: Banned file
    'ERROR_151' => 'That name is banned.',  // Error 151: Banned name
    'ERROR_152' => 'Cancer detected in post: ',  // Error 152: Banned text
    'ERROR_160' => 'Your ip address does not match the one listed in the ban.',  // Error 160: IP mismatch
    'ERROR_200' => 'No valid action specified.',  // Error 200: Wut u try do?
    'ERROR_201' => 'No acceptable hashing algorithm has been found. We can\'t function like this.',  // Error 201: No decent hash algorithm to work with
    'ERROR_300' => 'You have failed to login. Please wait a few seconds before trying again.',  // Error 300: Not authorized
    'ERROR_301' => 'JFC! Slow down on the failure!',  // Error 301: Too many login attempts
    'ERROR_302' => 'This account has had too many failed login attempts and has been temporarily locked for 10 minutes.',  // Error 302: Lockout 5 min
    'ERROR_303' => 'This account has had too many failed login attempts and has been temporarily locked for 30 minutes.',  // Error 303: Lockout 30 min
    'ERROR_310' => 'The session id provided is invalid.',  // Error 310: Session bad match
    'ERROR_311' => 'Login has not been validated or was not correctly flagged. Cannot start session.',  // Error 311: Session can't start
    'ERROR_312' => 'This session has expired. Please login again.',  // Error 312: Session expired
    'ERROR_320' => 'You are not allowed to access the bans panel.',  // Error 320: No access to ban panel
    'ERROR_321' => 'You are not allowed to add new bans.',  // Error 321: Not allowed to ban
    'ERROR_322' => 'You are not allowed to modify bans.',  // Error 322: Not allowed to modify ban
    'ERROR_323' => 'You are not allowed to delete bans.',  // Error 323: Not allowed to delete bans
    'ERROR_330' => 'You are not allowed to access the settings panel.',  // Error 330: No access to settings panel
    'ERROR_331' => 'You are not allowed to modify the board settings.',  // Error 331: No settings modify
    'ERROR_340' => 'You are not allowed to access the staff panel.',  // Error 340: No access to staff panel
    'ERROR_350' => 'You are not allowed to access the threads panel.',  // Error 350: No access to threads panel
    'ERROR_351' => 'You are not allowed to modify threads or posts.',  // Error 351: No modify thread or post
    'ERROR_352' => 'You are not allowed to delete threads or posts.',  // Error 352: No delete thread or post
    'ERROR_400' => 'No valid management action specified.',  // Error 400: Wut u try do?
    'ERROR_440' => 'The specified user does not exist.',  // Error 440: User does not exist
    'ERROR_441' => 'The specified role does not exist.',  // Error 441: Role does not exist
    'ERROR_442' => 'No valid action given for user or role panels.',  // Error 442: No valid user/role action

    'MANAGE_MODE' => 'Management Mode',  // Management
    'MANAGE_OPTIONS' => 'Options',  // Options
    'MANAGE_SETTINGS' => 'Board Settings',  // Settings
    'MANAGE_BANS' => 'Bans',  // Bans
    'MANAGE_STAFF' => 'Staff',  // Staff
    'MANAGE_LOGIN' => 'Management login',  // Login
    'MANAGE_THREADS' => 'Threads',  // Threads
    'MANAGE_FILESIZE_TOTAL' => 'Space used:',  // Total filesize
    'MANAGE_OPT_SETTINGS' => 'Board settings',  // Settings
    'MANAGE_OPT_BAN' => 'Ban controls',  // Bans
    'MANAGE_OPT_STAFF' => 'Staff controls',  // Staff
    'MANAGE_OPT_THREAD' => 'Thread management',  // Threads
    'MANAGE_OPT_MMODE' => 'Enter Mod mode',  // Threads
    'MANAGE_UPDATE_WARN' => 'Forces an update of all pages. May cause heavy server load.',  // May cause high load
    'MANAGE_UPDATE_CACHE_WARN' => 'Regenerates all the internal caches.',  // May cause high load


    'MANAGE_BAN_ID' => 'Ban ID',  // Ban ID
    'MANAGE_BAN_BOARD' => 'Board',  // Ban board
    'MANAGE_BAN_TYPE' => 'Type',  // Ban type
    'MANAGE_BAN_HOST' => 'Host',  // Ban host
    'MANAGE_BAN_NAME' => 'Name',  // Ban name
    'MANAGE_BAN_REASON' => 'Reason',  // Ban reason
    'MANAGE_BAN_EXPIRE' => 'Expiration',  // Ban expiration
    'MANAGE_BAN_APPEAL' => 'Appeal',  // Ban appeal
    'MANAGE_BAN_APPEAL_RESPONSE' => 'Appeal Response',  // Ban appeal response
    'MANAGE_BAN_STATUS' => 'Status',  // Ban status
    'MANAGE_BAN_MODIFY' => 'Modify',  // Modify ban
    'MANAGE_BAN_REMOVE' => 'Remove',  // Modify ban
    'MANAGE_BANMOD_IP' => 'IP to ban:',  // Ban ip
    'MANAGE_BANMOD_GEN' => 'Ban generated on:',  // Ban date
    'MANAGE_BANMOD_EXP' => 'Ban expires on:',  // Ban expiration
    'MANAGE_BANMOD_LENGTH' => 'Ban length:',  // Ban length
    'MANAGE_BANMOD_BOARD' => 'Board:',  // Board
    'MANAGE_BANMOD_RSN' => 'B& reason (optional):',  // B& reason
    'MANAGE_BANMOD_APPLRES' => 'Appeal response:',  // Appeal response
    'MANAGE_BANMOD_MRKAPPL' => 'Mark appeal as reviewed',  // Mark appeal as reviews
    'MANAGE_BANMOD_YEAR' => 'Years:',  // Years
    'MANAGE_BANMOD_MONTH' => 'Months:',  // Months
    'MANAGE_BANMOD_DAY' => 'Days:',  // Days
    'MANAGE_BANMOD_HOUR' => 'Hours:',  // Hours
    'MANAGE_BANMOD_MINUTES' => 'Minutes:',  // Minutes
    'MANAGE_BANMOD_SECONDS' => 'Seconds:',  // Seconds


    'MANAGE_THREAD_EXPAND' => 'Expand',  // Expand
    'MANAGE_THREAD_POST_NUM' => 'Post no.',  // Post #
    'MANAGE_THREAD_DELETE' => 'Delete',  // Delete
    'MANAGE_THREAD_STICKY' => 'Sticky',  // Sticky
    'MANAGE_THREAD_UNSTICKY' => 'Unsticky',  // Unsticky
    'MANAGE_THREAD_TIME' => 'Time',  // Time
    'MANAGE_THREAD_SUBJECT' => 'Subject',  // Subject
    'MANAGE_THREAD_NAME' => 'Name',  // Name
    'MANAGE_THREAD_COMMENT' => 'Comment',  // Comment
    'MANAGE_THREAD_HOST' => 'Host',  // Host
    'MANAGE_THREAD_FILE' => 'Filename (Filesize [Bytes])<br>md5',  // File info
    'MANAGE_THREAD_LOCKED' => 'Locked',  // Host
    'MANAGE_THREAD_LAST_UPDATE' => 'Last Updated',  // Host
    'MANAGE_THREAD_HOST' => 'Host',  // Host
    'MANAGE_THREAD_POST_COUNT' => 'Total Posts',  // Host
    'MANAGE_THREAD_TOTAL_FILES' => 'Total Files',  // Host
    'MANAGE_POST_NUMBER' => 'Post Number',  // Number
    'MANAGE_POST_DELETE' => 'Delete Post',  // Delete
    'MANAGE_POST_PARENT' => 'Thread',  // Parent
    'MANAGE_POST_TIME' => 'Time',  // Time
    'MANAGE_POST_IP' => 'IP',  // IP
    'MANAGE_POST_NAME' => 'Name',  // Name
    'MANAGE_POST_EMAIL' => 'Email',  // Email
    'MANAGE_POST_SUBJECT' => 'Subject',  // Subject
    'MANAGE_POST_COMMENT' => 'Comment',  // Comment
    'MANAGE_STAFF_USER_ID' => 'User ID',  // User ID
    'MANAGE_STAFF_USER_PASS' => 'New password (leave blank to keep current password)',  // Password
    'MANAGE_STAFF_CHANGE_PASS' => 'Check this to change current password to the one entered above',  // Checkbox to change password
    'MANAGE_STAFF_USER_TITLE' => 'User title',  // User title
    'MANAGE_STAFF_ROLE_ID' => 'Role ID',  // Role ID
    'MANAGE_STAFF_ROLE_TITLE' => 'Role Title',  // Role Title
    'MANAGE_STAFF_CAPCODE_TEXT' => 'Capcode Text',  // Capcode Text
    'MANAGE_STAFF_CONFIG_ACCESS' => 'Access board config panel',  // Config access
    'MANAGE_STAFF_CONFIG_CHANGE' => 'Can change the board settings',  // Change board config
    'MANAGE_STAFF_USER_ACCESS' => 'Can access user panel',  // Access user panel
    'MANAGE_STAFF_USER_ADD' => 'Can add new users',  // Add users
    'MANAGE_STAFF_USER_MODIFY' => 'Can modify existing users',  // Modify users
    'MANAGE_STAFF_USER_DELETE' => 'Can delete users',  // Delete users
    'MANAGE_STAFF_USER_CHANGE_PASS' => 'User can change their password',  // User change password
    'MANAGE_STAFF_ROLE_ACCESS' => 'Access roles panel',  // Access roles
    'MANAGE_STAFF_ROLE_ADD' => 'Can add new roles',  // Add roles
    'MANAGE_STAFF_ROLE_MODIFY' => 'Can modify existing roles',  // Modify roles
    'MANAGE_STAFF_ROLE_DELETE' => 'Can delete roles',  // Delete roles
    'MANAGE_STAFF_BAN_ACCESS' => 'Access bans panel',  // Access bans
    'MANAGE_STAFF_BAN_ADD' => 'Can add new bans',
    'MANAGE_STAFF_BAN_MODIFY' => 'Can modify existing bans',
    'MANAGE_STAFF_BAN_DELETE' => 'Can delete bans',
    'MANAGE_STAFF_MODMODE' => 'Use modmode',
    'MANAGE_STAFF_POST_ACCESS' => 'Access posts panel',
    'MANAGE_STAFF_POST_MODIFY' => 'Can modify existing posts',
    'MANAGE_STAFF_POST_DELETE' => 'Can delete posts',
    'MANAGE_STAFF_POST_FILE_DELETE' => 'Can delete files from a post',
    'MANAGE_STAFF_POST_DEFAULT_NAME' => 'Can post as staff with their assigned name',
    'MANAGE_STAFF_POST_CUSTOM_NAME' => 'Can post as staff with any name',
    'MANAGE_STAFF_POST_OVERRIDE_ANON' => 'Can override forced anonymous when posting as staff',
    'MANAGE_STAFF_POST_STICKY' => 'Can create sticky',
    'MANAGE_STAFF_POST_UNSTICKY' => 'Can unsticky a post',
    'MANAGE_STAFF_POST_LOCK' => 'Can lock a thread',
    'MANAGE_STAFF_POST_UNLOCK' => 'Can unlock a thread',
    'MANAGE_STAFF_POST_IN_LOCKED' => 'Can post in a locked thread',
    'MANAGE_STAFF_POST_COMMENT' => 'Can add commentary to an existing post',
    'MANAGE_STAFF_POST_PERMSAGE' => 'Can permanently sage or unsage a thread',
    'MANAGE_STAFF_REGEN_CACHES' => 'Can rebuild caches',
    'MANAGE_STAFF_REGEN_INDEX' => 'Can rebuild the index',
    'MANAGE_STAFF_REGEN_THREADS' => 'Can rebuild threads',
    'MANAGE_STAFF_MODMODE_ACCESS' => 'Can access Mod Mode',
    'MANAGE_STAFF_MODMODE_VIEW_IPS' => 'Can view IP addresses of posts',
    'MANAGE_STAFF_WARNDEL' => 'WARNING: Cannot undelete! Use with caution, etc.',  // Warning
    'MANAGE_STAFF_TADMIN' => 'Admin',  // Admin
    'MANAGE_STAFF_TMOD' => 'Moderator',  // Moderator
    'MANAGE_STAFF_TJAN' => 'Janitor',  // Janitor
    'MANAGE_STAFF_ADD' => 'Username of staff to add',  // Add staff
    'MANAGE_STAFF_EDIT' => 'Username of staff to edit',  // Edit staff


    'MANAGE_SET_BOARD_NAME' => 'Board Name',  // Board name
    'MANAGE_SET_SHOW_NAME' => 'Show board name at top',  // Board name at top
    'MANAGE_SET_FAVICON' => 'Favicon',  // Favicon
    'MANAGE_SET_SHOW_FAVICON' => 'Show Favicon',  // Show favicon
    'MANAGE_SET_LOGO' => 'Board Logo URL (image)',  // Logo
    'MANAGE_SET_SHOW_LOGO' => 'Show Logo',  // Show logo
    'MANAGE_SET_ISO_DATE' => 'Asian/ISO-8601 (yyyy mm dd)',  // Asian/ISO date
    'MANAGE_SET_COMMON_DATE' => 'Common (dd mm yyyy)',  // Common date
    'MANAGE_SET_US_DATE' => 'U.S. (mm dd yyyy)',  // U.S. date
    'MANAGE_SET_DATE_SEPARATOR' => 'Separator to use in dates',  // Separator to use in dates
    'MANAGE_SET_THREAD_DELAY' => 'Delay between new threads (seconds)',  // Thread delay
    'MANAGE_SET_POST_DELAY' => 'Delay between new posts (seconds)',  // Post delay
    'MANAGE_SET_ABBREVIATE_THREAD' => 'How many posts in a thread before abbreviating it on the main page',  // Thread abbreviation
    'MANAGE_SET_TRIP' => 'Allow use of tripcodes',  // Allow tripcodes
    'MANAGE_SET_TRIPMARK' => 'Tripkey marker',  // Tripkey
    'MANAGE_SET_ALLOW_MULTIFILE' => 'Allow mutiple files per post',  // Can haz multiple files
    'MANAGE_SET_ALLOW_OP_MULTIFILE' => 'Allow first post in thread to have multiple files',  // Op can haz multiple files
    'MANAGE_SET_MAXPF' => 'Maximum number of files per post',  // Max files per post
    'MANAGE_SET_MAXFR' => 'Number of files to display in each row of post',  // Max files per row
    'MANAGE_SET_IMGMAX_MW' => 'Maximum width when post has multiple files',  // Thumbnail width (multifile)
    'MANAGE_SET_IMGMAX_MH' => 'Maximum height when post has multiple files',  // Thumbnail height (multifile)
    'MANAGE_SET_MAXFS' => 'Max file size (KB)',  // Max filesize
    'MANAGE_SET_USE_THUMB' => 'Use thumbnails',  // Use thumbnails
    'MANAGE_SET_USE_MAGICK' => 'Use ImageMagick when available',  // Use ImageMagick
    'MANAGE_SET_USE_FILE_ICON' => 'Use filetype icon for non-images',  // Use filetype icon
    'MANAGE_SET_USE_PNG_THUMB' => 'Use PNG for thumbnails instead of JPEG',  // Use PNG for thumbnails
    'MANAGE_SET_JPG_QUALITY' => 'JPEG thumbnail quality (0 - 100)',  // JPEG Quality
    'MANAGE_SET_NEW_IMGDEL' => 'Use newer, individual file delete',  // New delete
    'MANAGE_SET_IMGMAX' => 'Maximum dimensions before an image is thumbnailed:',  // Dimensions before thumbnailed
    'MANAGE_SET_IMGMAX_W' => 'Maximum width',  // Thumbnail width
    'MANAGE_SET_IMGMAX_H' => 'Maximum height',  // Thumbnail height
    'MANAGE_SET_USE_SPAMBOT_TRAP' => 'Use built-in trap for spambots.',  // Spambot trap
    'MANAGE_SET_MAX_NLENGTH' => 'Maximum length of name (255 or less)',  // Name length
    'MANAGE_SET_MAX_ELENGTH' => 'Maximum length of e-mail (255 or less)',  // E-mail length
    'MANAGE_SET_MAX_SLENGTH' => 'Maximum length of subject (255 or less)',  // Subject length
    'MANAGE_SET_MAX_CLENGTH' => 'Maximum length of comment (# of characters)',  // Comment length
    'MANAGE_SET_MAX_CLINE' => 'Maximum number of lines in comment',  // Comment lines
    'MANAGE_SET_DISPLAY_CLINE' => 'How many lines of comment to display',  // Comment lines
    'MANAGE_SET_MAX_SRCLENGTH' => 'Maximum length of source field (255 or less)',  // Source length
    'MANAGE_SET_MAX_LLENGTH' => 'Maximum length of license field (255 or less)',  // License length
    'MANAGE_SET_IMGREQ_T' => 'Require image or file to start new thread',  // Require file new thread
    'MANAGE_SET_IMGREQ_P' => 'Always require an image or file',  // Always require file
    'MANAGE_SET_HANDLE_OLD' => 'How to handle old threads:',  // Handling old threads
    'MANAGE_SET_OLD_A' => 'Archive',  // Archive
    'MANAGE_SET_OLD_P' => 'Prune',  // Prune
    'MANAGE_SET_OLD_N' => 'Nothing',  // Nothing
    'MANAGE_SET_TPP' => 'Threads per page',  // Threads per page
    'MANAGE_SET_MAXPAGE' => 'Maximum number of pages',  // Max pages
    'MANAGE_SET_BUFFER' => 'Page buffer before archiving or pruning',  // Buffer size
    'MANAGE_SET_MAXPPT' => 'Maximum posts per thread',  // Max posts per thread
    'MANAGE_SET_MAXBUMP' => 'Maximum thread bumps',  // Max bumps
    'MANAGE_SET_FORCEANON' => 'Force Anonymous posting',  // Forced anonymous
    'MANAGE_SET_GFORMAT' => 'Enable/disable graphics files',  // Enable graphics
    'MANAGE_SET_FGSFDS' => 'Use FGSFDS field for commands (noko, sage, etc) instead of the e-mail field',  // Use the FGSFDS field
    'MANAGE_SET_FGSFDS_NAME' => 'Name of the FGSFDS field',  // Name of the FGSFDS field
    'MANAGE_SET_LANGUAGE' => 'Set language for the board',  // Board language


    'MANAGE_SET_ALW_GF' => 'Graphics files',  // Allow graphics files
    'MANAGE_SET_ALW_JPEG' => 'JPEG - .jpg, .jpe, .jpeg',  // JPEG
    'MANAGE_SET_ALW_GIF' => 'GIF - .gif',  // GIF
    'MANAGE_SET_ALW_PNG' => 'PNG - .png',  // PNG
    'MANAGE_SET_ALW_J2K' => 'JPEG-2000 - .jp2',  // JPEG2000
    'MANAGE_SET_ALW_TIFF' => 'TIFF - .tiff, .tif',  // TIFF
    'MANAGE_SET_ALW_BMP' => 'BMP - .bmp',  // BMP
    'MANAGE_SET_ALW_ICO' => 'ICO/Icon - .ico',  // ICO
    'MANAGE_SET_ALW_PSD' => 'PSD(Photoshop) - .psd',  // PSD
    'MANAGE_SET_ALW_TGA' => 'TGA - .tga',  // TGA
    'MANAGE_SET_ALW_PICT' => 'PICT - .pict',  // PICT
    'MANAGE_SET_AFORMAT' => 'Audio files',  // Enable audio
    'MANAGE_SET_ALW_AF' => 'Allow audio files',  // Allow audio files
    'MANAGE_SET_ALW_WAV' => 'WAV - .wav',  // WAV
    'MANAGE_SET_ALW_AIFF' => 'AIFF - .aif, .aiff',  // AIFF
    'MANAGE_SET_ALW_MP3' => 'MPEG Layer-3 - .mp3',  // MP3
    'MANAGE_SET_ALW_M4A' => 'MPEG-4 Audio - .m4a',  // M4A
    'MANAGE_SET_ALW_FLAC' => 'FLAC - .flac',  // FLAC
    'MANAGE_SET_ALW_AAC' => 'AAC - .aac',  // AAC
    'MANAGE_SET_ALW_OGG' => 'OGG Vorbis - .ogg',  // OGG
    'MANAGE_SET_ALW_AU' => 'AU - .au',  // AU
    'MANAGE_SET_ALW_AC3' => 'AC-3 Audio - .ac3',  // AC-3
    'MANAGE_SET_ALW_WMA' => 'Windows Media Audio - .wma',  // WMA
    'MANAGE_SET_ALW_MIDI' => 'MIDI - .mid, .midi',  // MIDI
    'MANAGE_SET_VFORMAT' => 'Video files',  // Enable video
    'MANAGE_SET_ALW_VF' => 'Allow video files',  // Allow video files
    'MANAGE_SET_ALW_MPEG' => 'MPEG - .mpg, .mpeg, .mpe',  // MPEG
    'MANAGE_SET_ALW_MOV' => 'MOV - .mov',  // MOV
    'MANAGE_SET_ALW_AVI' => 'AVI - .avi',  // AVI
    'MANAGE_SET_ALW_WMV' => 'WMV - .wmv',  // WMV
    'MANAGE_SET_ALW_MP4' => 'MP4 - .mp4',  // MP4
    'MANAGE_SET_ALW_MKV' => 'MKV - .mkv',  // MKV
    'MANAGE_SET_ALW_FLV' => 'FLV - .flv',  // FLV
    'MANAGE_SET_DFORMAT' => 'Text/document files',  // Enable documents
    'MANAGE_SET_ALW_DF' => 'Allow general text and document files',  // Allow document/text files
    'MANAGE_SET_ALW_RTF' => 'Rich Text - .rtf',  // RTF
    'MANAGE_SET_ALW_PDF' => 'PDF - .pdf',  // PDF
    'MANAGE_SET_ALW_DOC' => 'MS Word - .doc',  // DOC
    'MANAGE_SET_ALW_PPT' => 'Powerpoint - .ppt',  // PPT
    'MANAGE_SET_ALW_XLS' => 'Excel spreadsheet - .xls',  // XLS
    'MANAGE_SET_ALW_TXT' => 'Plain text - .txt',  // TXT
    'MANAGE_SET_OFORMAT' => 'Other files',  // Enable multimedia
    'MANAGE_SET_ALW_OF' => 'Allow other files',  // Allow mother files
    'MANAGE_SET_ALW_SWF' => 'Flash/Shockwave - .swf',  // SWF
    'MANAGE_SET_ALW_BLORB' => 'Blorb/Gblorb - .blorb, .gblorb',  // Blorb
    'MANAGE_SET_RFORMAT' => 'Archive files',  // Enable archive
    'MANAGE_SET_ALW_RF' => 'Allow archive files',  // Allow archive files
    'MANAGE_SET_ALW_GZIP' => 'GZip/Tar-Gzip - .gz, .gzip, .tgz',  // GZip
    'MANAGE_SET_ALW_BZ2' => 'BZip 2 - .bz2',  // BZip 2
    'MANAGE_SET_ALW_BINHEX' => 'BinHex Archive - .hqx',  // BinHex
    'MANAGE_SET_ALW_LZH' => 'LZH - .lzh, .lha',  // LZH
    'MANAGE_SET_ALW_ZIP' => 'Zip Archive - .zip',  // Zip
    'MANAGE_SET_ALW_TAR' => 'Tar Archive - .tar',  // TAR
    'MANAGE_SET_ALW_7Z' => '7Zip - .7z',  // 7Zip
    'MANAGE_SET_ALW_RAR' => 'WinRAR - .rar',  // WinRAR
    'MANAGE_SET_ALW_STUFFIT' => 'StuffIt - .sit',  // StuffIt
    'MANAGE_SET_ALW_ISO' => 'CD Image - .iso',  // ISO/CD
    'MANAGE_SET_ALW_DMG' => 'Disk Image - .dmg',  // Disk Image
    'MANAGE_FORM_UPDSET' => 'Update board settings',  // Update settings


    'FILES_GRAPHICS' => 'Supported graphic types: ',  // Prints graphics filetypes supported
    'FILES_AUDIO' => 'Supported audio types: ',  // Prints audio filetypes supported
    'FILES_VIDEO' => 'Supported video types: ',  // Prints video filetypes supported
    'FILES_DOCUMENT' => 'Supported document types: ',  // Prints document filetypes supported
    'FILES_ARCHIVE' => 'Supported archive types: ',  // Prints archive filetypes supported
    'FILES_OTHER' => 'Other supported file types: ',  // Prints other filetypes supported
    'POSTING_RULES1_1' => 'Maximum file size allowed is ',  // Rules line 1 first half
    'POSTING_RULES1_2' => ' KB.',  // Rules line 1 second half
    'POSTING_RULES2_1' => 'Images greater than ',  // Rules line 2 first half
    'POSTING_RULES2_2' => ' pixels will be thumbnailed.',  // Rules line 2 second half


    'DELETE_FILES_ONLY' => 'Delete File Only',  // Prints text next to checkbox for file deletion (right)


    'BAN_APPEAL_RESPONSE' => 'This is the response to your appeal: ',  // Response to appealed ban
    'BAN_NO_RESPONSE' => 'No response has been given.',  // No response yet
    'BAN_RESPONSE_PENDING' => 'You have already appealed this ban but the appeal has not been reviewed yet.',  // Appeal already filed
    'ABOUT_APPEALS' => 'If you wish, you may appeal this ban. Enter your reason(s) why you should get unbanned in the box below. A staff member will (probably) review it.',  // Message about appealing a ban
    'APPEAL_REVIEWED' => 'You appeal has been reviewed. You cannot appeal again.',  // Appeal has been reviewed
    'BAN_ALTERED' => 'Your appeal has been reviewed and the ban has been altered.',  // Appeal accepted but ban changed, not removed


    'LANG_END' => '');

// This array is for the plural form of text in the previous array
// If there is no plural form you don't need to use this
$lang_plural = array(

'LANG_CODE' => 'en-us', // Language code
'LANG_NAME' => 'United States English', // Full language name
'LANG_FORM' => 'plural',

'PLURAL_TEST %count%' => 'Plural Test - Count:%count%',  // Plural test

'LANG_END' => '');

;
?>
