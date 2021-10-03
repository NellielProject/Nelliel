# Nelliel Development Standards and Guidelines

A guide to the basic development standards of Nelliel. Any contributions to the core codebase or official plugins must follow these guidelines.
 
Developers of plugins or other unofficial contributions are not required to follow this guide.

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
 - Only minimal early loading and initialization should happen in `imgboard.php`.
 - Configurations should be stored in the database whenever possible.
 - Nelliel should cause no PHP errors, warnings or notices when `E_ALL` and `E_STRICT` are enabled.
 - If a class instance or mutable variable needs to exist in global scope it must only be accessed through a function.
 
## SQL and Queries
All schemas and queries should follow SQL standard (ANSI) when reasonably possible. When something is not covered in the standard then a commonly implemented alternative can be used. If there is none widely used, a RDBMS-specific option may be added to the `SQLCompatibility` class. Likewise where a data type is not fully cross-compatible, the equivalent may be used so long as the behavior is indistinguishable.

In addition:
 - Queries must be done using PDO classes or a PDO-extending class such as NellielPDO.
 - Queries must be parameterized unless the entire query is hardcoded.
 - Queries should use parameter or value binding with a PDO type whenever possible.
 - Identifiers must be placed in double quotes `" "`, except during table or column creation.
 - All identifiers should be treated as case sensitive.
 - Non-parameterized string literals must be placed inside single quotes `' '`.
 - SQL keywords should be ALL CAPS.
 
## Targets and Support
Any core functions and features must target the software versions listed below in addition to maintaining compatibility with all later versions. If forward compatibility is not possible then an updated target version may be considered.

### Target Versions
PHP: 7.2  
MySQL: 5.7  
MariaDB: 10.2  
PostgreSQL: 10  
SQLite: 3.22  

### Browser Support
Nelliel must be fully functional with Firefox, Chrome, Safari and Edge. Maintaining compatibility with older versions of these browsers is encouraged when reasonably possible.

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
 - 600+: Reserved.
 
 All error codes in core must be listed in `error-reference.md`.
 
## Server-side vs Client-side
Anything that can be practically implemented without client-side scripting should be implemented server-side first. Client-side scripting should only be required for niceties or features that cannot be done fully server-side.
