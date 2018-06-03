# Nelliel Development Standards and Guidelines #

A guide to the development standards of Nelliel. Any contributions to the core codebase must follow these guidelines. Pull requests not adhering to the guidelines must be fixed before acceptance.
 
Developers of mods, plugins or other unofficial contributions are encouraged to follow this guide but it is not mandatory.

## Coding Style and Formatting ##
Nelliel follows the [PSR-1](http://www.php-fig.org/psr/psr-1/) and [PSR-4](http://www.php-fig.org/psr/psr-4/) standards.

In addition:
 - Ideal line length is 80 characters or less; soft limit is 120 characters.
 - Code follows Allman style (braces go on next line).
 - 4-space indentation for code; tabs are used for indentation of HTML.
 - Single quotes `' '` should be used for strings when practical.
 - No `?>` closing tags.

## Functions, Classes and Structure ##
 - Procedural is not evil. OOP is not the Holy Grail. Use what makes sense for a given situation.
 - Function names should be prefixed with `nel_`
 - Classes should be in the `Nelliel` namespace.
 - If a class instance or mutable variable needs to be accessible in a global scope it should be encapsulated inside a function.
 
## SQL and Queries ##
All queries and schemas should comply with ANSI standards when possible. In cases where a data type is not fully cross-compatible or has a differing name (e.g. the BINARY equivalent in PostgreSQL is BYTEA), an equivalent may be used for the specific RDBMS schema; the functionality however must be indistinguishable.

Query requirements:
 - Queries must go through PDO or NellielPDO.
 - Queries must be parameterized unless the entire query is hardcoded.
 - Identifiers must be placed in double quotes `" "`, with the exception of table or column creation.
 - All identifiers should be treated as being case sensitive.
 - Non-parameterized string literals must be placed inside single quotes `' '`.
 - Keywords should be ALL CAPS.

Fetched results from a query should be checked with loose comparison ( == ) unless the result has been explicitly cast to a type.
 
## Targets and Version Support ##
Any stable core functions and features contributed to Nelliel must be fully functional with the minimum versions listed below in addition to all later versions of the software. These minimum requirements will change over time due to certain circumstances including:
 - Usage of the minimum version becomes negligible.
 - A necessary feature or function cannot be reasonably implemented.
 - Forward compatibility becomes impractical or is no longer possible.

### PHP Support ###
At present Nelliel has a target version of **PHP 5.3.3**.

### Database Support ###
Currently supported RDBMS:
 - MySQL 5.1.73+
 - SQLite 3.6.20+
 - PostgreSQL 8.4.20+

### Browser Support ###
These are the minimum browser versions Nelliel should be compatible with:
 - Internet Explorer 11
 - Safari 11
 - Chrome 64
 - Firefox 56

## Versioning ##
Upon the initial 1.0 Release, Nelliel versioning will follow Major.Minor.Patch under these definitions:
 - Major: Significant backwards-incompatible changes or project-wide reconstruction.
 - Minor: Backwards-compatible changes, minor backwards-incompatible changes or new features introduced.
 - Patch: Bug fixes, code tweaks and feature refinements.

When the version changes, the constant `NELLIEL_VERSION` in file `imgboard.php` must be updated. A git tag should be created upon Major or Minor changes, or when a release is created.
 
## Other ##
 - Only basic initialization, loading and dispatch should happen in `imgboard.php`.
 - Configurations should be stored in the database whenever possible.
 - Nelliel should cause no PHP errors, warnings or notices when `E_ALL` and `E_STRICT` are enabled.