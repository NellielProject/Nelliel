# Threadlist
Contains a list of threads on the board, divided into pages. Thread data in this list is minimal; Check [`index`](index.md) listing for full thread data.

## Structure

|Key                |Type       |Appears|Possible Values                                        |Description|                               
|:------------------|:----------|:------|:------------------------------------------------------|:----------|
|`page`             |`integer`  |Always |Any positive integer                                   |The page number|
|`threads`          |`array`    |Always |Zero or more limited data [`thread`](thread.md) objects|The list of threads.|

