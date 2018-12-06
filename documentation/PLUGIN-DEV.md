# Nelliel Plugin API Basic Documentation

Documentation on the basic functioning and usage of the plugin API.

**This is currently an incomplete and changing standard with minimal hooks implemented.**

## API Access
 API methods can be accessed from an instance of `\Nelliel\API\Plugin\PluginAPI` (preferred) or from the function `nel_plugins()`.
 
## Plugin Loading
On start, Nelliel will scan the plugins directory for `nelliel-plugin.ini` files. Each file will be loaded, then the plugin initialization will proceed:
1. The initializer file specified will be loaded and executed. This file must include one call to `registerPlugin` or else the plugin will be ignored.
2. The call to `registerPlugin` must pass the initializer file path and the base directory for the plugin. The simplest way to accomplish this is `registerPlugin(__FILE__, __DIR__)`.
3. `registerPlugin` will return an id if registration was successful or false if something went wrong. This id is required when registering or unregistering functions and methods.
4. Assuming successful registration of the plugin, the initializer may then complete whatever other functions it needs.

## Hooks
The core of Nelliel's plugin API is hooks. These may be set at almost any part of Nelliel's code or in the code of plugins. When the code execution reaches a hook, it will check for any functions or methods registered to it and execute those before continuing.

In order to avoid naming collisions, hooks set by Nelliel will be prepended by `nel-`. Hooks set by plugins must be prepended by a unique identifier such as the plugin name.

## Hook Creation
Both Nelliel and plugins can define hooks. This is done by using `processHook`. 3 parameters can be passed:
1. `$hook_name` - [Required] A string identifying the hook.
2. `$args` - [Required] An array of variables or parameters to be passed on to any registered functions or methods.
3. `$returnable` - [Optional] An optional variable that should be returned. If not passed, will have the value null.
  
If `$returnable` is given, all plugin methods and functions registered to the hook should return the variable as-is or with a modified value. If it is not returned or the type has been changed, the previous value will be passed on instead.

## Function Registration
To add a function to a hook, call `addFunction`. Four parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$function_name` - [Required] The name of the function being registered. This must include the namespace path.
3. `$plugin_id` - [Required] The ID of the plugin.
4. `$priority` - [Optional] Sets the priority of execution. Defaults to 10.

To remove a function from a hook, call `removeFunction`. Three parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$function_name` - [Required] The name of the function being registered. This must include the namespace path.
3. `$plugin_id` - [Required] The ID of the plugin.

## Class Method Registration
To add a class method to a hook, call `addMethod`. Five parameters can be passed during registration:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$class` - [Required] An instance of the class containing the method.
3. `$method_name` - [Required] The name of the method.
4. `$plugin_id` - [Required] The ID of the plugin.
5. `$priority` - [Optional] An optional parameter setting the priority of execution. Defaults to 10.

To remove a class method from a hook, call `removeMethod`. Four parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$class` - [Required] An instance of the class containing the method.
3. `$method_name` - [Required] The name of the method.
4. `$plugin_id` - [Required] The ID of the plugin.