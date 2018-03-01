;<?php die('NOPE.AVI');/*
;
; This holds the very basic configuration needed for Nelliel to function and setup.
; Other settings and board-specific config can be found in the management section.

[General]

; If on a new install or no users are found, Nelliel will use DEFAULTADMIN and DEFAULTADMIN_PASS to create an admin account.
; Once created the default admin can be used for the rest of setup, managing staff, etc.
; Once the default admin account is created, make sure to set both of these back to ""

defaultadmin = ""
defaultadmin_pass = ""

; Salt used for secure tripcodes
; Change this setting ONCE when you do initial setup. Changing it again will alter the secure tripcode output.

tripcode_salt = "sodiumz"

; Each time the script is run, this runs through the setup sequence and checks that everything is in place.
; Once initial setup and testing is finished this isn't really necessary and can be set to false.

run_setup_check = true

; Default file and directory permissions. There shouldn't be any need to change these.
; If they are changed, format must be in the proper octal format.

directory_perm = "0775"
file_perm = "0664"

[Crypt]

; The hash settings for staff logins and other higher security things
; Bcrypt - PHP default is 10; 04 is the minimum; 31 is maximum
; SHA2 - PHP default is 5000; minimum is 1000; maximum is 999999999

password_bcrypt_cost = 12
password_sha2_cost = 200000

;*/