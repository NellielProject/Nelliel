# Nelliel Plugin API Basic Documentation

Documentation on the functions of the plugin API. For instructions on using plugins see [using-plugins.md](using-plugins.md).

## Current API Version
1

## API Access
API methods can be accessed from an instance of `\Nelliel\API\Plugin\PluginAPI` or from the function `nel_plugins()` which provides a standard instance.

## Plugin Loading
Near the beginning of Nelliel's execution the loading phase will occur for plugins. In this phase all entries of installed and enabled plugins are loaded from the database. This set of plugins is then cycled through one at a time:
1. Version checks are done for each plugin.
2. Dependency checks. This check is for hard dependencies; soft dependencies must be handled by the plugin.
3. Ensure the initializer file is present.

Once all checks are completed successfully the initializer file will be loaded and executed using `include_once`. The initializer file must complete any necessary internal setup at this time.

During the loading phase plugins may register functions or methods, add or remove their own hooks and do internal preparation. Plugins should avoid causing effects outside of themselves until the loading phase for all plugins is complete.

## Hooks
Nelliel's plugin API uses hooks as its interface. Hooks may occur in any part of the Nelliel code or in the code of plugins. When execution reaches a hook, it will check for any functions or methods registered to it and call those before continuing. Plugins may also define their own hooks for other plugins to utilize.

In order to avoid naming collisions, hooks set by Nelliel are prepended by `nel-`. Hooks defined by plugins must be prepended by their own unique prefix.

The list of core hooks can be found in [core-plugin-hooks.md](core-plugin-hooks.md).

## Hook Arguments and Return Values
Hooks will provide zero or more arguments when called.

If a return value is expected the first argument will be the returnable value. This variable can be modified before being returned. If the variable's type is changed or it is not returned then the last valid value will be used. All other arguments are either solely informational values or passed by reference to allow direct modification.

## Using Hooks
A hook will be processed at any time code execution reaches the `processHook` call. Functions and methods can be added or removed at any time before or after a hook has been called but cannot be changed while the hook is being processed.

All registrations on a hook will be sorted by priority when it is called. If the priority of two or more are the same they will be sorted by the order they were added. A function or method can be registered to a hook as many times as desired.

## Hook Creation
Both Nelliel and plugins can define hooks. This is done by calling the method `processHook`. Three parameters can be passed:
1. `$hook_name` - [Required] A string identifying the hook.
2. `$args` - [Required] An array of variables or parameters to be passed on to any registered functions or methods. This can be an empty array.
3. `$returnable` - [Optional] An optional default for the return value. If not provided then no return value will be used.

## Function Registration
To add a function to a hook, call the method `addFunction`. Four parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$function_name` - [Required] The name of the function being registered. This must include the full namespace path.
3. `$plugin_id` - [Required] The ID of the plugin.
4. `$priority` - [Optional] Sets the priority of execution. Defaults to 10.

Returns `true` if successful. Otherwise returns `false`.

To remove a function from a hook, call the method `removeFunction`. Four parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$function_name` - [Required] The name of the function being registered. This must include the full namespace path.
3. `$plugin_id` - [Required] The ID of the plugin.
4. `$priority` - [Optional] Sets the priority of execution. Defaults to 10.

Returns `true` if successful. Otherwise returns `false`.

## Class Method Registration
To add a class method to a hook, call the method `addMethod`. Five parameters can be passed during registration:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$class` - [Required] An instance of the class containing the method.
3. `$method_name` - [Required] The name of the method being registered.
4. `$plugin_id` - [Required] The ID of the plugin.
5. `$priority` - [Optional] Sets the priority of execution. Defaults to 10.

Returns `true` if successful. Otherwise returns `false`.

To remove a class method from a hook, call the method `removeMethod`. Five parameters can be passed:
1. `$hook_name` - [Required] A string matching the name of a hook.
2. `$class` - [Required] An instance of the class containing the method.
3. `$method_name` - [Required] The name of the method being registered.
4. `$plugin_id` - [Required] The ID of the plugin.
5. `$priority` - [Optional] Sets the priority of execution. Defaults to 10.

Returns `true` if successful. Otherwise returns `false`.

NOTE: Registered methods must have public visibility. Protected and private methods cannot be called by the plugin system.
