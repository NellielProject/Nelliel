# Nelliel Imageboard #

A lightweight and fast imageboard. Designed with a focus on being a full-featured single board that is easy to set up and will work
 on virtually any halfway-sane hosting.

Nelliel was originally a modified version of Futallaby made to host Nigrachan. The software had good potential and was split off to
 independent development. While retaining an interface similar to Futallaby (and many other imageboards) it has been rewritten and
 expanded into a fully unique codebase.

## Minimum Requirements ##
Nelliel's minimum requirements are kept to the lowest reasonable level while also remaining forward compatible. This tends to
 correspond with the oldest version of Red Hat Enterprise Linux still in [Production 3/Maintenance](https://access.redhat.com/support/policy/updates/errata#Production_3_Phase)
 phase; at present that is RHEL 5/CentOS 5, which can satisfy all dependencies for Nelliel. Most testing is in fact done on said OS,
 with some additional testing on later versions of PHP.

Required:

- PHP 5.1.6+
- GD 2.0.28+
- PDO with MySQL or SQLite drivers
- MySQL 5.0.95+ or SQLite 3.3.6+
- iconv

## Optional Requirements ##
These are optional things that Nelliel or one of its libraries can utilize for extra features or performance increases. They are not
 required and the software will work perfectly fine without them:

- ImageMagick
- mbstring

## Development Stage ##
Nelliel is currently considered beta software. It is functional but somewhat buggy and likely subject to major changes that
 may break existing content. Try it out if you like but don't use it for anything serious just yet.

## Plugin API ##
Nelliel has recently been given a very basic API for making plugins. This will allow extension of the software without having to worry about
 mods that can be easily broken during updates or having to mess with core code. At present there are only a couple of hooks but a
 rather extensive list is planned for the future as well as documentation and guidelines for making plugins. It is not recommended to use
 the plugin system yet, however.

## License ##
Nelliel is released under the Modified BSD license. This can be viewed in LICENSE.txt or the imageboard's About page.