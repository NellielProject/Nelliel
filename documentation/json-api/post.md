# Post
Contains a representation of a post.

## Structure

|Key              |Type     |Appears                   |Possible Values                           |Description|                               
|:----------------|:--------|:-------------------------|:-----------------------------------------|:----------|
|`post_number`    |`integer`|Always                    |Any positive integer                      |Post ID.|
|`parent_thread`  |`integer`|Always                    |Any positive integer                      |ID of the parent thread.|
|`reply_to`       |`integer`|Always                    |Any positive integer                      |ID of the post being replied to.|
|`name`           |`string` |If present and visible    |Any string                                |Name of the poster.|
|`tripcode`       |`string` |If present and visible    |Any string                                |Tripcode.|
|`secure_tripcode`|`string` |If present and visible    |Any string                                |Secure tripcode.|
|`capcode`        |`string` |If present and visible    |Any string                                |Capcode. May contain HTML.|
|`email`          |`string` |If present and visible    |Any string                                |E-Mail address.|
|`subject`        |`string` |If present and visible    |Any string                                |Subject of the post.|
|`comment`        |`string` |If present and visible    |Any string                                |Commentary. May contain HTML or other formatting.|
|`post_time`      |`integer`|Always                    |64-bit Unix timestamp                     |Time the post was made.|
|`post_time_milli`|`integer`|Always                    |0-999                                     |Post time milliseconds. Used with `post_time`.|
|`formatted_time` |`string` |Always                    |Any string                                |Formatted version of `post_time`.|
|`total_uploads`  |`integer`|Always                    |Any positive integer                      |Total uploads in the post.|
|`file_count`     |`integer`|Always                    |Any positive integer                      |Total files in the post.|
|`embed_count`    |`integer`|Always                    |Any positive integer                      |Total embeds in the post.|
|`op`             |`boolean`|Always                    |True or false                             |Is the post OP (first in thread).|
|`sage`           |`boolean`|Always                    |True or false                             |Is the post saged.|
|`mod_comment`    |`string` |If present and visible    |Any string                                |Comment added by staff.|
|`uploads`        |`array`  |Always                    |Zero or more [`upload`](upload.md) objects|The list of uploads attached to the post.|