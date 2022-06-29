# Image Sets
Image sets contain one or more image files and a file named `set_info.ini` containing a map of keys for reference. The keys provide a relative path within the image set which allows Nelliel to dynamically construct a complete file or web path to the image.

## Creating
To create an image set:
1. Collect one or more image files in a folder. Subfolders and organization within the main folder can be done in any manner.
2. Create a `set_info.ini` file in the root folder.
3. In `set_info.ini` add an `[info]` section which will contain basic information about the image set. Required fields are `id`, `name` and `version` (the current revision).
4. After the `[info]` section one or more sections are added which will contain the file keys. The available sections and keys to map are determined by Nelliel or plugins. All sections and keys are optional. If a key is added in the file but has no corresponding file it should be set as an empty string `""`.
5. The assigned value for each key will be a relative path within the image set folder. Full URLs or files outside of the folder should never be used.

## Installation
To install a custom image set:
1. Move the image set folder into the `assets/image_sets/custom` directory.
2. Go to the `Image Sets` control panel and check the list for the new set.
3. Click on the install link and any necessary set up will be performed.
4. Update other control panels or configurations in places you wish to use the new image set.

## Keys defined by Nelliel core
`[ui]`
 - `status_sticky`
 - `status_locked`
 - `status_cyclic`
 
`[filetype]`
 - Any formats defined in the Filetypes control panel.