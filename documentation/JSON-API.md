# Nelliel JSON API
Nelliel's JSON API provides a read-only representation of resources such as threads and indexes. This documentation covers the base API outputs and should apply to all Nelliel installs. Additional output may be included by plugins or other mods a specific site is running.

NOTE: This API is **not** directly compatible with the 4chan or vichan APIs.

**NOTE: The base API is currently unfinished and should not be used except for testing.**

**API Version:** 0 (in development)

## Endpoints
### Boards
**Location:** http(s)://`:site_url`/boards.json

Contains a representation of boards present on the site.

### Index
**Location:** http(s)://`:site_url`/`:board_directory`/index-`:index_number`.json

NOTE: For index pages, `:index-number` starts at 1.

Contains a representation of the specified index page.

### Thread
**Location:** http(s)://`:site_url`/`:board_directory`/threads/`:thread_id`/thread-`:thread_id`.json

Contains a representation of the specified thread.

## Object Descriptors
### `version`
Version information.

|Attribute Key           |Type     |Possible Values      |Optional|Description        |                               
|:-----------------------|:--------|:--------------------|:-------|:------------------|
|`api_version`           |`integer`|0-2147483647         |No      |Version of the API used to generate data.|

### `boards`
Contains a list of boards and their basic information. Contains one `cooldowns` object.

|Attribute Key           |Type     |Possible Values      |Optional|Description        |                               
|:-----------------------|:--------|:--------------------|:-------|:------------------|
|`board_id`              |`string` |text                 |No      |ID of the board.|
|`name`                  |`string` |text                 |No      |Displayed name of the board.|
|`slogan`                |`string` |text                 |No      |Slogan/subtitle for the board.|
|`description`           |`string` |text                 |No      |Short description of the board.|
|`language`              |`string` |text                 |No      |Language code for the board default.|
|`forced_anonymous`      |`boolean`|true or false        |No      |Is forced anonymous posting enabled.|
|`threads_per_page`      |`integer`|1-2147483647         |No      |Maximum threads shown on each index page.|
|`page_limit`            |`integer`|1-2147483647         |No      |Maximum index pages available.|
|`max_bumps`             |`integer`|1-2147483647         |No      |Maximum times a thread can be bumped.|
|`max_posts`             |`integer`|1-2147483647         |No      |Maximum number of posts in a thread.|
|`max_filesize`          |`integer`|1-2147483647         |No      |Maximum size of uploaded files (in kilobytes).|
|`require_content_start` |`boolean`|true or false        |No      |Image, file or content required for new thread.|
|`require_content_always`|`boolean`|true or false        |No      |Image, file or content required for any post.|
|`allow_tripkeys`        |`boolean`|true or false        |No      |Are tripcodes allowed when posting.|

### `cooldowns`
Contains a list of cooldowns for posting on a board.

|Attribute Key           |Type      |Possible Values      |Optional|Description        |                               
|:---------------------|:--------|:--------------------|:-------|:------------------|
|`threads`             |`integer`|1-2147483647         |No      |Minimum time between making a new thread (in seconds).|
|`replies`             |`integer`|1-2147483647         |No      |Minimum time between making a new post/reply (in seconds).|

### `index`
Contains information about an index page.

|Attribute Key      |Type     |Possible Values      |Optional|Description        |                               
|:------------------|:--------|:--------------------|:-------|:------------------|
|`thread_count`     |`integer`|1-2147483647         |No      |Number of threads on the page.|

### `thread-list`
Contains zero or more `thread` objects.

### `thread`
Contains information about a thread.

|Attribute Key         |Type     |Possible Values      |Optional|Description        |                               
|:---------------------|:--------|:--------------------|:-------|:------------------|
|`thread_id`           |`integer`|1-2147483647         |No      |ID of the thread.|
|`first_post`          |`integer`|1-2147483647         |No      |First post in the thread.|
|`last_post`           |`integer`|1-2147483647         |No      |Last post in the thread.|
|`last_bump_time`      |`integer`|64-bit Unix timestamp|No      |Last thread bump time.|
|`last_bump_time_milli`|`integer`|0-999                |No      |Last thread bump time milliseconds. Used with `last_bump_time` when high precision is needed.|
|`last_update`         |`integer`|64-bit Unix timestamp|No      |Last thread update.|
|`last_update_milli`   |`integer`|0-999                |No      |Last thread update milliseconds. Used with `last_update` when high precision is needed.|
|`post_count`          |`integer`|1-2147483647         |No      |Total number of posts in the thread.|
|`content_count`       |`integer`|0-2147483647         |No      |Total content in the thread.|
|`thread_sage`         |`boolean`|true or false        |No      |Is the thread permasaged.|
|`sticky`              |`boolean`|true or false        |No      |Is the thread stickied.|
|`locked`              |`boolean`|true or false        |No      |Is the thread locked.|
|`slug`                |`string` |text                 |Yes     |Human-friendly thread ID.|

