# Nelliel Imageboard
## About
A relatively lightweight, expandable [Futallaby](https://www.1chan.net/futallaby/)-style imageboard which attempts to retain the feel of old school imageboards.

**IMPORTANT: Nelliel is in a beta stage of development and will have bugs and incomplete features.**

## Notable Features
 - [Plugin API](documentation/plugins/plugin-api.md)
 - [JSON API](documentation/json-api/api.md)
 - Customizable filetype checks with 84 formats included by default
 - Over 400 settings
 - Web-based control panels for most configuration and functions
 - Flexible role and permission system

## Minimum Requirements
These are only the bare minimum requirements for Nelliel to function. Even if an earlier version somehow works, you will not receive any support. **It is strongly recommended to use the latest software versions available.**

Required:
- [PHP](https://www.php.net/) 7.2+
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/) 5.7+, [MariaDB](https://mariadb.org/) 10.2+, [PostgreSQL](https://www.postgresql.org/) 10+ or [SQLite](https://www.sqlite.org/) 3.22+
- [PHP PDO with corresponding support of your database choice](https://www.php.net/manual/en/book.pdo.php)
- [PHP GD](https://www.php.net/manual/en/book.image.php)
- [PHP DOM](https://www.php.net/manual/en/book.dom.php)
- [PHP iconv](https://www.php.net/manual/en/iconv.requirements.php)
- [PHP libxml](https://www.php.net/manual/en/book.libxml.php)
- [PHP session](https://www.php.net/manual/en/book.session.php)

These requirements are intentionally well behind the leading edge and should be widely available. The PHP extensions required are usually included and enabled with a standard install; if not, they are almost always available in official repositories. If for some reason even the minimum cannot be provided, find a new host. Srsly.

## Optional Requirements
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not required and the software will work without them:
- [ImageMagick](https://imagemagick.org/) or [GraphicsMagick](http://www.graphicsmagick.org/)
- [Imagick](https://www.php.net/manual/en/book.imagick.php) or [Gmagick](https://www.php.net/manual/en/book.gmagick.php) ([PECL](https://pecl.php.net/) extensions)
- [mbstring](https://www.php.net/manual/en/book.mbstring.php)
- [ExifTool](https://exiftool.org/)

## Installation
See [INSTALL.md](INSTALL.md) for installation instructions.

## Plugins
Nelliel has a plugin system to allow extension of the software without having to worry about mods that can be easily broken during updates. Details about using the API are available in [plugin-api.md](documentation/plugins/plugin-api.md).

## License
Nelliel is released under the [3-Clause BSD License](https://opensource.org/licenses/BSD-3-Clause). This can be viewed in [LICENSE.md](LICENSE.md) or the About Nelliel page.