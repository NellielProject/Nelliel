# Styles
Styles contain a CSS file and an info file named `style_info.ini`.

## Creating
To create a style:
1. Create a folder for the style.
2. Create a CSS file in the root style folder.
3. Create `style_info.ini` in the root style folder.
4. In `style_info.ini` add an `[info]` section which will contain basic information about the style. Required fields are `id`, `name`, `version` and `main_file`.

## Info Fields
|Field        |Required|Description|                             
|:-----------:|:-------|:----------|
|`id`         |Yes     |A string ID used to uniqely identify the style.|
|`name`       |Yes     |The display name.|
|`description`|Yes     |A description of the style.|
|`version`    |Yes     |The current version.|
|`main_file`  |Yes     |The main CSS file.|

## Installation
To install a style:
1. Move the style folder into the `assets/styles/custom` directory.
2. Go to the `Styles` control panel and check the list for the new style.
3. Click on the install link and any necessary set up will be performed.
4. Update any relevant settings.
