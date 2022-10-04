# Dispatch

A guide to the dispatch system.

## What is it
Dispatch handles all incoming requests and directs them to the appropriate modules.

### Dispatch Spec
With a few exceptions all access is done through URL routing. `Router.php` processes URLs and directs to the appropriate module within `Dispatch`. From there control is handed to one or more functions.

The struture of route URLs can vary but will always contain a domain which as the first URI.


### Examples
Full query: `route=/site/account/private-messages`

In this example the `account` module is accessed in the `site` domain. Within the account module the call is directed to the `private-messages` section.
 


