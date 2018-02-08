<?php

//
// Because auto increment isn't really standard and everyone has to be a bit different
// Pass $int_column as SMALLINT, INTEGER or BIGINT. This is basically just to deal with Postgres
//
function nel_autoincrement_column($int_column)
{
    $auto = '';

    if (SQLTYPE === 'MYSQL')
    {
        $auto = 'AUTO_INCREMENT';
    }
    else if (SQLTYPE === 'POSTGRES')
    {
        if ($int_column === 'SMALLINT')
        {
            $int_column = 'SMALLSERIAL';
        }

        if ($int_column === 'INTEGER')
        {
            $int_column = 'SERIAL';
        }

        if ($int_column === 'BIGINT')
        {
            $int_column = 'BIGSERIAL';
        }
    }
    else if (SQLTYPE === 'SQLITE')
    {
        $auto = 'AUTOINCREMENT';
    }

    return array($int_column, $auto);
}

function nel_sql_binary_alternatives($datatype, $length)
{
    if (SQLTYPE === 'MYSQL')
    {
        if ($datatype === "BINARY")
        {
            return 'BINARY(' . $length . ')';
        }
        else if ($datatype === "VARBINARY")
        {
            return 'VARBINARY(' . $length . ')';
        }
    }
    else if (SQLTYPE === 'POSTGRES')
    {
        if ($datatype === "BINARY")
        {
            return 'BYTEA';
        }
        else if ($datatype === "VARBINARY")
        {
            return 'BYTEA';
        }
    }
    else if (SQLTYPE === 'SQLITE')
    {
        if ($datatype === "BINARY")
        {
            return 'BLOB';
        }
        else if ($datatype === "VARBINARY")
        {
            return 'BLOB';
        }
    }
}

function nel_table_options()
{
    $options = '';

    if (SQLTYPE === 'MYSQL')
    {
        $options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci';

        if (nel_check_for_innodb())
        {
            $options .= ' ENGINE = InnoDB';
        }
    }

    return $options . ';';
}

function nel_check_for_innodb()
{
    $dbh = nel_database();
    $result = $dbh->query("SHOW ENGINES");
    $list = $result->fetchAll(PDO::FETCH_ASSOC);

    foreach ($list as $entry)
    {
        if ($entry['Engine'] === 'InnoDB' && ($entry['Support'] === 'DEFAULT' || $entry['Support'] === 'YES'))
        {
            return true;
        }
    }

    return false;
}

function nel_create_table_query($schema, $table_name)
{
    $dbh = nel_database();

    if ($dbh->tableExists($table_name))
    {
        return false;
    }

    $result = $dbh->query($schema);

    if (!$result)
    {
        print_r($dbh->errorInfo());
        $dbh->tableFail($table_name);
    }

    return $result;
}

