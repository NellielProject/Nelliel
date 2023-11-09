## Upgrading
Guide for upgrading Nelliel to a new minor or major version.

1. Unarchive the updated files for Nelliel and replace the older files with the newer ones. Once the files are in place the different version will be detected and all other functions will be blocked until the upgrade is done.
2. Run `composer update --no-dev` in the main directory.
3. Navigate to `imgboard.php?upgrade` in a browser and log in as site owner.
4. Once login is completed the upgrade script will run to perform migrations and any other functions required.
5. After the script finishes the upgrade is done and normal functions will automatically resume.

NOTE: Users may need to manually do a full refresh on some pages to reload CSS and Javascript files.