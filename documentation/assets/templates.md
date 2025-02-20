# Templates
Templates contain an info file named `template_info.ini` and a structured set of template files.

## Creating
To create a new template:
1. Duplicate an existing template folder.
2. Modify the `template_info.ini` in the root folder.
3. Alter the template files as desired.

NOTE: It is possible to build a template from scratch but is not recommended.

## Info Fields
Fields available under the [info] section:

|Field        |Required|Description|                             
|:-----------:|:-------|:----------|
|`id`         |Yes     |A string ID used to uniqely identify the style.|
|`name`       |Yes     |The display name.|
|`description`|No      |A description of the style.|
|`version`    |Yes     |The current version.|
|`output_type`|Yes     |The type of output. Must be a type supported by Nelliel or a plugin.|

## Installation
To install a template:
1. Move the template folder into the `templates/custom` directory.
2. Go to the `Templates` control panel and check the list for the new template.
3. Click on the install link and any necessary set up will be performed.
4. Update any relevant settings.
5. Regenerate render cache and static pages where relevant.
