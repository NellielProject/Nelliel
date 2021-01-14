# Nelliel JSON API
Nelliel's JSON API provides a read-only representation of resources such as threads and indexes. This documentation covers the base API outputs and should apply to all Nelliel installs. Additional output may be included by plugins.

NOTE: This API is **not** directly compatible with other imageboard APIs.

**API Version:** 0 (in development)

## Endpoints
### Boards
**Location:** http(s)://`:site_url`/boards.json

Contains a representation of boards present on the site.

### Index
**Location:** http(s)://`:site_url`/`:board_directory`/index`:index_number`.json

NOTE: For index pages the first will be `index` without a number.

Contains a representation of the specified index page.

### Thread
**Location:** http(s)://`:site_url`/`:board_directory`/threads/`:thread_id`/`:thread_id`.json

Contains a representation of the specified thread.

## Object Descriptors
NOTE: All attribute values can be null. Null values should be treated as empty or unknown.

### `version`
Version information.

|Attribute Key           |Type     |Possible Values|Description|                               
|:-----------------------|:--------|:--------------|:----------|
|`api_version`           |`integer`|0-2147483647   |Version of the API used to generate data. 0 indicates in development version.|

### `boards`
Contains a list of boards and their basic information. Contains one `cooldowns` object.

|Attribute Key           |Type     |Possible Values|Description|                               
|:-----------------------|:--------|:--------------|:----------|
|`board_id`              |`string` |text           |ID of the board.|
|`name`                  |`string` |text           |Displayed name of the board.|
|`description`           |`string` |text           |Short description of the board.|
|`language`              |`string` |text           |Language code for the board default.|
|`forced_anonymous`      |`boolean`|true or false  |Is forced anonymous posting enabled.|
|`threads_per_page`      |`integer`|1-2147483647   |Maximum threads shown on each index page.|
|`page_limit`            |`integer`|1-2147483647   |Maximum index pages available.|
|`max_bumps`             |`integer`|1-2147483647   |Maximum times a thread can be bumped.|
|`max_posts`             |`integer`|1-2147483647   |Maximum number of posts in a thread.|
|`max_filesize`          |`integer`|1-2147483647   |Maximum size of uploaded files (in kilobytes).|
|`require_content_start` |`boolean`|true or false  |Image, file or content required for new thread.|
|`require_content_always`|`boolean`|true or false  |Image, file or content required for any post.|
|`allow_tripcodes`        |`boolean`|true or false  |Are tripcodes allowed when posting.|

### `cooldowns`
Contains a list of cooldowns for posting on a board.

|Attribute Key|Type     |Possible Values|Description|                               
|:------------|:--------|:--------------|:----------|
|`threads`    |`integer`|0-2147483647   |Minimum time between making a new thread (in seconds).|
|`replies`    |`integer`|0-2147483647   |Minimum time between making a new post/reply (in seconds).|

### `index`
Contains information about an index page.

|Attribute Key      |Type     |Possible Values|Description|                               
|:------------------|:--------|:--------------|:----------|
|`thread_count`     |`integer`|0-2147483647   |Number of threads on the page.|

### `thread-list`
Contains zero or more `thread` objects.

### `thread`
Contains information about a thread.

|Attribute Key         |Type     |Possible Values      |Description|                               
|:---------------------|:--------|:--------------------|:----------|
|`thread_id`           |`integer`|0-2147483647         |ID of the thread.|
|`first_post`          |`integer`|0-2147483647         |First post in the thread.|
|`last_post`           |`integer`|0-2147483647         |Last post in the thread.|
|`last_bump_time`      |`integer`|64-bit Unix timestamp|Last thread bump time.|
|`last_bump_time_milli`|`integer`|0-999                |Last thread bump time milliseconds. Used with `last_bump_time` when high precision is needed.|
|`last_update`         |`integer`|64-bit Unix timestamp|Last thread update.|
|`last_update_milli`   |`integer`|0-999                |Last thread update milliseconds. Used with `last_update` when high precision is needed.|
|`post_count`          |`integer`|0-2147483647         |Total number of posts in the thread.|
|`content_count`       |`integer`|0-2147483647         |Total content in the thread.|
|`permasage`         |`boolean`|true or false        |Is the thread permasaged.|
|`sticky`              |`boolean`|true or false        |Is the thread stickied.|
|`locked`              |`boolean`|true or false        |Is the thread locked.|

