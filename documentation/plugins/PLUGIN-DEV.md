# Nelliel Plugin API Basic Documentation

Documentation on the basic functioning and usage of the plugin API.

**This is currently an incomplete and changing standard.**

## Latest Version
0 (in development)

## API Access
API methods can be accessed from an instance of `\Nelliel\API\Plugin\PluginAPI` or from the function `nel_plugins()`.
 
## Plugin Loading
Near the beginning of Nelliel's execution a loading phase will occur for plugins. At this time Nelliel will scan the plugins directory for `nelliel-plugin.ini` files. Each file will be loaded, then the plugin initialization will proceed:
1. The initializer file specified will be loaded and executed. This file must include one call to `registerPlugin` or else the plugin will be ignored.
2. The call to `registerPlugin` must pass the initializer file path and the base directory for the plugin. The simplest way to accomplish this is `registerPlugin(__FILE__, __DIR__)`.
3. `registerPlugin` will return an id if registration was successful or false if something went wrong. This id is required when registering or unregistering functions and methods.
4. Assuming successful registration of the plugin, the initializer may then complete whatever other functions it needs.

During the plugin loading phase plugins may: register themselves, add or remove their own hooks and do internal preparation. Plugins must not call hooks or cause effects outside of themselves until the loading phase for all plugins is complete.

## Hooks
The core of Nelliel's plugin API is hooks. Hooks may occur in any part of the Nelliel code. When execution reaches a hook, it will check for any functions or methods registered to it and call those before continuing. Plugins may also define their own hooks for other plugins to utilize.

In order to avoid naming collisions, hooks set by Nelliel are prepended by `nel-`. Hooks defined by plugins must be prepended by their own unique prefix.

## Hook Arguments and Return Values
All hooks will provide one or more arguments when called. The first argument is always the return value which may be modified before being returned. If the first argument is `null`, then no return is required.

## Hook Creation
Both Nelliel and plugins can define hooks. This is done by calling the method `processHook`. Three parameters can be passed:
1. `$hook_name` - [Required] A string identifying the hook.
2. `$args` - [Required] An array of variables or parameters to be passed on to any registered functions or methods. This can be an empty array.
3. `$returnable` - [Optional] An optional value that should be returned, possibly with modifications.

## Function Registration
To add a function to a hook, call the method `addFunction`. Four parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$function_name` - [Required] The name of the function being registered. This must include the namespace path.
3. `$plugin_id` - [Required] The ID of the plugin.
4. `$priority` - [Optional] Sets the priority of execution. Defaults to 10.

To remove a function from a hook, call the method `removeFunction`. Three parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$function_name` - [Required] The name of the function being registered. This must include the namespace path.
3. `$plugin_id` - [Required] The ID of the plugin.

## Class Method Registration
To add a class method to a hook, call the method `addMethod`. Five parameters can be passed during registration:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$class` - [Required] An instance of the class containing the method.
3. `$method_name` - [Required] The name of the method.
4. `$plugin_id` - [Required] The ID of the plugin.
5. `$priority` - [Optional] An optional parameter setting the priority of execution. Defaults to 10.

To remove a class method from a hook, call the method `removeMethod`. Four parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$class` - [Required] An instance of the class containing the method.
3. `$method_name` - [Required] The name of the method.
4. `$plugin_id` - [Required] The ID of the plugin.
