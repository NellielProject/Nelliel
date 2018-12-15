## Installation
Basic installation guide. Should be enough to get things up and running.

1. Unzip the files for Nelliel and copy them to the location desired.
2. Make sure the PHP process has read, write and execute permissions on the parent directory and the `board_files` directory. On Linux this would be `chmod 755` or `chmod 775` depending on how your host is set up.
3. If using MySQL, MariaDB or PostgreSQL you need a database and a user that has CREATE, ALTER and DROP permissions for that database. For SQLite you just need the path to where you wish the database file to be stored (this must not be a web-accessible location!).
4. Go to the `configuration` directory and rename `config.php.example` to `config.php` then open the file to edit settings. Only a few of these settings need to be changed:
	- `defaultadmin` and `defaultadmin_pass` need to be set to a value. This will be used to create a basic administrative user.
	- Choose a salt for `tripcode_salt`. This will only be used for secure tripcodes (it is what makes them secure) and should be random characters.
	- Change `default_locale` if you want a language other than American English. If Nelliel can't find the appropriate language file it will default back to english.
	- Set `sqltype` to whichever type of database you are using. The current options are MYSQL, MARIADB, POSTGRESQL and SQLITE
	- There is a config section for each type of SQL database. You only need to configure the section for the one you are using; the others can be left alone.
5. Go to `imgboard.php` in a browser and give it a moment to run the install routines. If anything goes wrong it should give an error message.
6. If installation is successful you should be redirected to the default home page. From there you can log in to create boards and further configure the script. You can also go to `imgboard.php?module=login` to access the login page.
7. Once you have logged in and confirmed things are working, go back to the `config.php` and set `defaultadmin` and `defaultadmin_pass` back to empty.
