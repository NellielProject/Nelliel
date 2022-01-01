# SmallPHPGettext

A small standalone library to provide gettext functions in PHP apps without relying on the gettext extension.

Requires PHP 7.2+

## Installation
Include `autoload.php` in your project. That's it.

## Usage
### Setup
```
<?php
// Create a new instance
$gettext = new SmallPHPGettext();

// Set your project values for the instance
$gettext->textdomain('project_domain');
$gettext->bindtextdomain('project_domain', 'project_directory/locale');
$gettext->defaultCategory(LC_MESSAGES);
$gettext->language('en_US');

// Optionally enable the gettext functions found in src/gettext_functions.php
$gettext->registerFunctions();
```

## Loading translations
Once initialized, a translation can be added from either a .po file or a previously generated array. Both methods require a valid category and a domain.

```
// Load translation from a file. This assumes standard structure of locale/category/domain.po
$gettext->loadTranslation('project_domain');

// Load from a specific file
$gettext->loadTranslationFromFile('translation.po', 'project_domain');

// Load from an array produced by SmallPHPGettext
$gettext->loadTranslationFromArray($translation_array, 'project_domain');
```

### Translating
The translation method names and parameters correspond with the standard gettext functions. 

```
// Get a singular translation
$gettext->gettext('message'); // Using defaults
$gettext->pgettext('context', 'message'); // For a specific context
$gettext->dgettext('domain', 'message'); // For a specific domain
$gettext->dcgettext('domain', 'message', LC_MESSAGES); // For a specific category and domain

// Get a plural translation
$gettext->ngettext('message', 2); // Using defaults
$gettext->npgettext('context', 'message', 2); // For a specific contex
$gettext->dngettext('domain', 'message', 2); // For a specific domain
$gettext->dcngettext('domain', 'message', 2, LC_MESSAGES); // For a specific category and domain
```

If the functions in `src/gettext_functions.php` have been loaded with the `registerFunctions()` method, those will be globally available as aliases to the class translation methods.

## Important Notes
The SmallPHPGettext class is self-contained and all functions will only affect the instance they are called on. They will not affect other instances, system settings or interact with the gettext extension.

The language setting is only used for file path resolution. The value can include a codeset (e.g. 'en_US.UTF-8') if needed for the directory name, but will not be used for anything else.