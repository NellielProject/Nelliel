# Nelliel Plugin API Hook Guide
Documentation for the official hooks currently in Nelliel.

## Hook List
### nel-inb4-central-dispatch
Called when starting the central dispatch.  
**Arguments**

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$return` |`null`  |Yes       |Unused.|

Return type: void  

### nel-in-after-central-dispatch
Called at the end of the central dispatch.  
**Arguments**

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$inputs` |`array` |Yes       |Input action data.|
|2    |`$domain` |`object`|No        |Domain object.|

Return type: void  

### nel-inb4-module-dispatch
Called at the beginning of the module dispatch.  
**Arguments**

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$inputs` |`array` |Yes       |Input action data.|
|2    |`$domain` |`object`|No        |Domain object.|

### nel-in-after-module-dispatch
Called at the end of the module dispatch.  
**Arguments**

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$inputs` |`array` |Yes       |Input action data.|
|2    |`$domain` |`object`|No        |Domain object.|

### nel-json-prepare-board-list
Called when board list data has been prepared for the JSON API.  
**Arguments**

|Order|Argument           |Type   |Returnable|Description|                               
|:---:|:------------------|:------|:---------|:----------|
|1    |`$board_list_array`|`array`|Yes       |Processed board list data that will be JSON-encoded.|
|2    |`$data`            |`array`|No        |Raw board list data.|

### nel-json-prepare-board
Called when board data has been prepared for the JSON API.  
**Arguments**

|Order|Argument      |Type   |Returnable|Description|                               
|:---:|:-------------|:------|:---------|:----------|
|1    |`$board_array`|`array`|Yes       |Processed board data that will be JSON-encoded.|
|2    |`$data`       |`array`|No        |Raw board data.|

### nel-json-prepare-index
Called when index data has been prepared for the JSON API.  
**Arguments**

|Order|Argument      |Type   |Returnable|Description|                               
|:---:|:-------------|:------|:---------|:----------|
|1    |`$index_array`|`array`|Yes       |Processed index data that will be JSON-encoded.|
|2    |`$data`       |`array`|No        |Raw index data.|

### nel-json-prepare-thread
Called when thread data has been prepared for the JSON API.  
**Arguments**

|Order|Argument       |Type   |Returnable|Description|                               
|:---:|:--------------|:------|:---------|:----------|
|1    |`$thread_array`|`array`|Yes       |Processed thread data that will be JSON-encoded.|
|2    |`$data`        |`array`|No        |Raw thread data.|

### nel-json-prepare-post
Called when post data has been prepared for the JSON API.  
**Arguments**

|Order|Argument     |Type   |Returnable|Description|                               
|:---:|:------------|:------|:---------|:----------|
|1    |`$post_array`|`array`|Yes       |Processed post data that will be JSON-encoded.|
|2    |`$data`      |`array`|No        |Raw post data.|

### nel-json-prepare-content
Called when content data has been prepared for the JSON API.  
**Arguments**  

|Order|Argument        |Type   |Returnable|Description|                               
|:---:|:---------------|:------|:---------|:----------|
|1    |`$content_array`|`array`|Yes       |Processed content data that will be JSON-encoded.|
|2    |`$data`         |`array`|No        |Raw content data.|

### nel-generate-captcha
Called when getting output for a CAPTCHA.  
**Arguments**  

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$return` |`null`  |Yes       |Unused.|

### nel-verify-captcha
Called when verifying a CAPTCHA answer.  
**Arguments**

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$return` |`null`|Yes       |Unused.|

### nel-verify-recaptcha
Called when verifying a ReCAPTCHA answer.  
**Arguments**

|Order|Argument   |Type    |Returnable|Description|                               
|:---:|:----------|:-------|:---------|:----------|
|1    |`$return`  |`null`  |Yes       |Unused.|
|2    |`$response`|`string`|No        |The ReCAPTCHA answer.|

### nel-post-data-processed
Called after post data has been processed.
**Arguments**

|Order|Argument  |Type    |Returnable|Description|                               
|:---:|:---------|:-------|:---------|:----------|
|1    |`$post`   |`object`|Yes       |The post object.|
|2    |`$domain` |`object`|No        |Domain object for the board the post is being submitted to.|

### nel-post-tripcodes
Called after tripcodes have been processed.
**Arguments**

|Order|Argument      |Type    |Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:----------|
|1    |`$post`       |`object`|Yes       |The post object.|
|2    |`$domain`     |`object`|No        |Domain object for the board the post is being submitted to.|
|3    |`$name_pieces`|`array` |No        |Original name field split into pieces.|

### nel-post-files-processed
Called after uploaded files are processed.
**Arguments**

|Order|Argument          |Type    |Returnable|Description|                               
|:---:|:-----------------|:-------|:---------|:----------|
|1    |`$processed_files`|`array` |Yes       |Array of file objects after processing.|
|2    |`$domain`         |`object`|No        |Domain object for the board the files are being submitted to.|
|3    |`$uploaded_files` |`array` |No        |Array of file objects before processing.|

### nel-post-check-file-errors
Called when an uploaded file is checked for errors.
**Arguments**

|Order|Argument     |Type    |Returnable|Description|                               
|:---:|:------------|:-------|:---------|:----------|
|1    |`$return`    |`null`  |Yes       |Unused.|
|2    |`$domain`    |`object`|No        |Domain object for the board the files are being submitted to.|
|3    |`$error_data`|`array` |No        |Array of data sent to error handler.|

### nel-derp-happened
Called when an error occurs.
**Arguments**

|Order|Argument        |Type     |Returnable|Description|                               
|:---:|:---------------|:--------|:---------|:----------|
|1    |`$diagnostics`  |`array`  |Yes       |Array of diagnostic data.|
|2    |`$error_id`     |`integer`|No        |ID of the error.|
|3    |`$error_message`|`string` |No        |Error message.|
|4    |`$error_data`   |`array`  |No        |Array of data sent to error handler.|