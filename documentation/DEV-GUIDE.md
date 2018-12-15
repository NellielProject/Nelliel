# Nelliel Development Standards and Guideline

A guide to the development standards of Nelliel. Any contributions to the core codebase must follow these guidelines. Pull requests not adhering to the guidelines must be fixed before acceptance.
 
Developers of mods, plugins or other unofficial contributions are encouraged to follow this guide but it is not mandatory.

## Coding Style and Formatting
Nelliel follows the [PSR-1](http://www.php-fig.org/psr/psr-1/) and [PSR-4](http://www.php-fig.org/psr/psr-4/) standards.

In addition:
 - Ideal line length is 80 characters or less; soft limit is 120 characters.
 - Code follows Allman style (braces go on next line).
 - 4-space indentation for code; tabs are used for indentation of HTML.
 - Single quotes `' '` should be used for strings when practical.
 - No `?>` closing tags.

## Functions, Classes and Structure
 - Procedural is not evil. OOP is not the Holy Grail. Use what makes sense for a given situation.
 - Function names should be prefixed with `nel_`
 - Classes should be within the `Nelliel` namespace.
 - If a class instance or mutable variable needs to be accessible in a global scope it should be encapsulated inside a function.
 
## SQL and Queries
All schemas should comply with ANSI standards. In cases where a data type is not fully cross-compatible or has a differing name (e.g. the BINARY equivalent in PostgreSQL is BYTEA), an equivalent may be used for the specific RDBMS schema; however the behavior should be indistinguishable.

Query requirements:
 - Queries must be done through PDO or a PDO-extending class such as NellielPDO.
 - Queries must be parameterized unless the entire query is hardcoded.
 - Identifiers must be placed in double quotes `" "`, with the exception of table or column creation.
 - All identifiers should be treated as case sensitive.
 - Non-parameterized string literals must be placed inside single quotes `' '`.
 - SQL keywords should be ALL CAPS.
 - Database NULL is treated as unknown value.
 
## Targets and Version Support
Any stable core functions and features contributed to Nelliel must be fully functional with the minimum versions listed below in addition to all later versions of the software. These minimum requirements will change over time due to certain circumstances including (but not limited to):
 - Usage of the minimum version becomes negligible.
 - A necessary feature or function cannot be reasonably implemented.
 - Forward compatibility becomes impractical.

### PHP Support
At present Nelliel has a target version of **PHP 7.0**.

### Database Support
Minimum supported RDBMS versions:
 - MySQL 5.6.39
 - MariaDB 10.0.33
 - PostgreSQL 9.4.14
 - SQLite 3.16.0

### Browser Support
These are the minimum browser versions Nelliel must be compatible with:
 - Safari 11
 - Chrome 64
 - Firefox 56

## Versioning
Upon the initial 1.0 Release, Nelliel versioning will follow Major.Minor.Patch under these definitions:
 - Major: Major breaking changes or project-wide reconstruction.
 - Minor: Minor breaking changes, significant new features or changes introduced.
 - Patch: Bug fixes, code tweaks, refinements, minor new features.

When the version changes, the constant `NELLIEL_VERSION` in file `imgboard.php` must be updated. A git tag should be created upon Major or Minor changes, or when a formal release is created.

## Error Codes
Nelliel returns a numeric error id along with an error message. This keeps the better user experience without making it difficult to track exactly where in the code things went wrong (especially when other translations are involved). These are the designated ranges:
 - 0: Unknown or nonspecific error. Very rare.
 - 1-99: Content-related errors (upload problems, duplicate files, etc.).
 - 100-199: General system and input errors.
 - 200-299: Management-related system and input errors.
 - 300-599: Permissions errors.
 - 600-999: Reserved


## Other
 - Only basic initialization and early loading should happen in `imgboard.php`.
 - Configurations should be stored in the database whenever possible.
 - Nelliel should cause no PHP errors, warnings or notices when `E_ALL` and `E_STRICT` are enabled.