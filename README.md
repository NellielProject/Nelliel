# Nelliel Imageboard #

A lightweight and fast imageboard based of Futallaby.

Nelliel was originally a modified version of Futallaby made to host Nigrachan. The software had good potential and was split off to
 independent development. While retaining an interface similar to Futallaby (and many other imageboards) it has been rewritten and
 expanded into a unique codebase.

## Minimum Requirements ##
Nelliel's minimum requirements are kept to the lowest reasonable level for current functionality while also remaining forward compatible.
Presently this is what's included with the basic RHEL/CentOS 6 install, which can satisfy all dependencies for Nelliel and is also the primary
 testing environment. These are only the minimum requirements for Nelliel to function; it is strongly recommended to use the latest
 software versions available.

Required:

- PHP 5.3.3+
- GD 2.0.35+
- PDO with MySQL or SQLite drivers
- MySQL 5.1.73+ or SQLite 3.6.20+ or PostgreSQL 8.4.20+
- iconv

## Optional Requirements ##
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not
 required and the software will work fine without them:

- ImageMagick
- mbstring

## Development Stage ##
Nelliel should be considered early beta software. It is functional but somewhat buggy and still subject to major changes that
 may break existing content. Try it out if you like but don't use it for anything too serious just yet.

## Plugin API ##
Nelliel currently has a very basic API for making plugins. This will eventually allow extension of the software without having to worry about
 mods that can be easily broken during updates or having to mess with core code. It is not recommended to use
 the plugin system yet as it continues to undergo changes and lacks documentation.\

## License ##
Nelliel is released under the Modified BSD license. This can be viewed in LICENSE.txt or the imageboard's About page.