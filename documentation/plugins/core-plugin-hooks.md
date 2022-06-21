# Nelliel Plugin API Hook Guide
Documentation of official hooks in the Nelliel codebase. These hooks have three placement categories:
 - `inb4` is called before a process begins.
 - `in-during` is called while a process is executing.
 - `in-after` is called once a process has completed.

## Hook List

### nel-in-after-plugin-loaded
Called after the successful loading of a plugin.
 
**Arguments**

|Order|Argument     |Type    |Modifiable|Returnable|Description|                               
|:---:|:------------|:-------|:---------|:---------|:----------|
|1    |`$plugin_id` |`string`|No        |No        |ID of the plugin that was loaded.|

### nel-in-after-all-plugins-loaded
Called at the end of the plugin loading stage.
 
**Arguments**

None
