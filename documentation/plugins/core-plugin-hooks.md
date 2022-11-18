# Nelliel Plugin API Hook Guide
Documentation of official hooks in the Nelliel codebase. These hooks have three placement categories:
 - `inb4` is called before a process begins.
 - `in-during` is called while a process is executing.
 - `in-after` is called once a process has completed.

## Hook List

### nel-in-after-plugin-loaded
Added: v0.9.27

Called after the successful loading of a plugin.
 
**Arguments**

|Order|Argument    |Type    |Modifiable|Returnable|Description|                               
|:---:|:-----------|:-------|:---------|:---------|:----------|
|1    |`$plugin_id`|`string`|No        |No        |ID of the plugin that was loaded.|

### nel-in-after-all-plugins-loaded
Added: v0.9.27

Called at the end of the plugin loading stage.
 
**Arguments**

None

### nel-in-during-upload-json
Added: v0.9.30

Called when the raw data for an upload has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of upload data that will be JSON encoded.|
|2    |`$upload`  |`object`|No        |No        |The instance of `Nelliel\Content\Upload` being used.|

### nel-in-during-post-json
Added: v0.9.30

Called when the raw data for a post has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of post data that will be JSON encoded.|
|2    |`$post`    |`object`|No        |No        |The instance of `Nelliel\Content\Post` being used.|

### nel-in-during-thread-json
Added: v0.9.30

Called when the raw data for a thread has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of thread data that will be JSON encoded.|
|2    |`$thread`  |`object`|No        |No        |The instance of `Nelliel\Content\Thread` being used.|

### nel-in-during-index-json
Added: v0.9.30

Called when the raw data for an index page has been generated.
 
**Arguments**

|Order|Argument   |Type     |Modifiable|Returnable|Description|                               
|:---:|:----------|:--------|:---------|:---------|:----------|
|1    |`$raw_data`|`array`  |Yes       |Yes       |Array of index data that will be JSON encoded.|
|2    |`$board`   |`object` |No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|
|3    |`$page`    |`integer`|No        |No        |The index page being generated.|

### nel-in-during-catalog-json
Added: v0.9.30

Called when the raw data for the catalog has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of catalog data that will be JSON encoded.|
|2    |`$board`   |`object`|No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|

### nel-in-during-board-json
Added: v0.9.30

Called when the raw data for a board has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of board data that will be JSON encoded.|
|2    |`$board`   |`object`|No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|
