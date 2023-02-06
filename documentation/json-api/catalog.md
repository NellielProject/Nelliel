# Catalog
Contains a representation of a catalog page.

## Structure

|Key      |Type     |Appears|Possible Values                           |Description|                               
|:--------|:--------|:------|:-----------------------------------------|:----------|
|`page`   |`integer`|Always |Any positive integer                      |The current catalog page.|
|`threads`|`array`  |Always |Zero or more [`thread`](thread.md) objects|A list of threads on the current page.|

