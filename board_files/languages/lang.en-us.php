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


    'Home' => 'Home',  // Home page link
    'Manage' => 'Manage',  // Management page link
    'About Nelliel' => 'About Nelliel',  // About page link
    'Return' => 'Return',  // Return to main page link
    'Reply' => 'Reply',  // Reply to a post
    'Logout' => 'Logout',  // Logout
    'Link' => 'Link', // Reference post number


    'Submit' => 'Submit',  // Submit button
    'Delete' => 'Delete',  // Delete button
    'Clear form' => 'Clear form',  // Clear form button
    'Update' => 'Update',  // Update button
    'Expand' => 'Expand',  // Expand button
    'Delete selected content' => 'Delete selected content',  // Delete content
    'Ban selected posts' => 'Ban selected posts',  // Ban content
    'Update all pages' => 'Update all pages',  // Update pages
    'Regenerate all caches' => 'Regenerate all caches',  // Regenerate caches
    'Add new user' => 'Add New User', // New user button
    'Edit user' => 'Edit User', // Edit user button
    'Update user' => 'Update User', // Edit user button
    'Add new role' => 'Add New Role', // New user button
    'Edit role' => 'Edit Role', // Edit role button
    'Update role' => 'Update Role', // Edit role button
    'Update staff settings' => 'Update staff settings',  // Update staff settings
    'Modify ban' => 'Modify ban',  // Modify ban
    'Remove ban' => 'Remove ban',  // Remove ban
    'Add new ban' => 'Add new ban',  // Add ban
    'Add filter' => 'Add filter',
    'Remove filter' => 'Remove filter',
    'Name' => 'Name',  // Name field
    'E-mail' => 'E-mail',  // E-mail field
    'Subject' => 'Subject',  // Subject field
    'Comment' => 'Comment',  // Comment field
    'File' => 'File',  // File upload
    'Password' => 'Password',  // Password for deleting post
    'Source' => 'Source',  // Source field
    'License' => 'License',  // License field
    'Alt text' => 'Alt Text', // Alt text


    'Posting mode: Reply' => 'Posting mode: Reply',  // Reply mode
    'Posting mode: New thread' => 'Posting mode: New thread',  // New thread mode
    '(Password used for file deletion)' => '(Password used for file deletion)',  // Explain wtf the password is for
    'Never put anything in this field!' => 'Never put anything in this field!',  // Warning about the hidden spambot trap fields
    ' posts omitted. Click Reply or Expand Post to view.' => ' posts omitted. Click Reply or Expand Post to view.',  // Prints text to be shown when replies are hidden


    'Anonymous' => 'Anonymous',  // When there is no name
    '(no comment)' => '(no comment)',  // When there is no comment
    'Sticky' => 'Sticky',  // Sticky
    'sticky.png' => 'sticky.png',  // Sticky icon
    'Expand thread' => 'Expand thread',  // Expand thread
    'Collapse thread' => 'Collapse Thread',  // Collapse thread
    'More file info' => 'Show more file info',  // Moar info
    'Less file info' => 'Show less file info',  // Less info


    'oh god how did this get in here' => 'oh god how did this get in here',  // Header for error page
    'I just don\'t know what went wrong!' => 'I just don\'t know what went wrong!',  // Error 0: No fucking clue
    'Flood detected! You\'re posting too fast, slow the fuck down.' => 'Flood detected! You\'re posting too fast, slow the fuck down.',  // Error 1: Flood detected
    'This thread is locked.' => 'This thread is locked.',  // Error 2: Thread is locked
    'The thread you have tried posting in is currently inaccessible or archived.' => 'The thread you have tried posting in is currently inaccessible or archived.',  // Error 3: Thread inaccessible or gone
    'The thread you have tried posting in could not be found.' => 'The thread you have tried posting in could not be found.',  // Error 4: Thread not found
    'The thread has reached maximum posts.' => 'The thread has reached maximum posts.',  // Error 5: Thread is full
    'The thread is archived or buffered and cannot be posted to.' => 'The thread is archived or buffered and cannot be posted to.',  // Error 6: Archived thread
    'Post contains no content or file. Dumbass.' => 'Post contains no content or file. Dumbass.',  // Error 10: No comment or file
    'Image or file required when making a new post.' => 'Image or file required when making a new post.',  // Error 11: Must upload file for post
    'Image or file required to make new thread.' => 'Image or file required to make new thread.',  // Error 12: Must upload file for thread
    'Post is too long. Try looking up the word concise.' => 'Post is too long. Try looking up the word concise.',  // Error 13: Post too long
    'Id of thread or post was non-numeric. How did you even do that?' => 'Id of thread or post was non-numeric. How did you even do that?',  // Error 30: Non-numeric id
    'Password is wrong or you are not allowed to delete that.' => 'Password is wrong or you are not allowed to delete that.',  // Error 31: Can't delete file/post/thread
    'Spoon is too big.' => 'Spoon is too big.',  // Error 100: File too big
    'File is bigger than the server allows.' => 'File is bigger than the server allows.',  // Error 101: File too big (.ini limit)
    'File is bigger than submission form allows.' => 'File is bigger than submission form allows.',  // Error 102: File too big (form limit)
    'Only part of the file was uploaded.' => 'Only part of the file was uploaded.',  // Error 103: Partial upload
    'File size is 0 or Candlejack stole your uplo' => 'File size is 0 or Candlejack stole your uplo',  // Error 104: File was not uploaded/has size of 0
    'Cannot save uploaded files to server for some reason.' => 'Cannot save uploaded files to server for some reason.',  // Error 105: Can't save files to server
    'The uploaded file just ain\'t right. That\'s all I know.' => 'The uploaded file just ain\'t right. That\'s all I know.',  // Error 106: Unknown upload problem
    'Unrecognized file type.' => 'Unrecognized file type.',  // Error 107: Unrecognized file type
    'Filetype is not allowed.' => 'Filetype is not allowed.',  // Error 108: Filetype not allowed
    'Incorrect file type detected (does not match extension). Possible Hax.' => 'Incorrect file type detected (does not match extension). Possible Hax.',  // Error 109: filetype/extension mismatch
    'Duplicate file detected.' => 'Duplicate file detected.',  // Error 110: Duplicate file
    'Filename was empty or was purged by filter.' => 'Filename was empty or was purged by filter.',  // Error 111: Invalid or purged filename
    'That file is banned.' => 'That file is banned.',  // Error 150: Banned file
    'That name is banned.' => 'That name is banned.',  // Error 151: Banned name
    'Cancer detected in post: ' => 'Cancer detected in post: ',  // Error 152: Banned text
    'Your ip address does not match the one listed in the ban.' => 'Your ip address does not match the one listed in the ban.',  // Error 160: IP mismatch
    'No valid action specified.' => 'No valid action specified.',  // Error 200: Wut u try do?
    'No acceptable password hashing algorithm has been found. We can\'t function like this.' => 'No acceptable password hashing algorithm has been found. We can\'t function like this.',  // Error 201: No decent hash algorithm to work with
    'InnoDB engine is required for MySQL support. However the engine has been disabled for some stupid reason.' => 'InnoDB engine is required for MySQL support. However the engine has been disabled for some stupid reason.',  // Error 202: No InnoDB available
    'You have failed to login. Please wait a few seconds before trying again.' => 'You have failed to login. Please wait a few seconds before trying again.',  // Error 300: Not authorized
    'JFC! Slow down on the failure!' => 'JFC! Slow down on the failure!',  // Error 301: Too many login attempts
    'This account has had too many failed login attempts and has been temporarily locked for 10 minutes.' => 'This account has had too many failed login attempts and has been temporarily locked for 10 minutes.',  // Error 302: Lockout 5 min
    'This account has had too many failed login attempts and has been temporarily locked for 30 minutes.' => 'This account has had too many failed login attempts and has been temporarily locked for 30 minutes.',  // Error 303: Lockout 30 min
    'The session id provided is invalid.' => 'The session id provided is invalid.',  // Error 310: Session bad match
    'Login has not been validated or was not correctly flagged. Cannot start session.' => 'Login has not been validated or was not correctly flagged. Cannot start session.',  // Error 311: Session can't start
    'This session has expired. Please login again.' => 'This session has expired. Please login again.',  // Error 312: Session expired
    'You are not allowed to access the bans panel.' => 'You are not allowed to access the bans panel.',  // Error 320: No access to ban panel
    'You are not allowed to add new bans.' => 'You are not allowed to add new bans.',  // Error 321: Not allowed to ban
    'You are not allowed to modify bans.' => 'You are not allowed to modify bans.',  // Error 322: Not allowed to modify ban
    'You are not allowed to delete bans.' => 'You are not allowed to delete bans.',  // Error 323: Not allowed to delete bans
    'You are not allowed to access the settings panel.' => 'You are not allowed to access the settings panel.',  // Error 330: No access to settings panel
    'You are not allowed to modify the board settings.' => 'You are not allowed to modify the board settings.',  // Error 331: No settings modify
    'You are not allowed to access the staff panel.' => 'You are not allowed to access the staff panel.',  // Error 340: No access to staff panel
    'You are not allowed to modify staff.' => 'You are not allowed to modify staff.',  // Error 341: No staff modify
    'You are not allowed to modify roles.' => 'You are not allowed to modify roles.',  // Error 342: No role modify
    'You are not allowed to add staff.' => 'You are not allowed to add staff.',  // Error 343: No add staff
    'You are not allowed to access the threads panel.' => 'You are not allowed to access the threads panel.',  // Error 350: No access to threads panel
    'You are not allowed to modify threads or posts.' => 'You are not allowed to modify threads or posts.',  // Error 351: No modify thread or post
    'You are not allowed to delete threads or posts.' => 'You are not allowed to delete threads or posts.',  // Error 352: No delete thread or post
    'No valid management action specified.' => 'No valid management action specified.',  // Error 400: Wut u try do?
    'The specified user does not exist.' => 'The specified user does not exist.',  // Error 440: User does not exist
    'The specified role does not exist.' => 'The specified role does not exist.',  // Error 441: Role does not exist
    'No valid action given for user or role panels.' => 'No valid action given for user or role panels.',  // Error 442: No valid user/role action

    'General Management' => 'General Management',  // General Management
    'Board Management' => 'Board Management',  // Board Management
    'Current Board:' => 'Current Board:',  // Current Board
    'Options' => 'Options',  // Options
    'Board Settings' => 'Board Settings',  // Settings
    'Site Settings' => 'Site Settings',  // Settings
    'View and change the site-wide settings.' => 'View and change the site-wide settings.',  // Settings
    'Bans' => 'Bans',  // Bans
    'Staff' => 'Staff',  // Staff
    'Management Login' => 'Management login',  // Login
    'Threads' => 'Threads',  // Threads
    'Ban Controls' => 'Ban controls',  // Bans
    'Staff Controls' => 'Staff controls',  // Staff
    'Manage users and roles.' => 'Manage users and roles',  // Staff
    'Thread Management' => 'Thread management',  // Threads
    'Forces an update of all pages. May cause heavy server load.' => 'Forces an update of all pages. May cause heavy server load.',  // May cause high load
    'Regenerates all the internal caches.' => 'Regenerates all the internal caches.',  // May cause high load
    'New Board' => 'New board',  // New board
    'Create a new board.' => 'Create a new board.',  // New board
    'Board directory' => 'Board directory',  // New board directory
    'Board ID' => 'Board ID',  // New board ID

    'Ban ID' => 'Ban ID',  // Ban ID
    'Board' => 'Board',  // Ban board
    'Type' => 'Type',  // Ban type
    'Host' => 'Host',  // Ban host
    'Reason' => 'Reason',  // Ban reason
    'Expiration' => 'Expiration',  // Ban expiration
    'Appeal' => 'Appeal',  // Ban appeal
    'Appeal Response' => 'Appeal Response',  // Ban appeal response
    'Status' => 'Status',  // Ban status
    'Modify' => 'Modify',  // Modify ban
    'Remove' => 'Remove',  // Modify ban
    'IP to ban:' => 'IP to ban:',  // Ban ip
    'Ban generated on:' => 'Ban generated on:',  // Ban date
    'Ban expires on:' => 'Ban expires on:',  // Ban expiration
    'Ban length:' => 'Ban length:',  // Ban length
    'Ban reason (optional):' => 'B& reason (optional):',  // B& reason
    'Mark appeal as reviewed.' => 'Mark appeal as reviewed',  // Mark appeal as reviews
    'Years:' => 'Years:',  // Years
    'Months:' => 'Months:',  // Months:
    'Days:' => 'Days:',  // Days
    'Hours:' => 'Hours:',  // Hours
    'Minutes:' => 'Minutes:',  // Minutes
    'Seconds:' => 'Seconds:',  // Seconds
    'Ban from all boards.' => 'Ban from all boards', // All boards ban


    'Expand' => 'Expand',  // Expand
    'Post no.' => 'Post no.',  // Post #
    'Delete' => 'Delete',  // Delete
    'Sticky' => 'Sticky',  // Sticky
    'Unsticky' => 'Unsticky',  // Unsticky
    'Time' => 'Time',  // Time
    'Subject' => 'Subject',  // Subject
    'Name' => 'Name',  // Name
    'Comment' => 'Comment',  // Comment
    'Host' => 'Host',  // Host
    'Locked' => 'Locked',  // Host
    'Last updated' => 'Last Updated',  // Host
    'Total Posts' => 'Total Posts',  // Host
    'Total Files' => 'Total Files',  // Host
    'Post Number' => 'Post Number',  // Number
    'Delete Post' => 'Delete Post',  // Delete
    'Thread' => 'Thread',  // Parent
    'Time' => 'Time',  // Time
    'IP' => 'IP',  // IP
    'Name' => 'Name',  // Name
    'Email' => 'Email',  // Email
    'Subject' => 'Subject',  // Subject
    'Comment' => 'Comment',  // Comment
    'User ID' => 'User ID',  // User ID
    'New password (leave blank to keep current password)' => 'New password (leave blank to keep current password)',  // Password
    'Check this to change current password to the one entered above' => 'Check this to change current password to the one entered above',  // Checkbox to change password
    'User Title' => 'User title',  // User title
    'Role ID' => 'Role ID',  // Role ID
    'Role Title' => 'Role Title',  // Role Title
    'Capcode Text' => 'Capcode Text',  // Capcode Text
    'Can access board config panel' => 'Access board config panel',  // Config access
    'Can change the board settings' => 'Can change the board settings',  // Change board config
    'Can access user panel' => 'Can access user panel',  // Access user panel
    'Can add new users' => 'Can add new users',  // Add users
    'Can modify existing users' => 'Can modify existing users',  // Modify users
    'Can delete users' => 'Can delete users',  // Delete users
    'User\'s secure tripcode for posting as staff' => 'User\'s secure tripcode for posting as staff',
    'User can change their password' => 'User can change their password',  // User change password
    'Access roles panel' => 'Access roles panel',  // Access roles
    'Can add new roles' => 'Can add new roles',  // Add roles
    'Can modify existing roles' => 'Can modify existing roles',  // Modify roles
    'Can delete roles' => 'Can delete roles',  // Delete roles
    'Can access bans panel.' => 'Access bans panel',  // Access bans
    'Can add new bans.' => 'Can add new bans',
    'Can modify exsiting bans.' => 'Can modify existing bans',
    'Can delete bans.' => 'Can delete bans',
    'Can access posts panel.' => 'Access posts panel',
    'Can modify existing posts.' => 'Can modify existing posts',
    'Can delete posts.' => 'Can delete posts',
    'Can delete files from a post.' => 'Can delete files from a post',
    'Can post as staff with their assigned name.' => 'Can post as staff with their assigned name',
    'Can post as staff with any name' => 'Can post as staff with any name',
    'Can override forced anonymous when posting as staff' => 'Can override forced anonymous when posting as staff',
    'Can create sticky' => 'Can create sticky',
    'Can unsticky a post' => 'Can unsticky a post',
    'Can lock a thread' => 'Can lock a thread',
    'Can unlock a thread' => 'Can unlock a thread',
    'Can post in a locked thread' => 'Can post in a locked thread',
    'Can add commentary to an existing post' => 'Can add commentary to an existing post',
    'Can permanently sage or unsage a thread' => 'Can permanently sage or unsage a thread',
    'Can rebuild caches' => 'Can rebuild caches',
    'Can rebuild the index' => 'Can rebuild the index',
    'Can rebuild threads' => 'Can rebuild threads',
    'Can access Mod Mode' => 'Can access Mod Mode',
    'Can view IP addresses of posts' => 'Can view IP addresses of posts',
    'WARNING: Cannot undelete! Use with caution, etc.' => 'WARNING: Cannot undelete! Use with caution, etc.',  // Warning
    'Role Level' => 'Role Lvel', // Role level

    'Site home page. Can be absolute or relative URL.' => 'Site home page. Can be absolute or relative URL.', // Home page

    'Board Name' => 'Board Name',  // Board name
    'SHow board name at top' => 'Show board name at top',  // Board name at top
    'Favicon' => 'Favicon',  // Favicon
    'Show Favicon' => 'Show Favicon',  // Show favicon
    'Board Logo (image)' => 'Board Logo URL (image)',  // Logo
    'Show Logo' => 'Show Logo',  // Show logo
    'ISO-8601 (yyyy mm dd)' => 'Asian/ISO-8601 (yyyy mm dd)',  // Asian/ISO date
    'Common (dd mm yyyy)' => 'Common (dd mm yyyy)',  // Common date
    'U.S. (mm dd yyyy)' => 'U.S. (mm dd yyyy)',  // U.S. date
    'Separator to use in dates.' => 'Separator to use in dates',  // Separator to use in dates
    'Delay between new threads (seconds)' => 'Delay between new threads (seconds)',  // Thread delay
    'Delay between new posts (seconds)' => 'Delay between new posts (seconds)',  // Post delay
    'How many posts in a thread before abbreviating it on the main page' => 'How many posts in a thread before abbreviating it on the main page',  // Thread abbreviation
    'Allow use of tripcodes' => 'Allow use of tripcodes',  // Allow tripcodes
    'Tripcode marker' => 'Tripkey marker',  // Tripkey
    'Allow mutiple files per post' => 'Allow mutiple files per post',  // Can haz multiple files
    'Allow first post in thread to have multiple files' => 'Allow first post in thread to have multiple files',  // Op can haz multiple files
    'Maximum number of files per post' => 'Maximum number of files per post',  // Max files per post
    'Number of files to display in each row of post' => 'Number of files to display in each row of post',  // Max files per row
    'Maximum width when post has multiple files' => 'Maximum width when post has multiple files',  // Thumbnail width (multifile)
    'Maximum height when post has multiple files' => 'Maximum height when post has multiple files',  // Thumbnail height (multifile)
    'Max file size (KB)' => 'Max file size (KB)',  // Max filesize
    'Use thumbnails' => 'Use thumbnails',  // Use thumbnails
    'Use ImageMagick when available' => 'Use ImageMagick when available',  // Use ImageMagick
    'Use filetype icon for non-images' => 'Use filetype icon for non-images',  // Use filetype icon
    'Use PNG for thumbnails instead of JPEG' => 'Use PNG for thumbnails instead of JPEG',  // Use PNG for thumbnails
    'JPEG thumbnail quality (0 - 100)' => 'JPEG thumbnail quality (0 - 100)',  // JPEG Quality
    'Use newer, individual file delete' => 'Use newer, individual file delete',  // New delete
    'Maximum dimensions before an image is thumbnailed:' => 'Maximum dimensions before an image is thumbnailed:',  // Dimensions before thumbnailed
    'Maximum width' => 'Maximum width',  // Thumbnail width
    'Maximum height' => 'Maximum height',  // Thumbnail height
    'Use built-in trap for spambots.' => 'Use built-in trap for spambots.',  // Spambot trap
    'Maximum length of name (255 or less)' => 'Maximum length of name (255 or less)',  // Name length
    'Maximum length of e-mail (255 or less)' => 'Maximum length of e-mail (255 or less)',  // E-mail length
    'Maximum length of subject (255 or less)' => 'Maximum length of subject (255 or less)',  // Subject length
    'Maximum length of comment (# of characters)' => 'Maximum length of comment (# of characters)',  // Comment length
    'Maximum number of lines in comment' => 'Maximum number of lines in comment',  // Comment lines
    'How many lines of comment to display' => 'How many lines of comment to display',  // Comment lines
    'Maximum length of source field (255 or less)' => 'Maximum length of source field (255 or less)',  // Source length
    'Maximum length of license field (255 or less)' => 'Maximum length of license field (255 or less)',  // License length
    'Require image or file to start new thread' => 'Require image or file to start new thread',  // Require file new thread
    'Always require an image or file' => 'Always require an image or file',  // Always require file
    'How to handle old threads:' => 'How to handle old threads:',  // Handling old threads
    'Archive' => 'Archive',  // Archive
    'Prune' => 'Prune',  // Prune
    'Nothing' => 'Nothing',  // Nothing
    'Threads per page' => 'Threads per page',  // Threads per page
    'Maximum number of pages' => 'Maximum number of pages',  // Max pages
    'Page buffer before archiving or pruning' => 'Page buffer before archiving or pruning',  // Buffer size
    'Maximum posts per thread' => 'Maximum posts per thread',  // Max posts per thread
    'Maximum thread bumps' => 'Maximum thread bumps',  // Max bumps
    'Force Anonymous posting' => 'Force Anonymous posting',  // Forced anonymous
    'Use FGSFDS field for commands (noko, sage, etc)' => 'Use FGSFDS field for commands (noko, sage, etc) instead of the e-mail field',  // Use the FGSFDS field
    'Name of the FGSFDS field' => 'Name of the FGSFDS field',  // Name of the FGSFDS field
    'Set language for the board' => 'Set language for the board',  // Board language
    'Set the indent marker next to replies' => 'Set the indent marker next to replies',  // Indent marker
    'Create animated GIF preview' => 'Create animated GIF preview',  // Animated GIF previews
    'Generate a SHA256 hash for uploaded files' => 'Generate a SHA256 hash for uploaded files',  // SHA256 file hash
    'Generate a SHA512 hash for uploaded files' => 'Generate a SHA512 hash for uploaded files',  // SHA512 file hash
    'Hash algorithm for post passwords' => 'Hash algorithm for post passwords',  // Post password algorithm
    'Hash algorithm for secure tripcodes' => 'Hash algorithm for secure tripcodes',  // Secure tripcode algorithm
    'Do staff passwords need rehashing. Usually used when changing algorithms or cost' => 'Do password rehash as needed. Usually used when changing algorithms or cost',  // Password rehash
    'Only check for duplicates in current thread when replying' => 'Only check for duplicates in current thread when replying',
    'Only check for duplicates in other op posts when creating new thread' => 'Only check for duplicates in other op posts when creating new thread',
    'Update board settings' => 'Update board settings',  // Update settings
    'New board defaults' => 'New board defaults',

    'Graphics files' => 'Graphics files',  // Enable graphics
    'Allow graphics files' => 'Allow graphics files',  // Allow audio files
    'Audio files' => 'Audio files',  // Enable audio
    'Allow audio files' => 'Allow audio files',  // Allow audio files
    'Video files' => 'Video files',  // Enable video
    'Allow video files' => 'Allow video files',  // Allow video files
    'Text/document files' => 'Text/document files',  // Enable documents
    'Allow general text and document files' => 'Allow general text and document files',  // Allow document/text files
    'Other files' => 'Other files',  // Enable multimedia
    'Allow other files' => 'Allow other files',  // Allow mother files
    'Archive files' => 'Archive files',  // Enable archive
    'Allow archive files' => 'Allow archive files',  // Allow archive files

    'File filters' => 'File filters',
    'Hash type (md5, sha1, sha256, sha512)' => 'Hash type (md5, sha1, sha256, sha512)',
    'File hashes' => 'File hashes',
    'Filter ID' => 'Filter ID',
    'Hash type' => 'Hash type',
    'File hash' => 'File hash',
    'File notes' => 'File notes',
    'Remove' => 'Remove',

    'Maximum file size allowed is ' => 'Maximum file size allowed is ',  // Rules line 1 first half
    'Images greater than ' => 'Images greater than ',  // Rules line 2 first half
    ' pixels will be thumbnailed.' => ' pixels will be thumbnailed.',  // Rules line 2 second half

    'This is the response to your appeal: ' => 'This is the response to your appeal: ',  // Response to appealed ban
    'No response has been given.' => 'No response has been given.',  // No response yet
    'You have already appealed this ban but the appeal has not been reviewed yet.' => 'You have already appealed this ban but the appeal has not been reviewed yet.',  // Appeal already filed
    'If you wish, you may appeal this ban. Enter your reason(s) why you should get unbanned in the box below. A staff member will (probably) review it.' => 'If you wish, you may appeal this ban. Enter your reason(s) why you should get unbanned in the box below. A staff member will (probably) review it.',  // Message about appealing a ban
    'You appeal has been reviewed. You cannot appeal again.' => 'You appeal has been reviewed. You cannot appeal again.',  // Appeal has been reviewed
    'Your appeal has been reviewed and the ban has been altered.' => 'Your appeal has been reviewed and the ban has been altered.',  // Appeal accepted but ban changed, not removed


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