function nel_create_threads_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "thread_id"             INTEGER NOT NULL PRIMARY KEY,
        "first_post"            INTEGER NOT NULL DEFAULT 0,
        "last_post"             INTEGER NOT NULL DEFAULT 0,
        "second_last_post"      INTEGER NOT NULL DEFAULT 0,
        "last_bump_time"        BIGINT NOT NULL DEFAULT 0,
        "total_files"           INTEGER NOT NULL DEFAULT 0,
        "last_update"           BIGINT NOT NULL DEFAULT 0,
        "post_count"            INTEGER NOT NULL DEFAULT 0,
        "thread_sage"           SMALLINT NOT NULL DEFAULT 0,
        "sticky"                SMALLINT NOT NULL DEFAULT 0,
        "archive_status"        SMALLINT NOT NULL DEFAULT 0,
        "locked"                SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_create_posts_table($table_name, $threads_table)
{
    $dbh = nel_database();
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "post_number"           ' . $auto_inc[0] .
        ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "parent_thread"         INTEGER DEFAULT NULL,
        "poster_name"           VARCHAR(255) DEFAULT NULL,
        "post_password"         VARCHAR(255) DEFAULT NULL,
        "tripcode"              VARCHAR(255) DEFAULT NULL,
        "secure_tripcode"       VARCHAR(255) DEFAULT NULL,
        "email"                 VARCHAR(255) DEFAULT NULL,
        "subject"               VARCHAR(255) DEFAULT NULL,
        "comment"               TEXT,
        "ip_address"            ' .
        nel_sql_binary_alternatives('VARBINARY', '16') . ' DEFAULT NULL,
        "post_time"             BIGINT NOT NULL DEFAULT 0,
        "has_file"              SMALLINT NOT NULL DEFAULT 0,
        "file_count"            SMALLINT NOT NULL DEFAULT 0,
        "op"                    SMALLINT NOT NULL DEFAULT 0,
        "sage"                  SMALLINT NOT NULL DEFAULT 0,
        "mod_post"              VARCHAR(255) DEFAULT NULL,
        "mod_comment"           VARCHAR(255) DEFAULT NULL,
        CONSTRAINT fk_parent_thread_' . $threads_table . '_thread_id
        FOREIGN KEY (parent_thread) REFERENCES ' . $threads_table . '(thread_id) ON DELETE CASCADE
    ) ' . $options . ';';

        $result = nel_create_table_query($schema, $table_name);

        if ($result)
        {
            $dbh->query('CREATE INDEX index_parent_thread ON ' . $table_name . ' (parent_thread);');
        }

        nel_setup_stuff_done($result);
}

function nel_create_files_table($table_name, $posts_table)
{
    $dbh = nel_database();
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "parent_thread"         INTEGER NOT NULL DEFAULT 0,
        "post_ref"              INTEGER DEFAULT NULL,
        "file_order"            SMALLINT NOT NULL DEFAULT 1,
        "supertype"             VARCHAR(255) DEFAULT NULL,
        "subtype"               VARCHAR(255) DEFAULT NULL,
        "mime"                  VARCHAR(255) DEFAULT NULL,
        "filename"              VARCHAR(255) NOT NULL,
        "extension"             VARCHAR(255) DEFAULT NULL,
        "image_width"           INTEGER DEFAULT NULL,
        "image_height"          INTEGER DEFAULT NULL,
        "preview_name"          VARCHAR(255) DEFAULT NULL,
        "preview_width"         SMALLINT DEFAULT NULL,
        "preview_height"        SMALLINT DEFAULT NULL,
        "filesize"              INTEGER NOT NULL DEFAULT 0,
        "md5"                   ' .
         nel_sql_binary_alternatives('VARBINARY', '16') . ' NOT NULL,
        "sha1"                  ' .
         nel_sql_binary_alternatives('VARBINARY', '20') . ' NOT NULL,
        "sha256"                ' .
         nel_sql_binary_alternatives('VARBINARY', '32') . ' NOT NULL,
        "source"                VARCHAR(255) DEFAULT NULL,
        "license"               VARCHAR(255) DEFAULT NULL,
        "alt_text"              VARCHAR(255) DEFAULT NULL,
        "exif"                  TEXT DEFAULT NULL,
        CONSTRAINT fk_post_ref_' . $posts_table . '_post_number
        FOREIGN KEY(post_ref) REFERENCES ' . $posts_table . '(post_number) ON DELETE CASCADE
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result)
    {
        $dbh->query('CREATE INDEX index_md5 ON ' . $table_name . ' (md5);');
        $dbh->query('CREATE INDEX index_sha1 ON ' . $table_name . ' (sha1);');
        $dbh->query('CREATE INDEX index_sha256 ON ' . $table_name . ' (sha256);');
    }

    nel_setup_stuff_done($result);
}

function nel_create_board_config_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "config_name"           VARCHAR(255) NOT NULL,
        "config_type"           VARCHAR(255) DEFAULT NULL,
        "config_owner"          VARCHAR(255) NOT NULL DEFAULT \'nelliel\',
        "config_category"       VARCHAR(255) NOT NULL DEFAULT \'general\',
        "data_type"             VARCHAR(255) DEFAULT NULL,
        "setting"               VARCHAR(255) NOT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_board_config_defaults($table_name);
    }

    nel_setup_stuff_done($result);
}

