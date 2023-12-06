# Error Reference
This is a quick reference of all error codes, where they trigger and a shorthand description.

Error 0: Unknown error.  
Error 1: Database connection error. (database.php)  
Error 2: Invalid database type. (database.php)  
Error 3: Posting flood. (NewPost/NewPost.php)  
Error 4: Thread locked. (NewPost/NewPost.php)  
Error 5: Thread inaccessible. (NewPost/NewPost.php)  
Error 6: Thread missing. (NewPost/NewPost.php)  
Error 7: Maximum posts in thread. (NewPost/NewPost.php)  
Error 8: Thread archived. (NewPost/NewPost.php)  
Error 9: Post empty. (NewPost/NewPost.php)  
Error 10: Comment too long. (NewPost/NewPost.php)  
Error 11: No new post, board locked. (NewPost/NewPost.php)  
Error 12: File too big (config limit). (NewPost/Uploads.php)  
Error 13: File too big (server limit). (NewPost/Uploads.php)  
Error 14: File too big (form limit). (NewPost/Uploads.php)  
Error 15: Partial upload. (NewPost/Uploads.php)  
Error 16: File not found. (NewPost/Uploads.php)  
Error 17: Temp directory unavailable. (NewPost/Uploads.php)  
Error 18: Cannot write file. (NewPost/Uploads.php)  
Error 19: PHP extension interference. (NewPost/Uploads.php)  
Error 20: Something wrong with upload. (NewPost/Uploads.php)  
Error 21: Unrecognized filetype. (NewPost/Uploads.php)  
Error 22: Filetype not allowed. (NewPost/Uploads.php)  
Error 23: Filetype does not match extension. (NewPost/Uploads.php)  
Error 24: Banned file. (NewPost/Uploads.php)  
Error 25: Duplicate file. (NewPost/Uploads.php)  
Error 26: No uploads allowed. (NewPost/Uploads.php)  
Error 27: Too many uploads. (NewPost/Uploads.php)  
Error 28: File required. (NewPost/Uploads.php)  
Error 29: Files not allowed. (NewPost/Uploads.php)  
Error 30: Too many files. (NewPost/Uploads.php)  
Error 31: Embed required. (NewPost/Uploads.php)  
Error 32: Embeds not allowed. (NewPost/Uploads.php)  
Error 33: Too many embeds. (NewPost/Uploads.php)  
Error 34: Max uploads for thread. (NewPost/Uploads.php)  
Error 35: Duplicate embed. (NewPost/Uploads.php)  
Error 36: Max total filesize. (NewPost/Uploads.php)  
Error 37: Threads per hour limit. (NewPost/NewPost.php)  
Error 38: Dimensions are too large. (NewPost/Uploads.php)  
Error 39: Tripcode required. (NewPost/PostData.php)  
Error 40: No POST data. (NewPost/PostData.php)  
Error 41: Name required. (NewPost/PostData.php)  
Error 42: Email required. (NewPost/PostData.php)  
Error 43: Subject required. (NewPost/PostData.php)  
Error 44: Comment required. (NewPost/PostData.php)  
Error 45: Too many cites. (NewPost/PostData.php)  
Error 46: Too many cross-board cites. (NewPost/PostData.php)  
Error 47: Too many comment URLs. (NewPost/PostData.php)  
Error 48: Name too long. (NewPost/PostData.php)  
Error 49: Email too long. (NewPost/PostData.php)  
Error 50: Subject too long. (NewPost/PostData.php)  
Error 51: Comment too long. (NewPost/PostData.php)  
Error 52: Not valid upload. (NewPost/Uploads.php)  
Error 53: R9K already muted. (NewPost/PostData.php)  
Error 54: R9K unoriginal. (NewPost/PostData.php)  
Error 55: R9K unoriginal and muted. (NewPost/PostData.php)  
Error 56: Uploads disabled. (NewPost/Uploads.php)  
Error 57: Upload flood. (NewPost/NewPost.php)  
Error 58: File too big for category. (NewPost/Uploads.php)  
Error 59: Rejected by wordfilter. (Wordfilters.php)  

