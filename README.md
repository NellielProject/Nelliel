# Nelliel Imageboard
## About
A relatively lightweight, expandable and highly configurable imageboard written in PHP. Nelliel was originally a modified version of [Futallaby](https://www.1chan.net/futallaby/) made to host Nigrachan. The software had good potential and was split off to independent development. The codebase has since been fully rewritten and expanded, with continuing refinements. It does not try to do everything imaginable but instead provides a solid core that is readily expandable for users with additional needs.

**At present Nelliel should be considered beta software. Use with caution. It is still incomplete and will have breaking changes.**

## Minimum Requirements
These are only the confirmed bare minimum requirements for Nelliel to function. Earlier versions may work but are likely to be unstable and will not receive any support. **It is strongly recommended to use the latest software versions available.**

Required:
- PHP 7.0+
- MySQL 5.6+, MariaDB 10+, PostgreSQL 9.4+ or SQLite 3.16+
- PHP PDO with MySQL, MariaDB, PostgreSQL or SQLite support
- PHP GD
- PHP DOM
- iconv

In most cases these requirements will be fulfilled by a standard PHP install and everything will run out of the box. In the case a component is not present it will usually be available in the default system repos and can be easily installed.

## Optional Requirements
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not required and the software will work fine without them:
- ImageMagick
- Imagick (PECL extension)
- mbstring

## Installation
See [INSTALL.md](INSTALL.md) for installation instructions.

## Plugin API
Nelliel currently has a basic API for making plugins in the works. This will eventually allow extension of the software without having to worry about mods that can be easily broken during updates or having to mess with core code. Details about using the API available in [PLUGIN-DEV.md](documentation/PLUGIN-DEV.md).

## License
Nelliel is released under the [3-Clause BSD License](https://opensource.org/licenses/BSD-3-Clause). This can be viewed in [LICENSE.txt](LICENSE.txt) or the imageboard's About Nelliel page.