### `post-list`
Contains zero or more `post` objects.

### `post`
Contains information about a post.

|Attribute Key    |Type     |Possible Values      |Description|                               
|:----------------|:--------|:--------------------|:----------|
|`post_number`    |`integer`|0-2147483647         |Post ID.|
|`parent_thread`  |`integer`|0-2147483647         |ID of the parent thread.|
|`reply_to`       |`integer`|0-2147483647         |ID of the post being replied to.|
|`poster_name`    |`string` |text                 |Name of the poster.|
|`tripcode`       |`string` |text                 |Tripcode.|
|`secure_tripcode`|`string` |text                 |Secure tripcode.|
|`capcode`        |`string` |text                 |Capcode. May contain HTML.|
|`email`          |`string` |text                 |E-Mail address.|
|`subject`        |`string` |text                 |Subject of the post.|
|`comment`        |`string` |text                 |Commentary. May contain HTML or other formatting.|
|`post_time`      |`integer`|64-bit Unix timestamp|Time the post was made.|
|`post_time_milli`|`integer`|0-999                |Post time milliseconds. Used with `post_time` when high precision is needed.|
|`timestamp`      |`integer`|text                 |Formatted version of `post_time`|
|`has_content`    |`boolean`|true or false        |If the post has content.|
|`content_count`  |`integer`|0-32767              |Content count for the post.|
|`op`             |`boolean`|true or false        |Is the post OP (first in thread).|
|`sage`           |`boolean`|true or false        |Is the post saged.|
|`mod_comment`    |`string` |text                 |Comment added by staff.|

### `content-list`
Contains zero or more `content` objects.

### `content`
Contains the information about a file or other content.

|Attribute Key      |Type     |Possible Values         |Description|                               
|:------------------|:--------|:-----------------------|:----------|
|`parent_thread`    |`integer`|0-2147483647            |ID of the parent thread.|
|`post_ref`         |`integer`|0-2147483647            |ID of post the content is in.|
|`content_order`    |`integer`|1-32767                 |Order in which the content was added.|
|`type`             |`string` |text                    |Type of content.|
|`format`           |`string` |text                    |Format of content.|
|`mime`             |`string` |text                    |Mime type.|
|`filename`         |`string` |text                    |Filename (without extension).|
|`extension`        |`string` |text                    |File extension.|
|`display_width`    |`integer`|1-2147483647            |Display width of content.|
|`display_height`   |`integer`|1-2147483647            |Display height of content.|
|`preview_name`     |`string` |text                    |Filename of preview (without extension).|
|`preview_extension`|`string` |text                    |Preview extension.|
|`preview_width`    |`integer`|1-32767                 |Display width of preview.|
|`preview_height`   |`integer`|1-32767                 |Display height of preview.|
|`filesize`         |`integer`|0-2147483647            |File size in bytes.|
|`md5`              |`string` |32-character hex string |MDS hash of content.|
|`sha1`             |`string` |40-character hex string |SHA1 hash of content.|
|`sha256`           |`string` |64-character hex string |SHA256 hash of content.|
|`sha512`           |`string` |128-character hex string|SHA512 hash of content.|
|`embed_url`        |`string` |text                    |URL (mostly for embeds).|
|`spoiler`          |`boolean`|true or false           |Is marked as a spoiler.|
|`deleted`          |`boolean`|true or false           |Has been deleted.|
|`exif`             |`string` |text                    |EXIF data.|