function nel_create_site_config_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
        ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "config_name"           VARCHAR(255) NOT NULL,
        "config_type"           VARCHAR(255) DEFAULT NULL,
        "config_owner"          VARCHAR(255) NOT NULL DEFAULT \'nelliel\',
        "config_category"       VARCHAR(255) NOT NULL DEFAULT \'general\',
        "data_type"             VARCHAR(255) DEFAULT NULL,
        "setting"               VARCHAR(255) NOT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_site_config_defaults($table_name);
    }

    nel_setup_stuff_done($result);
}

function nel_create_user_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "user_id"               VARCHAR(255) NOT NULL UNIQUE,
        "user_title"            VARCHAR(255) DEFAULT NULL,
        "user_password"         VARCHAR(255) DEFAULT NULL,
        "user_tripcode"         VARCHAR(255) DEFAULT NULL,
        "active"                SMALLINT NOT NULL DEFAULT 0,
        "failed_logins"         SMALLINT NOT NULL DEFAULT 0,
        "last_failed_login"     BIGINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_default_admin();
    }

    nel_setup_stuff_done($result);
}

function nel_create_roles_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "role_id"               VARCHAR(255) NOT NULL,
        "role_level"            SMALLINT NOT NULL DEFAULT 0,
        "role_title"            VARCHAR(255) DEFAULT NULL,
        "capcode_text"          VARCHAR(255) DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_role_defaults();
    }

    nel_setup_stuff_done($result);
}

function nel_create_user_role_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "user_id"               VARCHAR(255) NOT NULL,
        "role_id"               VARCHAR(255) NOT NULL,
        "board"                 VARCHAR(255) NOT NULL,
        "all_boards"            SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_default_admin_role();
    }

    nel_setup_stuff_done($result);
}

function nel_create_permissions_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "role_id"               VARCHAR(255) DEFAULT NULL,
        "perm_id"               VARCHAR(255) NOT NULL,
        "perm_setting"          SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result !== false)
    {
        nel_insert_permissions_defaults();
    }

    nel_setup_stuff_done($result);
}

function nel_create_logins_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "ip_address"            ' .
         nel_sql_binary_alternatives('VARBINARY', '16') . ' NOT NULL UNIQUE,
        "failed_attempts"       INTEGER NOT NULL DEFAULT 0,
        "last_attempt"          BIGINT DEFAULT NULL
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_create_bans_table($table_name)
{
    $dbh = nel_database();
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "ban_id"                ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "board_id"              VARCHAR(255) DEFAULT NULL,
        "all_boards"            SMALLINT NOT NULL DEFAULT 0,
        "type"                  VARCHAR(255) DEFAULT NULL,
        "creator"               VARCHAR(255) DEFAULT NULL,
        "ip_address_start"      ' .
         nel_sql_binary_alternatives('VARBINARY', '16') . 'DEFAULT NULL,
        "ip_address_end"        ' .
         nel_sql_binary_alternatives('VARBINARY', '16') . 'DEFAULT NULL,
        "reason"                TEXT DEFAULT NULL,
        "length"                BIGINT NOT NULL DEFAULT 0,
        "start_time"            BIGINT NOT NULL DEFAULT 0,
        "appeal"                TEXT DEFAULT NULL,
        "appeal_response"       TEXT DEFAULT NULL,
        "appeal_status"         SMALLINT NOT NULL DEFAULT 0
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);

    if ($result)
    {
        $dbh->query('CREATE INDEX index_ip_address_start ON ' . $table_name . ' (ip_address_start);');
    }

    nel_setup_stuff_done($result);
}

function nel_create_board_data_table($table_name)
{
    $auto_inc = nel_autoincrement_column('INTEGER');
    $options = nel_table_options();
    $schema = '
    CREATE TABLE ' . $table_name . ' (
        "entry"                 ' . $auto_inc[0] .
         ' PRIMARY KEY ' . $auto_inc[1] . ' NOT NULL,
        "board_id"              VARCHAR(255) NOT NULL UNIQUE,
        "board_directory"       VARCHAR(255) NOT NULL,
        "db_prefix"             VARCHAR(255) NOT NULL,
    ) ' . $options . ';';

    $result = nel_create_table_query($schema, $table_name);
    nel_setup_stuff_done($result);
}

