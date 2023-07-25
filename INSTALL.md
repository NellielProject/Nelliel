## Installation
Basic installation guide. Installation requires basic knowledge of web hosting and use of a terminal or shell. The install process is kept as short and universal as possible but may need modifications for your environment.

1. Unarchive the files for Nelliel to the desired location. This must be somewhere outside of the web-accessible areas but is otherwise left to your discretion.
2. Make sure the PHP process has read, write and execute permissions on the following directories: `public`, `public/assets`, `configuration` and `core`.
3. Run `composer install --no-dev` in the main directory.
4. Set the `public` directory as web root for the domain or subdomain you are using. If this is not possible, go to step 4a; otherwise continue to step 5.
4a. In rare cases it will not be possible to use the `public` directory. If a different location is necessary:
 - Move all files in the `public` directory to the web-accessible location.
 - Update the `$core_path` variable in `nelliel_base.php` so that it points to the main directory (where `composer.json` is).
 - Make sure the PHP process has read, write and execute permissions on this directory and the `assets` directory.
5. Database preparation:  
 - For MySQL, MariaDB or PostgreSQL you need a database and a user that has CREATE, ALTER and DROP permissions for that database.  
 - For SQLite the database file will be created inside the `core` directory by default and requires no other setup.
6. Open the file `configuration/install_key.php` and enter an installer verification key.
6a. (Optional) If you wish to manually edit any of the configurations instead of using the installer, do so now.
7. Navigate to `imgboard.php?install` in a browser. It will request the install key to be entered.
8. Follow instructions given by the installer.