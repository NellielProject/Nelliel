# Nelliel Changelog
## v0.9.6.12 (2018/07/03)
### Added
 - Config setting to disable plugins

### Changed
 - Finish primary work on plugin API
 - Moved language handling to Language class

### Removed
 - language/language.php

## v0.9.6.11 (2018/07/03)
### Added
 - Filter hooks

### Changed
 - Updated example plugin

## v0.9.6.10 (2018/07/02)
### Breaking
 - Overhaul of plugin system (WIP)

### Added
 - Recursive file list function
 - Add caching for language files

### Changed
 - Path info uses SPL functions now
 - Default time limit on sessions increased
 - Improve error handler
 - writeFile now has better atomic function
 - Many tweaks and cleanup

### Removed
 - plugins.php, old plugin hooks

## v0.9.6.9 (2018/06/24)
### Added
 - Option to extract gettext strings to a .pot file

### Removed
 - Old lang.en-us.php file

## v0.9.6.8 (2018/06/24)
### Added
 - SmallPHPGettext library

### Changed
 - Converted language handling to gettext

# Nelliel Changelog
## v0.9.6.7 (2018/06/19)
### Added
 - Configurable defaults for new boards

### Changed
 - Improve file and post deletion functions
 - Updated database schema markers
 - nel_generate_salted_hash can now take an optional parameter for salt length
   
## v0.9.6.6 (2018/06/19)
### Changed
 - Preview generation moved to its own class
 - Set PDO to stringify fetches for now so we can use strict checking again
 - Handle the remaining likely case of filename collisions

### Removed
 - file_functions.php

### Fixed
 - No longer generate empty posts when a thread's post count is wrong
 - Add alt text field is now available for all files in the posting form
   
## v0.9.6.5 (2018/06/19)
### Breaking
 - Each post now gets a directory for files within the thread directory. This should avoid most filename collisions.

### Changed
 - Upload functions moved to new class

## v0.9.6.4 (2018/06/17)
### Added
 - More configurable parameters for file duplication checks
 - Now including changelog

### Changed
 - Improvements on preview generation code
 - Backup charset conversion for tripcode in case iconv is not present
 - Some work on tripcode generation
 - Small updates to README and DEV-GUIDE
 
###  Fixed
 - If tripcode is present but no name given, don't fill in Anonymous default name
 - A bit of derp in the standard tripcode gen

## v0.9.6.3 (2018/06/14)
### Added
 - Beginnings of JSON API

### Changed
 - `type`, `format` and `extension` columns in file table can no longer be null
 - Finish converting the insert data functions to the new prepared queries
 - InnoDB engine unavailable error now uses the standard error function
 - Store the file extension for preview files separately

## v0.9.6.2 (2018/06/14)
### Changed
 - Simplify the millisecond time function
 - Insert data in setup returns to individual queries

### Fixed
 - Generate internal caches after running setup and board creation

## v0.9.6.1 (2018/06/13)
### Added
 - Argon2I support for passwords when using PHP >7.2.0

### Changed
 - Move date check from NellielPDO (wtf) to initializations.php
 - MySQL encoding and charset changed to utf8mb4
 - Converting 4-byte UTF-8 to entities no longer needed for MySQL
 - Overhaul of hashing and password functions

### Removed
 - general_salt config
 - Dropping support for SHA256/SHA512 in passwords; can still be used for basic hashes

## v0.9.6 (2018/06/12)
### Breaking
 - Minimum requirements updated:
  - PHP 5.4.16
  - MySQL 5.5.52
  - MariaDB 5.5.52
  - PostgreSQL 9.2.18

### Added
 - SHA512 can be stored for files (off by default)
 - When available, SHA256 and SHA512 hashes are now displayed in file meta
 - Functions to show/hide threads or posts
 - Namespacing for javascript
 - Simple MP4 and WEBM embedding
 - MariaDB support

