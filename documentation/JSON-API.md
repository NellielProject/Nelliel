# Nelliel JSON API

This is the read-only API for Nelliel. The basis is similar to the 4chan and vichan read-only APIs: a JSON-encoded output representing content such as threads or indexes. This documentation covers the base API outputs and should work on all Nelliel installs. Additional output may be included by plugins or other mods a specific site is running.

NOTE: This API is **not** directly compatible with the 4chan or vichan APIs.

**NOTE: The API is currently unfinished and should not be used yet except for testing.**

**API Version:** 0.2

## Thread JSON
**Location:** http(s)://`<site url>`/`<board directory>`/threads/`<thread id>`/thread-`<thread id>`.json
Will contain one thread object followed by a `posts` object which may contain one or more post objects. Each post object may contain one or more content objects.

### Thread object
Contains information for a thread.

|Attribute          |Type|Possible Values            |Optional|Description        |                               
|:------------------|:-------- |:--------------------|:-------|:------------------|
|`thread_id`        |`integer` |1-2147483647         |No      |ID of the thread.|
|`first_post`       |`integer` |1-2147483647 or null |No      |First post in the thread. null if there are no posts.|
|`last_post`        |`integer` |1-2147483647 or null |No      |Last post in the thread. null if there are no posts.|
|`total_files`      |`integer` |0-2147483647         |No      |Total files in the thread.|
|`last_update`      |`integer` |64-bit Unix timestamp|No      |Last thread update.|
|`last_update_milli`|`integer` |0-999                |No      |Last thread update milliseconds. Used with `last_update` when high precision is needed.|
|`post_count`       |`integer` |0-2147483647         |No      |Total number of posts in the thread. It is possible for a thread to have no posts.|
|`thread_sage`      |`integer` |0 or 1               |No      |Is the thread permasaged. Boolean integer value: 0 is false, 1 is true|
|`sticky`           |`integer` |0 or 1               |No      |Is the thread stickied. Boolean integer value: 0 is false, 1 is true|
|`locked`           |`integer` |0 or 1               |No      |Is the thread locked. Boolean integer value: 0 is false, 1 is true|

### Post object
Contains information for a post.

|Attribute         |Type|Possible Values            |Optional|Description        |                               
|:-----------------|:---------|:--------------------|:-------|:------------------|
|`post_number`     |`integer` |1-2147483647         |No      |Post ID.|
|`parent_thread`   |`integer` |1-2147483647 or null |No      |ID of the parent thread. It is possible for a post to have no thread.|
|`reply_to`        |`integer` |1-2147483647 or null |No      |ID of the post being replied to. null if not replying to a post.|
|`poster_name`     |`string`  |text                 |Yes     |Name of the poster.|
|`tripcode`        |`string`  |text                 |Yes     |Tripcode.|
|`secure_tripcode` |`string`  |text                 |Yes     |Secure tripcode.|
|`email`           |`string`  |text                 |Yes     |E-Mail address.|
|`subject`         |`string`  |text                 |Yes     |Subject of the post.|
|`comment`         |`string`  |text                 |Yes     |Commentary. May contain HTML or other formatting.|
|`post_time`       |`integer` |64-bit Unix timestamp|No      |Time the post was made.|
|`post_time_milli` |`integer` |0-999                |No      |Post time milliseconds. Used with `post_time` when high precision is needed.|
|`has_file`        |`integer` |0 or 1               |No      |If the post has a file. Boolean integer value: 0 is false, 1 is true|
|`file_count`      |`integer` |0-32767              |No      |File count for the post.|
|`op`              |`integer` |0 or 1               |No      |Is the post OP (first in thread). Boolean integer value: 0 is false, 1 is true|
|`sage`            |`integer` |0 or 1               |No      |Is the post saged. Boolean integer value: 0 is false, 1 is true|
|`mod_comment`     |`string`  |text                 |Yes     |Comment added by staff|

### Content object
Contains the information for a file or other content.

|Attribute          |Type|Possible Values               |Optional|Description        |                               
|:------------------|:---------|:-----------------------|:-------|:------------------|
|`parent_thread`    |`integer` |1-2147483647 or null    |No      |ID of the parent thread. null if there is no parent thread.|
|`post_ref`         |`integer` |1-2147483647 or null    |No      |ID of post the content is in. null if there is no post ref.|
|`content_order`    |`integer` |1-32767                 |No      |Order in which the content was added.|
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

