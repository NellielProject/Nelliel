# Thread
Contains a representation of the thread.

**Location:** http(s)://`:site_url`/`:board_uri`/threads/`:thread_id`/`:thread_id`.json

## Structure
API information comes first:

|Key          |Type     |Appears|Possible Values     |Description|                               
|:------------|:--------|:------|:-------------------|:----------|
|`api_output` |`string` |Always |Any string          |Which API output is being generated.|
|`api_version`|`integer`|Always |Any positive integer|Version of the API used. 0 indicates in development.|

General information about the thread follows:

|Key                |Type     |Appears|Possible Values            |Description|                               
|:------------------|:--------|:------|:--------------------------|:----------|
|`thread_id`        |`integer`|Always |Any positive integer       |ID of the thread.|
|`bump_time`        |`integer`|Always |64-bit Unix timestamp      |Last thread bump time.|
|`bump_time_milli`  |`integer`|Always |0-999                      |Last thread bump time milliseconds. Used with `bump_time`.|
|`last_update`      |`integer`|Always |64-bit Unix timestamp      |Last thread update.|
|`last_update_milli`|`integer`|Always |0-999                      |Last thread update milliseconds. Used with `last_update`.|
|`post_count`       |`integer`|Always |Any positive integer       |Total number of posts in the thread.|
|`bump_count`       |`integer`|Always |Any positive integer       |Total number of thread bumps.|
|`total_uploads`    |`integer`|Always |Any positive integer       |Total uploads in the thread.|
|`file_count`       |`integer`|Always |Any positive integer       |Total files in the thread.|
|`embed_count`      |`integer`|Always |Any positive integer       |Total embeds in the thread.|
|`permasage`        |`boolean`|Always |true or false              |Is the thread permasaged.|
|`sticky`           |`boolean`|Always |true or false              |Is the thread stickied.|
|`locked`           |`boolean`|Always |true or false              |Is the thread locked.|
|`cyclic`           |`boolean`|Always |true or false              |Is the thread cyclic.|
|`old`              |`boolean`|Always |true or false              |Thread is marked as old.|
|`shadow`           |`boolean`|Always |true or false              |Shadow of a moved thread.|
|`slug`             |`string` |Always |Any string                 |SEO-friendly slug.|
|`posts`            |`array`  |Always |Zero or more `post` objects|The list of posts in the thread.|

## Objects
### `post`
Contains information about a post.

|Key              |Type     |Appears                      |Possible Values              |Description|                               
|:----------------|:--------|:----------------------------|:----------------------------|:----------|
|`post_number`    |`integer`|Always                       |Any positive integer         |Post ID.|
|`parent_thread`  |`integer`|Always                       |Any positive integer         |ID of the parent thread.|
|`reply_to`       |`integer`|Always                       |Any positive integer         |ID of the post being replied to.|
|`name`           |`string` |If post has name             |Any string                   |Name of the poster.|
|`tripcode`       |`string` |If post has tripcode         |Any string                   |Tripcode.|
|`secure_tripcode`|`string` |If post has secure tripcode  |Any string                   |Secure tripcode.|
|`capcode`        |`string` |If post has capcode          |Any string                   |Capcode. May contain HTML.|
|`email`          |`string` |If post has email            |Any string                   |E-Mail address.|
|`subject`        |`string` |If post has subject          |Any string                   |Subject of the post.|
|`comment`        |`string` |If post has comment          |Any string                   |Commentary. May contain HTML or other formatting.|
|`post_time`      |`integer`|Always                       |64-bit Unix timestamp        |Time the post was made.|
|`post_time_milli`|`integer`|Always                       |0-999                        |Post time milliseconds. Used with `post_time`.|
|`formatted_time` |`string` |Always                       |Any string                   |Formatted version of `post_time`.|
|`total_uploads`  |`integer`|Always                       |Any positive integer         |Total uploads in the post.|
|`file_count`     |`integer`|Always                       |Any positive integer         |Total files in the post.|
|`embed_count`    |`integer`|Always                       |Any positive integer         |Total embeds in the post.|
|`op`             |`boolean`|Always                       |true or false                |Is the post OP (first in thread).|
|`sage`           |`boolean`|Always                       |true or false                |Is the post saged.|
|`mod_comment`    |`string` |If post has moderator comment|Any string                   |Comment added by staff.|
|`uploads`        |`array`  |Always                       |Zero or more `upload` objects|The list of uploads attached to the post.|

### `upload`
Contains the information about a file or embed.

| Key               |Type     |Appears                          |Possible Values     |Description|                               
|:------------------|:--------|:--------------------------------|:-------------------|:----------|
|`parent_thread`    |`integer`|Always                           |Any positive integer|ID of the parent thread.|
|`post_ref`         |`integer`|Always                           |Any positive integer|ID of post the upload is in.|
|`upload_order`     |`integer`|Always                           |Any positive integer|Order in which the upload was added.|
|`category`         |`string` |Always                           |Any string          |Type of upload.|
|`format`           |`string` |Always                           |Any string          |Format of upload.|
|`mime`             |`string` |If upload is a file              |Any string          |Mime type.|
|`filename`         |`string` |If upload is a file              |Any string          |Filename (without extension).|
|`extension`        |`string` |If upload is a file              |Any string          |File extension.|
|`display_width`    |`integer`|If upload is a file              |Any positive integer|Display width of upload.|
|`display_height`   |`integer`|If upload is a file              |Any positive integer|Display height of upload.|
|`preview_name`     |`string` |If upload has a static preview   |Any string          |Filename of preview (without extension).|
|`preview_extension`|`string` |If upload has an animated preview|Any string          |Preview extension.|
|`preview_width`    |`integer`|If upload has a preview          |Any positive integer|Display width of preview.|
|`preview_height`   |`integer`|If upload has a preview          |Any positive integer|Display height of preview.|
|`filesize`         |`integer`|If upload is a file              |Any positive integer|File size in bytes.|
|`md5`              |`string` |If upload is a file              |32-character string |MDS hash of upload.|
|`sha1`             |`string` |If upload is a file              |40-character string |SHA1 hash of upload.|
|`sha256`           |`string` |If upload is a file              |64-character string |SHA256 hash of upload.|
|`sha512`           |`string` |If upload is a file              |128-character string|SHA512 hash of upload.|
|`embed_url`        |`string` |If upload is an embed            |Any string          |URL (mostly for embeds).|
|`spoiler`          |`boolean`|Always                           |true or false       |Is marked as a spoiler.|
|`deleted`          |`boolean`|Always                           |true or false       |Has been deleted.|
|`exif`             |`string` |Always                           |Any string          |EXIF data.|
