# Nelliel Imageboard
## About
A relatively lightweight, expandable and highly configurable imageboard written in PHP. Nelliel was originally a modified version of [Futallaby](https://www.1chan.net/futallaby/) made to host Nigrachan. The software had good potential and was split off to independent development. The codebase has since been fully rewritten and expanded, with continuing refinements. It does not try to do everything imaginable but instead provides a solid core that is readily expandable for users with additional needs.

**At present Nelliel should be considered early beta software. Use with caution. It is still incomplete and will almost certainly have breaking changes.**

## Minimum Requirements
These are only the bare minimum requirements for Nelliel to function. **It is strongly recommended to use the latest software versions available.**

Required:
- PHP 5.6.25+
- PDO with MySQL, SQLite or PostgreSQL support
- MySQL 5.5.52+, MariaDB 5.5.52+, SQLite 3.6.20+ or PostgreSQL 9.2.18+
- iconv
- libxml 2.6+
- PHP GD
- PHP DOM

In most cases these requirements will be fulfilled by a standard PHP install and everything will run out of the box. In the case a component is not present it will usually be available in the default system repos and can be easily installed.

## Optional Requirements
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not required and the software will work fine without them:
- ImageMagick
- Imagick (PECL extension)
- mbstring

## Installation
See [INSTALL.md](INSTALL.md) for instructions.

## Plugin API
Nelliel currently has a very basic API for making plugins in the works. This will eventually allow extension of the software without having to worry about mods that can be easily broken during updates or having to mess with core code. **Do not use the plugin system yet! It is still undocumented and changing!**

## License
Nelliel is released under the [3-Clause BSD License](https://opensource.org/licenses/BSD-3-Clause). This can be viewed in LICENSE.txt or the imageboard's About Nelliel page.