function nel_insert_role_defaults() // TODO: Make this specific check for each role, then re-add to login
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . ROLES_TABLE . "
    (role_id, role_level, role_title, capcode_text)
    VALUES
    ('SUPER_ADMIN', 1000, 'Site Administrator', '## Site Administrator ##'),
    ('BOARD_ADMIN', 100, 'Board Administrator', '## Board Administrator ##'),
    ('MOD', 50, 'Moderator', '## Moderator ##'),
    ('JANITOR', 10, 'Janitor', '## Janitor ##')");

    nel_setup_stuff_done($result);
}

function nel_insert_permissions_defaults()
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . PERMISSIONS_TABLE . " (role_id, perm_id, perm_setting)
                        VALUES  ('SUPER_ADMIN', 'perm_config_access', 1),
                                ('SUPER_ADMIN', 'perm_config_modify', 1),
                                ('SUPER_ADMIN', 'perm_user_access', 1),
                                ('SUPER_ADMIN', 'perm_user_add', 1),
                                ('SUPER_ADMIN', 'perm_user_modify', 1),
                                ('SUPER_ADMIN', 'perm_user_delete', 1),
                                ('SUPER_ADMIN', 'perm_user_change_pass', 1),
                                ('SUPER_ADMIN', 'perm_role_access', 1),
                                ('SUPER_ADMIN', 'perm_role_add', 1),
                                ('SUPER_ADMIN', 'perm_role_modify', 1),
                                ('SUPER_ADMIN', 'perm_role_delete', 1),
                                ('SUPER_ADMIN', 'perm_ban_access', 1),
                                ('SUPER_ADMIN', 'perm_ban_add', 1),
                                ('SUPER_ADMIN', 'perm_ban_modify', 1),
                                ('SUPER_ADMIN', 'perm_ban_delete', 1),
                                ('SUPER_ADMIN', 'perm_post_access', 1),
                                ('SUPER_ADMIN', 'perm_post_modify', 1),
                                ('SUPER_ADMIN', 'perm_post_delete', 1),
                                ('SUPER_ADMIN', 'perm_post_file_delete', 1),
                                ('SUPER_ADMIN', 'perm_post_default_name', 1),
                                ('SUPER_ADMIN', 'perm_post_custom_name', 1),
                                ('SUPER_ADMIN', 'perm_post_override_anon', 1),
                                ('SUPER_ADMIN', 'perm_post_sticky', 1),
                                ('SUPER_ADMIN', 'perm_post_unsticky', 1),
                                ('SUPER_ADMIN', 'perm_post_lock', 1),
                                ('SUPER_ADMIN', 'perm_post_unlock', 1),
                                ('SUPER_ADMIN', 'perm_post_in_locked', 1),
                                ('SUPER_ADMIN', 'perm_post_comment', 1),
                                ('SUPER_ADMIN', 'perm_post_permsage', 1),
                                ('SUPER_ADMIN', 'perm_regen_caches', 1),
                                ('SUPER_ADMIN', 'perm_regen_index', 1),
                                ('SUPER_ADMIN', 'perm_regen_threads', 1),
                                ('SUPER_ADMIN', 'perm_modmode_access', 1),
                                ('SUPER_ADMIN', 'perm_modmode_view_ips', 1),
                                ('BOARD_ADMIN', 'perm_config_access', 1),
                                ('BOARD_ADMIN', 'perm_config_modify', 1),
                                ('BOARD_ADMIN', 'perm_user_access', 1),
                                ('BOARD_ADMIN', 'perm_user_add', 1),
                                ('BOARD_ADMIN', 'perm_user_modify', 1),
                                ('BOARD_ADMIN', 'perm_user_delete', 1),
                                ('BOARD_ADMIN', 'perm_user_change_pass', 1),
                                ('BOARD_ADMIN', 'perm_role_access', 1),
                                ('BOARD_ADMIN', 'perm_role_add', 1),
                                ('BOARD_ADMIN', 'perm_role_modify', 1),
                                ('BOARD_ADMIN', 'perm_role_delete', 1),
                                ('BOARD_ADMIN', 'perm_ban_access', 1),
                                ('BOARD_ADMIN', 'perm_ban_add', 1),
                                ('BOARD_ADMIN', 'perm_ban_modify', 1),
                                ('BOARD_ADMIN', 'perm_ban_delete', 1),
                                ('BOARD_ADMIN', 'perm_post_access', 1),
                                ('BOARD_ADMIN', 'perm_post_modify', 1),
                                ('BOARD_ADMIN', 'perm_post_delete', 1),
                                ('BOARD_ADMIN', 'perm_post_file_delete', 1),
                                ('BOARD_ADMIN', 'perm_post_default_name', 1),
                                ('BOARD_ADMIN', 'perm_post_custom_name', 1),
                                ('BOARD_ADMIN', 'perm_post_override_anon', 1),
                                ('BOARD_ADMIN', 'perm_post_sticky', 1),
                                ('BOARD_ADMIN', 'perm_post_unsticky', 1),
                                ('BOARD_ADMIN', 'perm_post_lock', 1),
                                ('BOARD_ADMIN', 'perm_post_unlock', 1),
                                ('BOARD_ADMIN', 'perm_post_in_locked', 1),
                                ('BOARD_ADMIN', 'perm_post_comment', 1),
                                ('BOARD_ADMIN', 'perm_post_permsage', 1),
                                ('BOARD_ADMIN', 'perm_regen_caches', 1),
                                ('BOARD_ADMIN', 'perm_regen_index', 1),
                                ('BOARD_ADMIN', 'perm_regen_threads', 1),
                                ('BOARD_ADMIN', 'perm_modmode_access', 1),
                                ('BOARD_ADMIN', 'perm_modmode_view_ips', 1),
                                ('MOD', 'perm_config_access', 0),
                                ('MOD', 'perm_config_change', 0),
                                ('MOD', 'perm_user_access', 0),
                                ('MOD', 'perm_user_add', 0),
                                ('MOD', 'perm_user_modify', 0),
                                ('MOD', 'perm_user_delete', 0),
                                ('MOD', 'perm_user_change_pass', 1),
                                ('MOD', 'perm_role_access', 0),
                                ('MOD', 'perm_role_add', 0),
                                ('MOD', 'perm_role_modify', 0),
                                ('MOD', 'perm_role_delete', 0),
                                ('MOD', 'perm_ban_access', 1),
                                ('MOD', 'perm_ban_add', 1),
                                ('MOD', 'perm_ban_modify', 1),
                                ('MOD', 'perm_ban_delete', 1),
                                ('MOD', 'perm_post_access', 1),
                                ('MOD', 'perm_post_modify', 0),
                                ('MOD', 'perm_post_delete', 1),
                                ('MOD', 'perm_post_file_delete', 1),
                                ('MOD', 'perm_post_default_name', 1),
                                ('MOD', 'perm_post_custom_name', 0),
                                ('MOD', 'perm_post_override_anon', 0),
                                ('MOD', 'perm_post_sticky', 1),
                                ('MOD', 'perm_post_unsticky', 1),
                                ('MOD', 'perm_post_lock', 1),
                                ('MOD', 'perm_post_unlock', 1),
                                ('MOD', 'perm_post_in_locked', 1),
                                ('MOD', 'perm_post_comment', 1),
                                ('MOD', 'perm_post_permsage', 1),
                                ('MOD', 'perm_regen_caches', 0),
                                ('MOD', 'perm_regen_index', 0),
                                ('MOD', 'perm_regen_threads', 0),
                                ('MOD', 'perm_modmode_access', 1),
                                ('MOD', 'perm_modmode_view_ips', 1),
                                ('JANITOR', 'perm_config_access', 0),
                                ('JANITOR', 'perm_config_change', 0),
                                ('JANITOR', 'perm_user_access', 0),
                                ('JANITOR', 'perm_user_add', 0),
                                ('JANITOR', 'perm_user_modify', 0),
                                ('JANITOR', 'perm_user_delete', 0),
                                ('JANITOR', 'perm_user_change_pass', 1),
                                ('JANITOR', 'perm_role_access', 0),
                                ('JANITOR', 'perm_role_add', 0),
                                ('JANITOR', 'perm_role_modify', 0),
                                ('JANITOR', 'perm_role_delete', 0),
                                ('JANITOR', 'perm_ban_access', 0),
                                ('JANITOR', 'perm_ban_add', 0),
                                ('JANITOR', 'perm_ban_modify', 0),
                                ('JANITOR', 'perm_ban_delete', 0),
                                ('JANITOR', 'perm_post_access', 1),
                                ('JANITOR', 'perm_post_modify', 0),
                                ('JANITOR', 'perm_post_delete', 1),
                                ('JANITOR', 'perm_post_file_delete', 1),
                                ('JANITOR', 'perm_post_default_name', 0),
                                ('JANITOR', 'perm_post_custom_name', 0),
                                ('JANITOR', 'perm_post_override_anon', 0),
                                ('JANITOR', 'perm_post_sticky', 0),
                                ('JANITOR', 'perm_post_unsticky', 0),
                                ('JANITOR', 'perm_post_lock', 0),
                                ('JANITOR', 'perm_post_unlock', 0),
                                ('JANITOR', 'perm_post_in_locked', 0),
                                ('JANITOR', 'perm_post_comment', 0),
                                ('JANITOR', 'perm_post_permsage', 0),
                                ('JANITOR', 'perm_regen_caches', 0),
                                ('JANITOR', 'perm_regen_index', 0),
                                ('JANITOR', 'perm_regen_threads', 0),
                                ('JANITOR', 'perm_modmode_access', 1),
                                ('JANITOR', 'perm_modmode_view_ips', 0)");

    nel_setup_stuff_done($result);
}

