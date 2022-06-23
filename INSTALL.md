## Installation
Basic installation guide. Please note that installing and running a modern imageboard requires basic knowledge of web hosting and use of a terminal or shell. The install process is kept as short and universal as possible but may need modifications for your environment.

1. Unarchive the files for Nelliel to the desired location. This must be somewhere outside of the web-accessible areas but is otherwise left to your discretion.
2. Make sure the PHP process has read, write and execute permissions on the `public` directory, `public/assets` directory and `core` directory.
3. Run `composer install --no-dev` in the main directory.
4. Set the `public` directory as web root for the domain or subdomain you are using.
5. (skip if step 4 was successful) In rare cases it will not be possible to use the `public` directory. If a different location is necessary:
 - Move all files in the `public` directory to the web-accessible location.
 - Update the `$core_path` variable in `nelliel_base.php` so that it points to the main directory (where `composer.json` is).
 - Make sure the PHP process has read, write and execute permissions on this directory and the `assets` directory.
6. Database preparation:  
 - For MySQL, MariaDB or PostgreSQL you need a database and a user that has CREATE, ALTER and DROP permissions for that database.  
 - For SQLite the database file will be created inside the `core` directory by default and requires no other setup.
7. Go to the `configuration` directory and rename `config.php.example` to `config.php` then open the file to edit settings. Only a few of these settings are required to be changed for installation:  
 - Enter a value for `install_key`. This will be used for the install process.
 - Set `sqltype` to whichever type of database you are using. The current options are MYSQL, MARIADB, POSTGRESQL and SQLITE  
 - There is a configuration section for each type of database. You only need to configure for the type of database you are using.
8. Navigate to `imgboard.php?install` in a browser and give it a moment to run the install routines. If anything goes wrong it should give a relevant error message.
9. At the end of the install process, you will receive one of two messages:  
 - It will ask you to create a site owner account. This account will have all permissions and cannot be deleted.
 - A message confirming a site owner account already exists and a link to the login page. You can also go to `imgboard.php?route=/_site_/account/login` if the link does not work for some reason.