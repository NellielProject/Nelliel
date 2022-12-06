# Board
Contains a representation of a board.

## Structure

|Key                 |Type     |Appears                          |Possible Values     |Description|                               
|:-------------------|:--------|:--------------------------------|:-------------------|:----------|
|`board_uri`         |`string` |Always                           |Any string          |Public URI.|
|`name`              |`string` |If board name is displayed       |Any string          |Display name of the board.|
|`description`       |`string` |If board description is displayed|Any string          |Board decription.|
|`safety_level`      |`string` |Always                           |Any string          |The safety level.|
|`source_directory`  |`string` |Always                           |Any string          |Directory containing uploaded files.|
|`preview_directory` |`string` |Always                           |Any string          |Directory containing preview files.|
|`page_directory`    |`string` |Always                           |Any string          |Directory containing threads.|
|`archive_directory` |`string` |Always                           |Any string          |Directory containing archived content.|
|`threads_per_page`  |`integer`|Always                           |Any positive integer|Number of threads displayed per index page.|
|`cooldowns`         |`object` |Always                           |Object              |List of cooldowns and their values in seconds.|
|`content_disclaimer`|`string` |Always                           |Any string          |Board-wide content disclaimer added to posts.|
|`footer_text`       |`string` |Always                           |Any string          |Text added to the footer of board pages.|
|`styles`            |`array`  |Always                           |Array of strings    |List of enabled styles.|
|`new_post_fields`   |`object` |Always                           |Object              |List of objects representing the enabled new post fields and related info for each.|
|`forced_anonymous`  |`boolean`|Always                           |True or false       |Is forced anonymous mode active.|
|`allow_no_markup`   |`array`  |Always                           |True or false       |Can posts have markup disabled.|
|`poster_ids`        |`array`  |Always                           |True or false       |Are poster IDs displayed.|
|`op_uploads`        |`object` |Always                           |Object              |List of settings for OP uploads.|
|`reply_uploads`     |`object` |Always                           |Object              |List of settings for reply uploads.|
|`enable_spoilers`   |`boolean`|Always                           |True or false       |Are spoilers enabled.|
|`max_filesize`      |`integer`|Always                           |Any positive integer|Maximum file size of uploads in bytes.|