function nel_insert_default_admin()
{
    if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
    {
        return false;
    }

    $dbh = nel_database();
    $result = $dbh->query('SELECT 1 FROM "' . USER_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query('INSERT INTO "' . USER_TABLE . '" (user_id, user_password, active, failed_logins, last_failed_login)
    VALUES (\'' .
         DEFAULTADMIN . '\', \'' . nel_password_hash(DEFAULTADMIN_PASS, NELLIEL_PASS_ALGORITHM) . '\', 1, 1, 0)');

    nel_setup_stuff_done($result);
}

function nel_insert_default_admin_role()
{
    if (DEFAULTADMIN === '' || DEFAULTADMIN_PASS === '')
    {
        return false;
    }

    $dbh = nel_database();
    $result = $dbh->query('SELECT 1 FROM "' . USER_ROLE_TABLE . '" WHERE "user_id" = \'' . DEFAULTADMIN . '\'');

    if ($result->fetch() !== false)
    {
        return false;
    }

    $result = $dbh->query('INSERT INTO "' . USER_ROLE_TABLE . '" (user_id, role_id, all_boards)
    VALUES
    (\'' . DEFAULTADMIN . '\', \'SUPER_ADMIN\', 1)');

    nel_setup_stuff_done($result);
}

function nel_insert_board_config_defaults($config_table)
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . $config_table . " (config_type, config_owner, config_category, data_type, config_name, setting)
                VALUES  ('technical', 'nelliel', 'database', 'str', 'original_schema_version', '003'),
                        ('technical', 'nelliel', 'database', 'str', 'current_schema_version', '003'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'allow_tripkeys', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'force_anonymous', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'show_title', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'show_favicon', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'show_logo', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_thumb', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_magick', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_file_icon', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_png_thumb', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'require_image_start', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'require_image_always', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'allow_multifile', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'allow_op_multifile', '0'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_new_imgdel', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_fgsfds', '1'),
                        ('board_setting', 'nelliel', 'general', 'bool', 'use_spambot_trap', '1'),
                        ('board_setting', 'nelliel', 'general', 'str', 'board_name', 'Nelliel-powered image board'),
                        ('board_setting', 'nelliel', 'general', 'str', 'board_favicon', ''),
                        ('board_setting', 'nelliel', 'general', 'str', 'board_logo', ''),
                        ('board_setting', 'nelliel', 'general', 'int', 'board_language', 'en-us'),
                        ('board_setting', 'nelliel', 'general', 'int', 'thread_delay', '60'),
                        ('board_setting', 'nelliel', 'general', 'int', 'reply_delay', '15'),
                        ('board_setting', 'nelliel', 'general', 'int', 'abbreviate_thread', '5'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_post_files', '3'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_files_row', '3'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_multi_width', '175'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_multi_height', '175'),
                        ('board_setting', 'nelliel', 'general', 'int', 'jpeg_quality', '85'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_width', '256'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_height', '256'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_filesize', '1024'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_name_length', '100'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_email_length', '100'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_subject_length', '100'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_comment_length', '5000'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_comment_lines', '60'),
                        ('board_setting', 'nelliel', 'general', 'int', 'comment_display_lines', '15'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_source_length', '255'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_license_length', '255'),
                        ('board_setting', 'nelliel', 'general', 'int', 'threads_per_page', '10'),
                        ('board_setting', 'nelliel', 'general', 'int', 'page_limit', '10'),
                        ('board_setting', 'nelliel', 'general', 'int', 'page_buffer', '0'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_posts', '1000'),
                        ('board_setting', 'nelliel', 'general', 'int', 'max_bumps', '750'),
                        ('board_setting', 'nelliel', 'general', 'str', 'tripkey_marker', '!'),
                        ('board_setting', 'nelliel', 'general', 'str', 'date_format', 'ISO'),
                        ('board_setting', 'nelliel', 'general', 'str', 'old_threads', 'ARCHIVE'),
                        ('board_setting', 'nelliel', 'general', 'str', 'date_separator', '/'),
                        ('board_setting', 'nelliel', 'general', 'str', 'fgsfds_name', 'FGSFDS'),
                        ('board_setting', 'nelliel', 'general', 'str', 'indent_marker', '>>'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'graphics', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'gif', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'png', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'jpeg2000', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'tiff', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'bmp', '1'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'ico', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'psd', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'tga', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'pict', '0'),
                        ('filetype_enable', 'nelliel', 'graphics', 'bool', 'art', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'audio', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'wav', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'aiff', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'mp3', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'm4a', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'flac', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'aac', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'ogg', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'au', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'wma', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'midi', '0'),
                        ('filetype_enable', 'nelliel', 'audio', 'bool', 'ac3', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'video', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mpeg', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mov', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'avi', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'wmv', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mp4', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'mkv', '0'),
                        ('filetype_enable', 'nelliel', 'video', 'bool', 'flv', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'document', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'rtf', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'pdf', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'doc', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'ppt', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'xls', '0'),
                        ('filetype_enable', 'nelliel', 'document', 'bool', 'txt', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'archive', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'gzip', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'bz2', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'hqx', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'lzh', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'zip', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'rar', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'sit', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'tar', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', '7z', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'iso', '0'),
                        ('filetype_enable', 'nelliel', 'archive', 'bool', 'dmg', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'other', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'swf', '0'),
                        ('filetype_enable', 'nelliel', 'other', 'bool', 'blorb', '0')
                        ");

    nel_setup_stuff_done($result);
}

function nel_insert_site_config_defaults($config_table)
{
    $dbh = nel_database();
    $result = $dbh->query("INSERT INTO " . $config_table . " (config_type, config_owner, config_category, data_type, config_name, setting)
                VALUES  ('core_setting', 'nelliel', 'general', 'str', 'home_page', '../')
                        ");

    nel_setup_stuff_done($result);
}
