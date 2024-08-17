# Board Defaults
Board defaults provides the default configuration when new boards are created. It also provides some global override functions for board configurations.

## Access
The control panel can be accessed via the `Board Defaults` option in the site control panel. Requires the `perm_modify_board_defaults` permission.

## Structure
 - **Lock:** If checked, the setting will be locked from further changes on all boards. Users with the `perm_override_config_lock` permission can bypass the lock.
 - **Force Update:** If checked, the value of the setting will be applied globally to all boards. This is a one-time change.
 - **Store Raw:** If checked, the value of the setting will be stored with no filtering or modification. Requires the `perm_raw_html` permission.
 - **Setting:** The name of the setting. This is the string key used within configurations and code.
 - **Value:** The value assigned to the setting.
 - **Description:** Explains the purpose of the setting and other relevant details.
