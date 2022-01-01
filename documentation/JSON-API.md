# Nelliel JSON API
Nelliel's JSON API provides a read-only representation of resources such as threads and indexes. This documentation covers the base API outputs and should apply to all Nelliel installs. Additional output may be included by plugins.

This API is **not** directly compatible with 4chan or other imageboard JSON APIs.

**API Version:** 0 (in development)

## Endpoints
### Thread
**Location:** http(s)://`:site_url`/`:board_directory`/threads/`:thread_id`/`:thread_id`.json

Contains a representation of the specified thread.

## Object Descriptors
Most values have potential to be null if no data is present.

### `version`
Version information. This will be the first item in all endpoint files.

|Attribute Key           |Type     |Possible Values|Description|                               
|:-----------------------|:--------|:--------------|:----------|
|`api_version`           |`integer`|0-2147483647   |Version of the API used to generate data. 0 indicates in development version.|

### `thread`
Contains information about a thread.

|Attribute Key         |Type     |Possible Values      |Description|                               
|:---------------------|:--------|:--------------------|:----------|
|`thread_id`           |`integer`|0-2147483647         |ID of the thread.|
|`bump_time`           |`integer`|64-bit Unix timestamp|Last thread bump time.|
|`bump_time_milli`     |`integer`|0-999                |Last thread bump time milliseconds. Used with `bump_time` when high precision is needed.|
|`last_update`         |`integer`|64-bit Unix timestamp|Last thread update.|
|`last_update_milli`   |`integer`|0-999                |Last thread update milliseconds. Used with `last_update` when high precision is needed.|
|`post_count`          |`integer`|0-2147483647         |Total number of posts in the thread.|
|`total_uploads`       |`integer`|0-2147483647         |Total uploads in the thread.|
|`file_count`          |`integer`|0-2147483647         |Total files in the thread.|
|`embed_count`         |`integer`|0-2147483647         |Total embeds in the thread.|
|`permasage`           |`boolean`|true or false        |Is the thread permasaged.|
|`sticky`              |`boolean`|true or false        |Is the thread stickied.|
|`locked`              |`boolean`|true or false        |Is the thread locked.|
|`cyclic`              |`boolean`|true or false        |Is the thread cyclic.|

### `posts`
Contains zero or more `post` objects.

### `post`
Contains information about a post.

|Attribute Key    |Type     |Possible Values      |Description|                               
|:----------------|:--------|:--------------------|:----------|
|`post_number`    |`integer`|0-2147483647         |Post ID.|
|`parent_thread`  |`integer`|0-2147483647         |ID of the parent thread.|
|`reply_to`       |`integer`|0-2147483647         |ID of the post being replied to.|
|`name`           |`string` |text                 |Name of the poster.|
|`capcode`        |`string` |text                 |Capcode. May contain HTML.|
|`tripcode`       |`string` |text                 |Tripcode.|
|`secure_tripcode`|`string` |text                 |Secure tripcode.|
|`email`          |`string` |text                 |E-Mail address.|
|`subject`        |`string` |text                 |Subject of the post.|
|`comment`        |`string` |text                 |Commentary. May contain HTML or other formatting.|
|`post_time`      |`integer`|64-bit Unix timestamp|Time the post was made.|
|`post_time_milli`|`integer`|0-999                |Post time milliseconds. Used with `post_time` when high precision is needed.|
|`formatted_time` |`string` |text                 |Formatted version of `post_time`|
|`total_uploads`  |`integer`|0-32767              |Total uploads in the post.|
|`file_count`     |`integer`|0-32767              |Total files in the post.|
|`embed_count`    |`integer`|0-32767              |Total embeds in the post.|
|`op`             |`boolean`|true or false        |Is the post OP (first in thread).|
|`sage`           |`boolean`|true or false        |Is the post saged.|
|`mod_comment`    |`string` |text                 |Comment added by staff.|

### `uploads`
Contains zero or more `upload` objects.

### `upload`
Contains the information about a file, embed or other uploads.

|Attribute Key      |Type     |Possible Values         |Description|                               
|:------------------|:--------|:-----------------------|:----------|
|`parent_thread`    |`integer`|0-2147483647            |ID of the parent thread.|
|`post_ref`         |`integer`|0-2147483647            |ID of post the upload is in.|
|`upload_order`     |`integer`|1-32767                 |Order in which the upload was added.|
|`type`             |`string` |text                    |Type of upload.|
|`format`           |`string` |text                    |Format of upload.|
|`mime`             |`string` |text                    |Mime type.|
|`filename`         |`string` |text                    |Filename (without extension).|
|`extension`        |`string` |text                    |File extension.|
|`display_width`    |`integer`|1-2147483647            |Display width of upload.|
|`display_height`   |`integer`|1-2147483647            |Display height of upload.|
|`preview_name`     |`string` |text                    |Filename of preview (without extension).|
|`preview_extension`|`string` |text                    |Preview extension.|
|`preview_width`    |`integer`|1-32767                 |Display width of preview.|
|`preview_height`   |`integer`|1-32767                 |Display height of preview.|
|`filesize`         |`integer`|0-2147483647            |File size in bytes.|
|`md5`              |`string` |32-character hex string |MDS hash of upload.|
|`sha1`             |`string` |40-character hex string |SHA1 hash of upload.|
|`sha256`           |`string` |64-character hex string |SHA256 hash of upload.|
|`sha512`           |`string` |128-character hex string|SHA512 hash of upload.|
|`embed_url`        |`string` |text                    |URL (mostly for embeds).|
|`spoiler`          |`boolean`|true or false           |Is marked as a spoiler.|
|`deleted`          |`boolean`|true or false           |Has been deleted.|
|`exif`             |`string` |text                    |EXIF data.|
