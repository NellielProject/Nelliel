# Nelliel Imageboard
## About
A relatively lightweight, expandable Futallaby-style imageboard. Originally based on the [Futallaby](https://www.1chan.net/futallaby/) imageboard software.

**WARNING: At present Nelliel is in a pre-release beta stage of development. It is still incomplete and undergoing breaking changes. Try it out if you want but don't rely on it for long-term use yet!**

## Minimum Requirements
These are only the bare minimum requirements for Nelliel to function. Even if an earlier version somehow works, you will not receive any support. **It is strongly recommended to use the latest software versions available.**

Required:
- PHP 7.1+
- MySQL 5.6+, MariaDB 10.1+, PostgreSQL 9.5+ or SQLite 3.20+
- PHP PDO with MySQL, MariaDB, PostgreSQL or SQLite support
- PHP GD
- PHP DOM
- iconv

These requirements are well behind the leading edge and should be widely available. If for some reason even the minimum cannot be provided, find a new host. Srsly.

## Optional Requirements
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not required and the software will work fine without them:
- ImageMagick or GraphicsMagick
- Imagick or Gmagick (PECL extensions)
- mbstring

## Installation
See [INSTALL.md](INSTALL.md) for installation instructions.

## Plugin API
Nelliel currently has a basic API for making plugins in the works. This will eventually allow extension of the software without having to worry about mods that can be easily broken during updates or having to mess with core code. Details about using the API available in [PLUGIN-DEV.md](documentation/plugins/PLUGIN-DEV.md).

## License
Nelliel is released under the [3-Clause BSD License](https://opensource.org/licenses/BSD-3-Clause). This can be viewed in [LICENSE.md](LICENSE.md) or the imageboard's About Nelliel page.