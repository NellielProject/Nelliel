# Thread
Contains a representation of a thread.

## Structure

|Key                |Type     |Appears                      |Possible Values                       |Description|                               
|:------------------|:--------|:----------------------------|:-------------------------------------|:----------|
|`thread_id`        |`integer`|Always                       |Any positive integer                  |ID of the thread.|
|`bump_time`        |`integer`|Always                       |64-bit Unix timestamp                 |Last thread bump time.|
|`bump_time_milli`  |`integer`|Always                       |0-999                                 |Last thread bump time milliseconds. Used with `bump_time`.|
|`last_update`      |`integer`|Always                       |64-bit Unix timestamp                 |Last thread update.|
|`last_update_milli`|`integer`|Always                       |0-999                                 |Last thread update milliseconds. Used with `last_update`.|
|`post_count`       |`integer`|Always                       |Any positive integer                  |Total number of posts in the thread.|
|`bump_count`       |`integer`|Always                       |Any positive integer                  |Total number of thread bumps.|
|`total_uploads`    |`integer`|Always                       |Any positive integer                  |Total uploads in the thread.|
|`file_count`       |`integer`|Always                       |Any positive integer                  |Total files in the thread.|
|`embed_count`      |`integer`|Always                       |Any positive integer                  |Total embeds in the thread.|
|`permasage`        |`boolean`|Always                       |True or false                         |Is the thread permasaged.|
|`sticky`           |`boolean`|Always                       |True or false                         |Is the thread stickied.|
|`locked`           |`boolean`|Always                       |True or false                         |Is the thread locked.|
|`cyclic`           |`boolean`|Always                       |True or false                         |Is the thread cyclic.|
|`old`              |`boolean`|Always                       |True or false                         |Thread is marked as old.|
|`shadow`           |`boolean`|Always                       |True or false                         |Shadow of a moved thread.|
|`slug`             |`string` |Always                       |Any string                            |SEO-friendly slug.|
|`op`               |`array`  |Catalog page only            |0-1 `[post](post.md)` objects         |The first post in the thread.|
|`posts`            |`array`  |Always except on catalog page|Zero or more `[post](post.md)` objects|A list of posts in the thread.|
|`omitted_posts`    |`integer`|Index page only              |Any positive integer                  |The number of posts omitted from display.|
|`slug`             |`string` |Always                       |Any string                            |SEO-friendly slug.|