### `post-list`
Contains zero or more `post` objects.

### `post`
Contains information about a post.

|Attribute Key     |Type     |Possible Values      |Optional|Description        |                               
|:-----------------|:--------|:--------------------|:-------|:------------------|
|`post_number`     |`integer`|1-2147483647         |No      |Post ID.|
|`parent_thread`   |`integer`|1-2147483647         |No      |ID of the parent thread.|
|`reply_to`        |`integer`|1-2147483647         |Yes     |ID of the post being replied to. Omitted if not replying to a post.|
|`poster_name`     |`string` |text                 |Yes     |Name of the poster.|
|`tripcode`        |`string` |text                 |Yes     |Tripcode.|
|`secure_tripcode` |`string` |text                 |Yes     |Secure tripcode.|
|`capcode_text`    |`string` |text                 |Yes     |Capcode text. May contain HTML.|
|`email`           |`string` |text                 |Yes     |E-Mail address.|
|`subject`         |`string` |text                 |Yes     |Subject of the post.|
|`comment`         |`string` |text                 |Yes     |Commentary. May contain HTML or other formatting.|
|`post_time`       |`integer`|64-bit Unix timestamp|No      |Time the post was made.|
|`post_time_milli` |`integer`|0-999                |No      |Post time milliseconds. Used with `post_time` when high precision is needed.|
|`timestamp`       |`integer`|text                 |No      |Formatted version of `post_time`|
|`has_content`     |`boolean`|true or false        |No      |If the post has content.|
|`content_count`   |`integer`|0-32767              |No      |Content count for the post.|
|`op`              |`boolean`|true or false        |No      |Is the post OP (first in thread).|
|`sage`            |`boolean`|true or false        |No      |Is the post saged.|
|`mod_comment`     |`string` |text                 |Yes     |Comment added by staff.|

### `content-list`
Contains zero or more `content` objects.

### `content`
Contains the information about a file or other content.

|Attribute Key      |Type     |Possible Values         |Optional|Description        |                               
|:------------------|:--------|:-----------------------|:-------|:------------------|
|`parent_thread`    |`integer`|1-2147483647            |Yes     |ID of the parent thread. Omitted only if not part of a thread.|
|`post_ref`         |`integer`|1-2147483647            |No      |ID of post the content is in.|
|`content_order`    |`integer`|1-32767                 |No      |Order in which the content was added.|
|`type`             |`string` |text                    |No      |Type of content.|
|`format`           |`string` |text                    |No      |Format of content.|
|`mime`             |`string` |text                    |Yes     |Mime type.|
|`filename`         |`string` |text                    |Yes     |Filename (without extension).|
|`extension`        |`string` |text                    |Yes     |File extension.|
|`display_width`    |`integer`|0-2147483647            |Yes     |Display width of content.|
|`display_height`   |`integer`|0-2147483647            |Yes     |Display height of content.|
|`preview_name`     |`string` |text                    |Yes     |Filename of preview (without extension).|
|`preview_extension`|`string` |text                    |Yes     |Preview extension.|
|`preview_width`    |`integer`|0-32767                 |Yes     |Display width of preview.|
|`preview_height`   |`integer`|0-32767                 |Yes     |Display height of preview.|
|`filesize`         |`integer`|0-2147483647            |Yes     |File size.|
|`md5`              |`string` |32-character hex string |Yes     |MDS hash of content.|
|`sha1`             |`string` |40-character hex string |Yes     |SHA1 hash of content.|
|`sha256`           |`string` |64-character hex string |Yes     |SHA256 hash of content.|
|`sha512`           |`string` |128-character hex string|Yes     |SHA512 hash of content.|
|`url`              |`string` |text                    |Yes     |URL (mostly for embeds).|
|`spoiler`          |`boolean`|true or false           |No      |Is marked as a spoiler.|
|`exif`             |`string` |text                    |Yes     |EXIF data.|
|`meta`             |`string` |text                    |Yes     |Other metadata.|
