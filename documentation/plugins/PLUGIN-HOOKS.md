# Nelliel Plugin API Hook Guide
Documentation for the official hooks currently in Nelliel.

## Arguments and Returnables
All hooks will provide one or more arguments when called. The first argument will always be the returnable; the returnable may contain data or it may be an unused placeholder with the value of `null`. If a function or method returns a value, it must be the same type as the returnable otherwise it will be ignored.

## Hook List
### nel-inb4-central-dispatch
Called when starting the central dispatch.
**Arguments**
|Order|Argument     |Type  |Returnable|Description|                               
|:---:|:------------|:-----|:---------|:----------|
|1    |`$returnable`|`null`|Yes       |Unused.|

### nel-in-after-central-dispatch
Called at the end of the central dispatch.
**Arguments**
|Order|Argument      |Type    |Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:----------|
|1    |`$returnable` |`null`  |Yes       |Unused.|
|2    |`$inputs`     |`array` |No        |Input data.|
|3    |`$domain`     |`object`|No        |Domain object.|

### nel-inb4-module-dispatch
Called at the beginning of the module dispatch.
**Arguments**
|Order|Argument      |Type    |Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:----------|
|1    |`$inputs`     |`array` |Yes       |Input data.|
|2    |`$domain`     |`object`|No        |Domain object.|

### nel-in-after-module-dispatch
Called at the end of the module dispatch.
**Arguments**
|Order|Argument      |Type    |Returnable|Description|                               
|:---:|:-------------|:-------|:---------|:----------|
|1    |`$inputs`     |`array` |Yes       |Input data.|
|2    |`$domain`     |`object`|No        |Domain object.|

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

### nel-get-captcha
Called when getting output for a CAPTCHA.
**Arguments**
|Order|Argument     |Type  |Returnable|Description|                               
|:---:|:------------|:-----|:---------|:----------|
|1    |`$returnable`|`null`|Yes       |Unused.|

### nel-verify-captcha
Called when verifying a CAPTCHA answer.
**Arguments**
|Order|Argument     |Type  |Returnable|Description|                               
|:---:|:------------|:-----|:---------|:----------|
|1    |`$returnable`|`null`|Yes       |Unused.|

### nel-verify-recaptcha
Called when verifying a ReCAPTCHA answer.
**Arguments**
|Order|Argument     |Type    |Returnable|Description|                               
|:---:|:------------|:-------|:---------|:----------|
|1    |`$returnable`|`null`  |Yes       |Unused.|
|2    |`$response`  |`string`|No        |The ReCAPTCHA answer.|
