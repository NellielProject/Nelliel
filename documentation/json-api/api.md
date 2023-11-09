# Nelliel JSON API
Nelliel's JSON API provides a read-only representation of resources such as threads and indexes. This documentation covers the base API outputs and should apply to all Nelliel installs. Plugins or other modifications may alter the output.

This API is **not** directly compatible with 4chan or other imageboard JSON APIs.

## Endpoints
Note: All endpoints will have an [`api_info`](info.md) entry.

### [Boards](boards.md)
**Location:** http(s)://`:site_url`/boards.json

### [Catalog](catalog.md)
**Location:** http(s)://`:site_url`/`:board_uri`/catalog.json

### [Index](index.md)
**Location:** http(s)://`:site_url`/`:board_uri`/`:index page`.json

### [Threadlist](threadlist.md)
**Location:** http(s)://`:site_url`/`:board_uri`/threads.json

### [Thread](thread.md)
**Location:** http(s)://`:site_url`/`:board_uri`/`:thread_directory`/`:thread_id`/`:thread_id`.json