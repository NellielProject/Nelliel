# Nelliel Development Standards and Guidelines

A guide to the basic development standards of Nelliel. Any contributions to the core codebase or official plugins must follow these guidelines.

## Code Conventions
PHP code follows [PSR-1](https://www.php-fig.org/psr/psr-1/), [PSR-2](https://www.php-fig.org/psr/psr-2/), [PSR-3](https://www.php-fig.org/psr/psr-3/) and [PSR-4](https://www.php-fig.org/psr/psr-4/) standards.

Javascript and other code should follow common or recommended practice for the language.

Markup languages (HTML, CSS, etc) must use tabs for indentation.

### Naming
 - Global constants must be ALL CAPS and prefixed with `NEL_` or `NELLIEL_`.
 - Function names in global scope must be prefixed with `nel_`.
 - Variable names must be snake_case.
 - Markup identifiers must be kebab-case.

### Other
 - Procedural is not evil. OOP is not the Holy Grail. A function is fine too.
 - Nelliel should cause no PHP errors, warnings or notices when `E_ALL` and `E_STRICT` are enabled.
 - If a class instance or mutable variable needs to exist in global scope it must only be accessed through a function.
 
## SQL and Queries
All schemas and queries should follow SQL standards or widely used alternatives. If something RDBMS-specific is necessary it may be added to the `SQLCompatibility` class.

 - Queries must be done through PDO or NellielPDO.
 - Queries must be parameterized unless the entire query is hardcoded.
 - Identifiers must be placed in double quotes `" "`, except during table or column creation.
 - SQL keywords must be ALL CAPS.
 - Non-parameterized string literals must be placed inside single quotes `' '`.
 - All identifiers should be treated as case sensitive.
 
## Targets and Support
Any core functions and features must target the software versions listed below in addition to maintaining compatibility with all later versions. If forward compatibility is not possible then an updated target version may be considered.

### Target Versions
PHP: 7.2  
MySQL: 5.7  
MariaDB: 10.2  
PostgreSQL: 10  
SQLite: 3.22  

### Browser Support
Nelliel must be fully functional with recent versions of major browsers. Maintaining compatibility with older versions or less common browsers is encouraged when reasonable to do so.

## Versioning
After the 1.0 Release, Nelliel versioning will follow Major.Minor.Patch under these definitions:
 - Major: Major breaking changes or project-wide rework.
 - Minor: Minor breaking changes, requirements update, significant new features or changes introduced.
 - Patch: Bug fixes, code tweaks, refinements, minor new features.

When the version changes, the constant `NELLIEL_VERSION` in file `imgboard.php` must be updated. A git tag must be created upon Major or Minor changes, or when a formal release is created.

## Error Codes
Nelliel returns a numeric error id along with an error message. This keeps the benefit of a descriptive message for the user while making it easier to track where in the code the problem occurs. These are the designated ranges:
 - 0: Unknown or nonspecific error.
 - 1-199: General content, system and input errors.
 - 200-299: Management and account errors.
 - 300-599: Permissions errors.
 - 600-999: Reserved.
 - 1000: Plugin-generated error.
 
 All error codes in core must be listed in `error-reference.md`.
 
## Server-side vs Client-side
As much functionality and rendering as possible should be implemented server-side. Client-side scripting should only be used for niceties or features that cannot be done fully server-side. All primary functions for posting and moderation must continue to work even if a user has disabled scripts. 

## Core vs Plugins
Most functionality can be put in the core code base. However anything that is:
 - both complex to implement and opinionated or niche
 - subject to regular changes in implementation
 - reliant on third-party services
 
 should be strongly considered for implementation via plugin instead of added directly to core. This simplifies maintenance and avoids bloating the code base over time.

## AI Code 	Generation
Current code generation AI (e.g. GitHub Copilot) raises issues with copyright and open source licenses with no practical method of vetting the original context. Until these tools become better actors or the mentioned concerns are reasonably settled, code from broad-sourced generators is prohibited.