### Changed
 - Update to NellielTemplates 1.0.2
 - Javascript refactoring and tweaks
 - Split regen functions for site and board
 - Minor CSS and HTML tweaks
 - Copyright update

## v0.9.5.1 (2018/5/27)
### Added
 - File filter system
 - Basic unit test setup
 
### Fixed
 - Preview size calculations for certain cases

## v0.9.5 (2018/5/17)
### Breaking
 - Full conversion to multi-board. No backwards compatibility with pre-v0.9.5
 
### Added
 - Foreign key constraints for easier and cleaner deletion
 - Board data table
 - Board ID for board-specific input/output
 - Per-board settings
 - Very basic install instructions
 - Initial dev guidelines
 - Support for .ogv, .webm, .3gp, .cel, .kcf, .art file extensions
 
### Changed
 - Each board gets a thread, post and file table plus the archive versions
 - Redid preview generation code
 - Cleanup and sync themes
 - Cleanup header/footer
 - Just copy animated GIF for preview if smaller dimensions than preview limits
 - Redo dispatch
 - Better handling of Unicode/UTF-8
 - Some decoupling and conversion to classes
 - File format detection regexes updated
 - Filetype handling redone
 - Combine configs into config.php with minimal parameters
 - Setup updates
 
### Removed
 - The old $dataforce god-variable is gone
 - No more MD5 support for passwords

## Fixed
 - Many derps

## v0.9.4.12 (2018/01/17)
### Breaking
 - Begin conversion to multi-board
 - Board id and directory updated
 - Restructure of files
 - Database now uses binary type for hashes and IPs
 
### Changed
 - Work on rendering code

## v0.9.4.11 (2018/01/11)
### Changed
 - Javascript updates


## v0.9.4.10 (2018/01/11)
### Changed
 - Change some settings defaults
 - Update README
 - Javascript updates
 
### Fixed
 - Various CSS fixes


## v0.9.4.9 (2018/01/04)
### Added
 - Alt text can now be added when uploading files

### Changed
 - Various tweaks and fixes

## v0.9.4.8 (2018/01/03)
### Changed
 - Expand thread panel
 - Work on removing $dataforce god-variable

## v0.9.4.7 (2018/01/03)
### Added
Add unicode to entities conversion functions

### Changed
 - Rearrange some of imgboard.php

### Fixed
 - Got post quotes and links working again
 - HTML5 fixes

## v0.9.4.6 (2018/01/01)
### Changed
 - File handling moved to FileHandler class
 - Bits of cleanup and fixing
 - Improve handling of FGSFDS field input
 - Improve login throttling

## v0.9.4.5 (2018/01/01)
### Changed
 - Improvements on the dispatch system
 - Update post/thread archiving system
 - Update error handling system
 - Debugging and cleanup

## v0.9.4.4 (2017/12/28)
### Changed
 - Cleanup

## v0.9.4.3 (2017/12/26)
### Changed
 - Redo ban system

## v0.9.4.2 (2017/12/16)
### Added
 - Autoloading
 - Add NellielTemplates library
 - Add phpDOMExtend library

### Changed
 - Fix up sessions
 - Update login system
 - Update some paths
 - Got a couple TODOs done
 - Begin converting templating to DOM-based system
 - Set up i18n system for language handling
 - Improve internal caching code

## v0.9.4.1 (2017/12/16)
### Changed
 - Prepare for PSR-4 complaince
 - Update posting form input
 - Database overhaul and new RDBMS support
 - Query updates
  - Thread and file handling changes and fixes
  
### Fixed
 - Fix regen
 - Fix bans being applied

### Removed
 - Retire the old rendering system

## v0.9.4 (2017/10/15)
### Breaking
 - Requirements update:
  - PHP 5.3.3 minimum

### Added
 - Basic staff structure and permissions

### Changed
 - Many many minor fixes and tweaks
 - Update code for handling tripcodes
 - Update config system
 - Language updates