Error 60: Password does not match post. (Content/Post.php)  
Error 61: Board locked, can't remove file. (Content/Upload.php)  
Error 62: Board locked, can't remove post. (Content/Post.php)  
Error 63: Board locked, can't remove thread. (Content/Thread.php)  
Error 64: Delete cooldown. (Content/Post.php)  
Error 65: Replies threshold. (Content/Thread.php)  
Error 66: Age threshold. (Content/Thread.php)  
Error 67: Invalid embed URL. (Content/Upload.php)  
Error 68: Delete time limit. (Content/Post.php)  

Error 70: CAPTCHA failed. (CAPTCHA.php)  
Error 72: Requesting CAPTCHAs too fast. (CAPTCHA.php)  

Error 75: Post rejected by checkpoint. (NewPost/ActionsPost.php)  

Error 102: InnoDB engine unavailable. (Setup/Installer/DatabaseSetup.php)  
Error 103: Failed to create database table. (Table.php)  
Error 107: Install not done. (initializations.php)  
Error 108: Install already done. (Setup/Installer.php)  
Error 110: Version mismatch. (initializations.php)    
Error 111: No valid request. (Dispatch/Start.php)     
Error 114: Wrong install key. (Setup/Installer.php)   

Error 130: Report item limit. (Reports.php)  
Error 131: Delete item limit. (ThreadHandler.php)  

Error 140: Filename purged by filter. (Utility/FileHandler.php)  

Error 150: Invalid length. (BanHammer.php)  
Error 151: Can't appeal range ban. (Snacks.php)  
Error 152: IP does not match. (Snacks.php)  
Error 153: Pending appeal. (Snacks.php)  
Error 154: Missing or invalid IP. (BanHammer.php)  
Error 155: No IP hash. (BanHammer.php)  
Error 156: Ban appeals disabled. (Snacks.php)  
Error 157: DNSBL (DNSBL.php)  
Error 158: No board or domain. (BanHammer.php)  
Error 159: Not minimum appeal time. (Snacks.php)  
Error 160: Appeal not allowed. (Snacks.php)  
Error 161: Max appeals reached. (Snacks.php)  

Error 180: Board doesn't exist. (Admin/AdminBoards.php) 

Error 200: No user ID. (Account/Login.php)  
Error 201: No password. (Account/Login.php)  
Error 202: Wrong user ID or password. (Account/Login.php)  
Error 203: Fast login attempts. (Account/Login.php)  

Error 210: No user ID. (Account/Register.php)  
Error 211: No password. (Account/Register.php)  
Error 212: User already exists. (Account/Register.php)  
Error 213: Passwords do not match. (Account/Register.php)  
Error 214: Install ID doesn't match. (Account/Register.php)  
Error 215: Registration disabled. (Account/Register.php)  
Error 216: Username too long. (Account/Register.php)  
Error 217: Password too long. (Account/Register.php)  

Error 220: Secure session only. (Account/Session.php)  
Error 221: Expired session. (Account/Session.php)  
Error 222: Inactive user. (Account/Session.php)  

Error 224: Must be logged in. (Account/Session.php)  
Error 225: Cannot view PM. (PrivateMessage.php)  

Error 230: User does not exist. (Admin/AdminUsers.php)  
Error 231: Role does not exist. (Admin/AdminRoles.php)  

Error 240: Board URI already exists. (Admin/AdminBoards.php)  
Error 241: Trouble with board URI. (Admin/AdminBoards.php)  
Error 242: Invalid board URI characters. (Admin/AdminBoards.php)  
Error 243: Board URI is empty. (Admin/AdminBoards.php)  
Error 244: Board URI is reserved. (Admin/AdminBoards.php)  
Error 245: Board URI too long. (Admin/AdminBoards.php)  
Error 246: Empty subdirectory name. (Admin/AdminBoards.php)  
Error 247: Subdirectory name too long. (Admin/AdminBoards.php)  
Error 248: Invalid directory characters. (Admin/AdminBoards.php)  
Error 249: Problematic URI. (Admin/AdminBoards.php)  

Error 260: Thread not exist. (Admin/AdminThreads.php)  
Error 261: Post not exist. (Admin/AdminThreads.php)  
Error 262: Can't move replies. (Admin/AdminThreads.php)  
Error 263: Can't move uploads. (Admin/AdminThreads.php)  
Error 264: Invalid target thread. (Admin/AdminThreads.php) 

Error 270: Maximum static pages. (Admin/AdminPages.php)  

Error 300: Default permission error. (Admin/Admin.php|Dispatch/Dispatch.php)  

