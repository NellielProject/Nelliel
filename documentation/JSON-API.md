# Nelliel JSON API

This is the read-only API for Nelliel. The basis is similar to the 4chan and vichan read-only APIs: a JSON-encoded output representing content such as threads or indexes. This documentation covers the base API outputs and should work on all Nelliel installs. Additional output may be included by plugins or other mods a specific site is running.

NOTE: This API is **not** directly compatible with the 4chan or vichan APIs.

**API Version:** 0.1

## Thread JSON
**Location:** http(s)://`<site url>`/`<board directory>`/threads/`<thread id>`/thread-`<thead id>`.json

### Thread object
Contains information about the thread.

|Attribute          |Type|Possible Values          |Optional|Description        |                               
|:------------------|:-------- |:------------------|:-------|:------------------|
|`thread_id`        |`integer` |1-2147483647       |No      |ID of the thread.|
|`first_post`       |`integer` |1-2147483647       |No      |First post in the thread.|
|`last_post`        |`integer` |1-2147483647       |No      |Last post in the thread.|
|`total_files`      |`integer` |0-2147483647       |No      |Total files in the thread.|
|`last_update`      |`integer` |0-92233720368775807|No      |Last thread update. 64-bit Unix time stamp.|
|`last_update_milli`|`integer` |0-999              |No      |Last thread update milliseconds. Used with `last_update` when high precision is needed.|
|`post_count`       |`integer` |0-2147483647       |No      |Total number of posts in the thread. It is possible for a thread to have no posts.|
|`thread_sage`      |`integer` |0 or 1             |No      |Is the thread permasaged. Boolean integer value: 0 is false, 1 is true|
|`sticky`           |`integer` |0 or 1             |No      |Is the thread stickied. Boolean integer value: 0 is false, 1 is true|
|`locked`           |`integer` |0 or 1             |No      |Is the thread locked. Boolean integer value: 0 is false, 1 is true|

### Posts object
Contains the information for posts in the thread.

|Attribute         |Type|Possible Values          |Optional|Description        |                               
|:-----------------|:---------|:------------------|:-------|:------------------|
|`post_number`     |`integer` |1-2147483647       |No      |Post ID.|
|`parent_thread`   |`integer` |1-2147483647       |No      |ID of the parent thread.|
|`poster_name`     |`string`  |text               |Yes     |Name of the poster.|
|`tripcode`        |`string`  |text               |Yes     |Tripcode.|
|`secure_tripcode` |`string`  |text               |Yes     |Secure tripcode.|
|`email`           |`string`  |text               |Yes     |E-Mail address.|
|`subject`         |`string`  |text               |Yes     |Subject of the post.|
|`comment`         |`string`  |text               |Yes     |Commentary. May contain HTML or other formatting.|
|`post_time`       |`integer` |0-92233720368775807|No      |Time the post was made. 64-bit Unix time stamp.|
|`post_time_milli` |`integer` |0-999              |No      |Post time milliseconds. Used with `post_time` when high precision is needed.|
|`has_file`        |`integer` |0 or 1             |No      |If the post has a file. Boolean integer value: 0 is false, 1 is true|
|`file_count`      |`integer` |0-32767            |No      |File count for the post.|
|`op`              |`integer` |0 or 1             |No      |Is the post OP (first in thread). Boolean integer value: 0 is false, 1 is true|
|`sage`            |`integer` |0 or 1             |No      |Is the post saged. Boolean integer value: 0 is false, 1 is true|
|`mod_comment`     |`string`  |text               |Yes     |Comment added by staff|

### Content object
Contains the information for files or other content. Is found inside the post objects.

|Attribute          |Type|Possible Values               |Optional|Description        |                               
|:------------------|:---------|:-----------------------|:-------|:------------------|
|`parent_thread`    |`integer` |1-2147483647            |No      |ID of the parent thread.|
|`post_ref`         |`integer` |1-2147483647            |No      |ID of post the content is in|
|`content_order`    |`integer` |1-32767                 |No      |Order the content was added to post.|
|`type`             |`string`  |text                    |No      |Type of content.|
|`format`           |`string`  |text                    |No      |Format of content.|
|`mime`             |`string`  |text                    |Yes     |Mime type.|
|`filename`         |`string`  |text                    |Yes     |Filename (without extension).|
|`extension`        |`string`  |text                    |Yes     |File extension.|
|`display_width`    |`integer` |0-2147483647            |Yes     |Display width of content.|
|`display_height`   |`integer` |0-2147483647            |Yes     |Display height of content.|
|`preview_name`     |`string`  |text                    |Yes     |Filename of preview (without extension).|
|`preview_extension`|`string`  |text                    |Yes     |Preview extension.|
|`preview_width`    |`integer` |0-32767                 |Yes     |Display width of preview.|
|`preview_height`   |`integer` |0-32767                 |Yes     |Display height of preview.|
|`filesize`         |`integer` |0-2147483647            |Yes     |File size.|
|`md5`              |`string`  |32-character hex string |Yes     |MDS hash of content.|
|`sha1`             |`string`  |40-character hex string |Yes     |SHA1 hash of content.|
|`sha256`           |`string`  |64-character hex string |Yes     |SHA256 hash of content.|
|`sha512`           |`string`  |128-character hex string|Yes     |SHA512 hash of content.|
|`license`          |`string`  |text                    |Yes     |Content license.|
|`alt_text`         |`string`  |text                    |Yes     |Alt text.|
|`url`              |`string`  |text                    |Yes     |URL (mostly for embeds).|
|`exif`             |`string`  |text                    |Yes     |EXIF data.|
|`meta`             |`string`  |text                    |Yes     |Other metadata.|

