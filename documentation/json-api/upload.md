# Upload
Contains a representation of an upload.

## Structure

| Key               |Type     |Appears                    |Possible Values     |Description|                               
|:------------------|:--------|:--------------------------|:-------------------|:----------|
|`parent_thread`    |`integer`|Always                     |Any positive integer|ID of the parent thread.|
|`post_ref`         |`integer`|Always                     |Any positive integer|ID of post the upload is in.|
|`upload_order`     |`integer`|Always                     |Any positive integer|Order in which the upload was added.|
|`category`         |`string` |Always                     |Any string          |Type of upload.|
|`format`           |`string` |Always                     |Any string          |Format of upload.|
|`mime`             |`string` |If upload is a file        |Any string          |Mime type.|
|`filename`         |`string` |If upload is a file        |Any string          |Filename (without extension).|
|`extension`        |`string` |If upload is a file        |Any string          |File extension.|
|`display_width`    |`integer`|If upload is a file        |Any positive integer|Display width of upload.|
|`display_height`   |`integer`|If upload is a file        |Any positive integer|Display height of upload.|
|`preview_name`     |`string` |If present and visible     |Any string          |Filename of preview (without extension).|
|`preview_extension`|`string` |If present and visible     |Any string          |Preview extension.|
|`preview_width`    |`integer`|If present and visible     |Any positive integer|Display width of preview.|
|`preview_height`   |`integer`|If present and visible     |Any positive integer|Display height of preview.|
|`filesize`         |`integer`|If upload is a file        |Any positive integer|File size in bytes.|
|`md5`              |`string` |If upload is a file        |32-character string |MDS hash of upload.|
|`sha1`             |`string` |If upload is a file        |40-character string |SHA1 hash of upload.|
|`sha256`           |`string` |If upload is a file        |64-character string |SHA256 hash of upload.|
|`sha512`           |`string` |If upload is a file        |128-character string|SHA512 hash of upload.|
|`embed_url`        |`string` |If upload is an embed      |Any string          |URL (mostly for embeds).|
|`spoiler`          |`boolean`|Always                     |True or false       |Is marked as a spoiler.|
|`deleted`          |`boolean`|Always                     |True or false       |Has been deleted.|
|`exif`             |`string` |Always                     |Any string          |EXIF data.|