Error 310: perm_bans_view (Admin/AdminBans.php)  
Error 311: perm_bans_add (Admin/AdminBans.php)   
Error 312: perm_bans_modify (Admin/AdminBans.php)   
Error 313: perm_bans_delete (Admin/AdminBans.php)   

Error 315: perm_manage_blotter (Admin/AdminBlotter.php)   

Error 320: perm_modify_board_defaults (Admin/AdminBoardDefaults.php)  

Error 325: perm_boards_view (Admin/AdminBoards.php)  
Error 326: perm_boards_add (Admin/AdminBoards.php)   
Error 327: perm_boards_modify (Admin/AdminBoards.php)   
Error 328: perm_boards_delete (Admin/AdminBoards.php)   

Error 330: perm_modify_board_config (Admin/AdminBoardSettings.php)   

Error 340: perm_manage_file_filters (Admin/AdminFileFilters.php)  

Error 345: perm_manage_filetypes (Admin/AdminFiletypes.php)  

Error 350: perm_manage_image_sets (Dispatch/DispatchImageSets.php)  

Error 355: perm_view_public_logs (Dispatch/DispatchLogs.php)  
Error 356: perm_view_system_logs (Dispatch/DispatchLogs.php)  

Error 360: perm_manage_news (Admin/AdminNews.php)  

Error 365: perm_manage_permissions (Admin/AdminPermissions.php)  

Error 370: perm_reports_view (Admin/AdminReports.php)  
Error 371: perm_reports_dismiss (Admin/AdminReports.php)  

Error 375: perm_view_roles (Admin/AdminRoles.php)  
Error 376: perm_manage_roles (Admin/AdminRoles.php)  

Error 380: perm_modify_site_config (Admin/AdminSiteSettings.php)  

Error 385: perm_manage_styles (Dispatch/DispatchStyles.php)  

Error 390: perm_manage_templates (Dispatch/DispatchTemplates.php)  

Error 395: perm_view_users (Admin/AdminUsers.php)  
Error 396: perm_manage_users (Admin/AdminUsers.php)  

Error 400: perm_manage_wordfilters (Admin/AdminWordfilters.php)  

Error 405: perm_manage_embeds (Admin/AdminEmbeds.php)  

Error 410: perm_threads_access (Admin/AdminThreads.php)  
Error 411: perm_modify_content_status (Admin/AdminThreads.php)  
Error 412: perm_edit_posts (Admin/AdminThreads.php)  
Error 413: perm_delete_by_ip (Admin/AdminThreads.php)  
Error 414: perm_move_content (Admin/AdminThreads.php)  
Error 415: perm_delete_content (Admin/AdminThreads.php)  

Error 420: perm_manage_content_ops (Admin/AdminContentOps.php)  

Error 425: perm_manage_capcodes (Admin/AdminCapcodes.php)  

Error 430: perm_noticeboard_view (Admin/AdminNoticeboard.php)  
Error 431: perm_noticeboard_post (Admin/AdminNoticeboard.php)  
Error 432: perm_noticeboard_delete (Admin/AdminNoticeboard.php)  

Error 435: perm_manage_scripts (Admin/AdminScripts.php)  

Error 440: perm_view_unhashed_ip (Dispatch/DispatchIPInfo.php)  
Error 441: perm_view_ip_info (Dispatch/DispatchIPInfo.php)  
Error 442: perm_add_ip_notes (Dispatch/DispatchIPInfo.php)  
Error 443: perm_delete_ip_notes (Dispatch/DispatchIPInfo.php)  

Error 450: perm_manage_plugins (Dispatch/DispatchPlugins.php)  

Error 500: perm_regen_pages (site) (Dispatch/DispatchRegen.php)  
Error 501: perm_regen_cache (site) (Dispatch/DispatchRegen.php)  
Error 502: perm_regen_pages (global) (Dispatch/DispatchRegen.php)  
Error 503: perm_regen_cache (global) (Dispatch/DispatchRegen.php)  
Error 504: perm_regen_pages (board) (Dispatch/DispatchRegen.php)  
Error 505: perm_regen_cache (board) (Dispatch/DispatchRegen.php)  
Error 506: perm_regen_overboard (Dispatch/DispatchRegen.php)  

Error 510: perm_extract_gettext (Language/Language.php)  
Error 511: perm_use_private_messages (Dispatch/DispatchAccount.php) 