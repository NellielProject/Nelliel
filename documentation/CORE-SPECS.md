# Nelliel Core Guide

A guide to the specs and design of the core.

## Dispatch
Dispatch handles all incoming requests and directs them to the appropriate modules.

### Dispatch Spec
Several parameters can be sent:

 - `module` is the name of the module to use.
 - `section` indicates what section within a module to use.
 - `subsection` is a specific subsection within the given section.
 - `actions` are the actions the module should execute. This can be a single action passed as a string or multiple actions passed in an array. For multiple actions they will be executed in the order they are received.

### Examples
Full query: `module=admin&section=threads&actions[0]=delete&actions[1]=ban&content_id=0_1_2`

In this example the `admin` module would go to the section `threads` and the indicated content would be erased then ban the IP that posted it.
 


