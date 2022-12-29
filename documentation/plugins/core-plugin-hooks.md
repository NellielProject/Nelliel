# Nelliel Plugin API Hook Guide
Documentation of official hooks in the Nelliel codebase. These hooks have three placement categories:
 - `inb4` is called before a process begins.
 - `in-during` is called while a process is executing.
 - `in-after` is called after a process has completed.

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

### nel-in-after-upload-json
Added: v0.9.30

Called when the raw data for an upload has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of upload data that will be JSON encoded.|
|2    |`$upload`  |`object`|No        |No        |The instance of `Nelliel\Content\Upload` being used.|

### nel-in-after-post-json
Added: v0.9.30

Called when the raw data for a post has been generated for the JSON API.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of post data that will be JSON encoded.|
|2    |`$post`    |`object`|No        |No        |The instance of `Nelliel\Content\Post` being used.|

### nel-in-after-thread-json
Added: v0.9.30

Called when the raw data for a thread has been generated for the JSON API.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of thread data that will be JSON encoded.|
|2    |`$thread`  |`object`|No        |No        |The instance of `Nelliel\Content\Thread` being used.|

### nel-in-after-index-json
Added: v0.9.30

Called when the raw data for an index page has been generated for the JSON API.
 
**Arguments**

|Order|Argument   |Type     |Modifiable|Returnable|Description|                               
|:---:|:----------|:--------|:---------|:---------|:----------|
|1    |`$raw_data`|`array`  |Yes       |Yes       |Array of index data that will be JSON encoded.|
|2    |`$board`   |`object` |No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|
|3    |`$page`    |`integer`|No        |No        |The index page being generated.|

### nel-in-after-catalog-json
Added: v0.9.30

Called when the raw data for the catalog has been generated for the JSON API.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of catalog data that will be JSON encoded.|
|2    |`$board`   |`object`|No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|

### nel-in-after-board-json
Added: v0.9.30

Called when the raw data for a board has been generated for the JSON API.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of board data that will be JSON encoded.|
|2    |`$board`   |`object`|No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|

### nel-in-after-info-json
Added: v0.9.30

Called when the raw data for JSON API info has been generated.
 
**Arguments**

|Order|Argument   |Type    |Modifiable|Returnable|Description|                               
|:---:|:----------|:-------|:---------|:---------|:----------|
|1    |`$raw_data`|`array` |Yes       |Yes       |Array of API info data that will be JSON encoded.|

### nel-in-after-regen-site-pages
Added: v0.9.30

Called when regenerating site pages.
 
**Arguments**

|Order|Argument      |Type    |Modifiable|Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:---------|:----------|
|1    |`$site_domain`|`object`|No        |No        |Instance of `Nelliel\Domains\DomainSite`.|

### nel-in-after-regen-overboard
Added: v0.9.30

Called when regenerating the overboards.
 
**Arguments**

|Order|Argument      |Type    |Modifiable|Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:---------|:----------|
|1    |`$site_domain`|`object`|No        |No        |Instance of `Nelliel\Domains\DomainSite`.|

### nel-in-after-regen-board-pages
Added: v0.9.30

Called when regenerating board pages.
 
**Arguments**

|Order|Argument       |Type    |Modifiable|Returnable|Description|                               
|:---:|:--------------|:-------|:---------|:---------|:----------|
|1    |`$board_domain`|`object`|No        |No        |The instance of `Nelliel\Domains\DomainBoard` being used.|

### nel-in-before-markup-blocks
Added: v0.9.30

Called before parsing block markup.
 
**Arguments**

|Order|Argument      |Type    |Modifiable|Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:---------|:----------|
|1    |`$markup_data`|`array` |Yes       |Yes       |Data for the markups that will be used.|
|2    |`$text`       |`string`|No        |No        |String of unmodified text.|

### nel-in-before-markup-lines
Added: v0.9.30

Called before parsing line markup.
 
**Arguments**

|Order|Argument      |Type   |Modifiable|Returnable|Description|                               
|:---:|:-------------|:------|:---------|:---------|:----------|
|1    |`$markup_data`|`array`|Yes       |Yes       |Data for the markups that will be used.|
|2    |`$lines`      |`array`|No        |No        |Array of text split into lines.|

### nel-in-before-markup-simple
Added: v0.9.30

Called before parsing simple markup.
 
**Arguments**

|Order|Argument      |Type    |Modifiable|Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:---------|:----------|
|1    |`$markup_data`|`string`|Yes       |Yes       |Data for the markups that will be used.|
|2    |`$text`       |`string`|No        |No        |String of unmodified text.|

### nel-in-before-markup-loops
Added: v0.9.30

Called before parsing loop markup.
 
**Arguments**

|Order|Argument      |Type    |Modifiable|Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:---------|:----------|
|1    |`$markup_data`|`string`|Yes       |Yes       |Data for the markups that will be used.|
|2    |`$text`       |`string`|No        |No        |String of unmodified text.|

### nel-in-before-captcha-verify
Added: v0.9.30

Called before verifying the native CAPTCHA.
 
**Arguments**

|Order|Argument           |Type     |Modifiable|Returnable|Description|                               
|:---:|:------------------|:--------|:---------|:---------|:----------|
|1    |`$failed`          |`boolean`|Yes       |Yes       |Boolean indicating if a CAPTCHA verification has failed.|
|2    |`$domain`          |`string` |No        |No        |Instance of `Nelliel\Domains\Domain` the CAPTCHA was submitted through.|
|3    |`$captcha_instance`|`object` |No        |No        |The active instance of `\Nelliel\AntiSpam\CAPTCHA`.|
|4    |`$key`             |`string` |No        |No        |CAPTCHA key.|
|5    |`$answer`          |`string` |No        |No        |CAPTCHA answer.|
