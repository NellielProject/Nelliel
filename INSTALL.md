## Installation
Basic installation guide. Should be enough to get things up and running.

1. Unzip the files for Nelliel and copy them to the location desired.
2. Make sure the PHP process has read, write and execute permissions on the main directory and the `nelliel_core` directory. On Linux this would normally be `chmod 755` or `chmod 775` depending on how your host is set up.
3. If using MySQL, MariaDB or PostgreSQL you need a database and a user that has CREATE, ALTER and DROP permissions for that database.  
For SQLite you just need the path to where you wish the database file to be stored. **This must not be a web-accessible location!**
4. Go to the `nelliel_core/configuration` directory and rename `config.php.example` to `config.php` then open the file to edit settings. Only a few of these settings are required to be changed:
	- `super_admin` and `super_admin_pass` need to be set to a value. This will be used temporarily to create a site owner account.
	- Change `default_locale` if you want a language other than American English. If Nelliel can't find the language file for `default_locale` it will fall back to U.S. English.
	- Set `sqltype` to whichever type of database you are using. The current options are MYSQL, MARIADB, POSTGRESQL and SQLITE
	- There is a configuration section for each type of SQL database. You only need to configure for the type of database you are using; the others can be left alone.
5. Go to `imgboard.php?install` in a browser and give it a moment to run the install routines. If anything goes wrong it should give a relevant error message.
6. If installation is successful you should receive a success message and a link to default home page. From there you can log in to create boards and further configure the script. You can also go to `imgboard.php?module=login` to access the login page.
7. Once you have logged in and confirmed things are working, go back to the `config.php` and set `super_admin` and `super_admin_pass` back to empty.
