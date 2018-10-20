<?php

namespace Nelliel\setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class SQLTables
{
    private $insert_data;

    function __construct()
    {
        $this->insert_data = new \Nelliel\setup\TableInsertData();
    }

    public function createThreadsTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            thread_id               INTEGER NOT NULL PRIMARY KEY,
            first_post              INTEGER NULL DEFAULT 0,
            last_post               INTEGER NULL DEFAULT 0,
            last_bump_time          BIGINT DEFAULT NULL,
            last_bump_time_milli    SMALLINT DEFAULT NULL,
            total_files             INTEGER NOT NULL DEFAULT 0,
            last_update             BIGINT DEFAULT NULL,
            last_update_milli       SMALLINT DEFAULT NULL,
            post_count              INTEGER NOT NULL DEFAULT 0,
            thread_sage             SMALLINT NOT NULL DEFAULT 0,
            sticky                  SMALLINT NOT NULL DEFAULT 0,
            archive_status          SMALLINT NOT NULL DEFAULT 0,
            locked                  SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        nel_setup_stuff_done($result);
    }

    public function createPostsTable($table_name, $threads_table)
    {
        $dbh = nel_database();
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            post_number           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            parent_thread           INTEGER NULL DEFAULT 0,
            poster_name             VARCHAR(255) NULL DEFAULT '',
            post_password           VARCHAR(255) NULL DEFAULT '',
            tripcode                VARCHAR(255) NULL DEFAULT '',
            secure_tripcode         VARCHAR(255) NULL DEFAULT '',
            email                   VARCHAR(255) NULL DEFAULT '',
            subject                 VARCHAR(255) NULL DEFAULT '',
            comment                 TEXT,
            ip_address              " . $this->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            post_time               BIGINT DEFAULT NULL,
            post_time_milli         SMALLINT DEFAULT NULL,
            has_file                SMALLINT NOT NULL DEFAULT 0,
            file_count              SMALLINT NOT NULL DEFAULT 0,
            op                      SMALLINT NOT NULL DEFAULT 0,
            sage                    SMALLINT NOT NULL DEFAULT 0,
            mod_post                VARCHAR(255) NULL DEFAULT '',
            mod_comment             VARCHAR(255) NULL DEFAULT '',
            CONSTRAINT fk_parent_thread_" . $threads_table . "_thread_id
            FOREIGN KEY (parent_thread) REFERENCES " . $threads_table . "(thread_id)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result)
        {
            $dbh->query('CREATE INDEX index_' . $table_name . '_parent_thread ON ' . $table_name . ' (parent_thread);');
        }

        nel_setup_stuff_done($result);
    }

    public function createFilesTable($table_name, $posts_table)
    {
        $dbh = nel_database();
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                 " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            parent_thread           INTEGER NULL DEFAULT 0,
            post_ref                INTEGER NULL DEFAULT 0,
            file_order              SMALLINT NULL DEFAULT 0,
            type                    VARCHAR(255) NULL DEFAULT '',
            format                  VARCHAR(255) NULL DEFAULT '',
            mime                    VARCHAR(255) NULL DEFAULT '',
            filename                VARCHAR(255) NULL DEFAULT '',
            extension               VARCHAR(255) NULL DEFAULT '',
            display_width           INTEGER NULL DEFAULT 0,
            display_height          INTEGER NULL DEFAULT 0,
            preview_name            VARCHAR(255) NULL DEFAULT '',
            preview_extension       VARCHAR(255) NULL DEFAULT '',
            preview_width           SMALLINT NULL DEFAULT 0,
            preview_height          SMALLINT NULL DEFAULT 0,
            filesize                INTEGER NULL DEFAULT 0,
            md5                     " . $this->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            sha1                    " . $this->sqlAlternatives('VARBINARY', '20') . " DEFAULT NULL,
            sha256                  " . $this->sqlAlternatives('VARBINARY', '32') . " DEFAULT NULL,
            sha512                  " . $this->sqlAlternatives('VARBINARY', '64') . " DEFAULT NULL,
            source                  VARCHAR(255) NULL DEFAULT '',
            license                 VARCHAR(255) NULL DEFAULT '',
            alt_text                VARCHAR(255) NULL DEFAULT '',
            exif                    TEXT,
            CONSTRAINT fk_post_ref_" . $posts_table . "_post_number
            FOREIGN KEY(post_ref) REFERENCES " . $posts_table . "(post_number)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result)
        {
            $dbh->query('CREATE INDEX index_' . $table_name . '_post_ref ON ' . $table_name . ' (post_ref);');
            $dbh->query('CREATE INDEX index_' . $table_name . '_md5 ON ' . $table_name . ' (md5);');
            $dbh->query('CREATE INDEX index_' . $table_name . '_sha1 ON ' . $table_name . ' (sha1);');
            $dbh->query('CREATE INDEX index_' . $table_name . '_sha256 ON ' . $table_name . ' (sha256);');
        }

        nel_setup_stuff_done($result);
    }

    public function createBoardConfigTable($table_name, $copy_defaults)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            config_name             VARCHAR(255) NOT NULL,
            config_type             VARCHAR(255) NULL DEFAULT '',
            config_owner            VARCHAR(255) NULL DEFAULT 'nelliel',
            config_category         VARCHAR(255) NULL DEFAULT 'general',
            data_type               VARCHAR(255) NULL DEFAULT '',
            setting                 VARCHAR(255) NULL DEFAULT ''
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result !== false)
        {
            if($copy_defaults)
            {
                $this->insert_data->copyBoardDefaults($table_name);
            }
            else
            {
                $this->insert_data->boardConfigDefaults($table_name);
            }
        }

        nel_setup_stuff_done($result);
    }

    public function createSiteConfigTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            config_name             VARCHAR(255) NOT NULL,
            config_type             VARCHAR(255) NULL DEFAULT '',
            config_owner            VARCHAR(255) NULL DEFAULT 'nelliel',
            config_category         VARCHAR(255) NULL DEFAULT 'general',
            data_type               VARCHAR(255) NULL DEFAULT '',
            setting                 VARCHAR(255) NULL DEFAULT ''
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result !== false)
        {
            $this->insert_data->siteConfigDefaults();
        }

        nel_setup_stuff_done($result);
    }

    public function createUserTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id                 VARCHAR(255) NOT NULL,
            display_name            VARCHAR(255) NULL DEFAULT '',
            user_password           VARCHAR(255) NULL DEFAULT '',
            active                  SMALLINT NOT NULL DEFAULT 0,
            failed_logins           SMALLINT NOT NULL DEFAULT 0,
            last_failed_login       BIGINT DEFAULT NULL
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        $this->insert_data->defaultAdmin();
        nel_setup_stuff_done($result);
    }

    public function createRolesTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id                 VARCHAR(255) NOT NULL,
            role_level              SMALLINT NOT NULL DEFAULT 0,
            role_title              VARCHAR(255) NULL DEFAULT '',
            capcode_text            VARCHAR(255) NULL DEFAULT ''
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result !== false)
        {
            $this->insert_data->roleDefaults();
        }

        nel_setup_stuff_done($result);
    }

    public function createUserRoleTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            user_id                 VARCHAR(255) NOT NULL,
            role_id                 VARCHAR(255) NOT NULL,
            board                   VARCHAR(255) NULL DEFAULT ''
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        $this->insert_data->defaultAdminRole();
        nel_setup_stuff_done($result);
    }

    public function createPermissionsTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            role_id                 VARCHAR(255) NULL DEFAULT '',
            perm_id                 VARCHAR(255) NOT NULL,
            perm_setting            SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result !== false)
        {
            $this->insert_data->permissionsDefaults();
        }

        nel_setup_stuff_done($result);
    }

    public function createLoginsTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            ip_address              " . $this->sqlAlternatives('VARBINARY', '16') . " NOT NULL UNIQUE,
            failed_attempts         INTEGER NOT NULL DEFAULT 0,
            last_attempt            BIGINT DEFAULT NULL
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        nel_setup_stuff_done($result);
    }

    public function createBansTable($table_name)
    {
        $dbh = nel_database();
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            ban_id                  " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id                VARCHAR(255) NULL DEFAULT '',
            all_boards              SMALLINT NOT NULL DEFAULT 0,
            type                    VARCHAR(255) NULL DEFAULT '',
            creator                 VARCHAR(255) NULL DEFAULT '',
            ip_address_start        " . $this->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            ip_address_end          " . $this->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL,
            reason                  TEXT NULL DEFAULT '',
            length                  BIGINT NOT NULL DEFAULT 0,
            start_time              BIGINT NOT NULL DEFAULT 0,
            appeal                  TEXT NULL DEFAULT '',
            appeal_response         TEXT NULL DEFAULT '',
            appeal_status           SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result)
        {
            $dbh->query('CREATE INDEX index_' . $table_name . '_ip_address_start ON ' . $table_name . ' (ip_address_start);');
            $dbh->query('CREATE INDEX index_' . $table_name . '_ip_address_end ON ' . $table_name . ' (ip_address_end);');
        }

        nel_setup_stuff_done($result);
    }

    public function createBoardDataTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id                VARCHAR(255) NOT NULL,
            board_directory         VARCHAR(255) NOT NULL,
            db_prefix               VARCHAR(255) NOT NULL
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        nel_setup_stuff_done($result);
    }

    public function createFiletypeTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            extension               VARCHAR(255) NOT NULL,
            parent_extension        VARCHAR(255) NULL DEFAULT '',
            type                    VARCHAR(255) NULL DEFAULT '',
            format                  VARCHAR(255) NULL DEFAULT '',
            mime                    VARCHAR(255) NULL DEFAULT '',
            id_regex                VARCHAR(512) NULL DEFAULT '',
            label                   VARCHAR(255) NULL DEFAULT ''
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);

        if ($result !== false)
        {
            $this->insert_data->filetypes();
        }

        nel_setup_stuff_done($result);
    }

    public function createFileFilterTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            entry                   " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            hash_type               VARCHAR(255) NOT NULL,
            file_hash               " . $this->sqlAlternatives('VARBINARY', '128') . " DEFAULT NULL,
            file_notes              VARCHAR(255) NULL DEFAULT ''
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        nel_setup_stuff_done($result);
    }

    public function createReportsTable($table_name)
    {
        $auto_inc = $this->autoincrementColumn('INTEGER');
        $options = $this->tableOptions();
        $schema = "
        CREATE TABLE " . $table_name . " (
            report_id               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            board_id                VARCHAR(255) NULL DEFAULT '',
            content_id              VARCHAR(255) NOT NULL,
            reason                  VARCHAR(255) NULL DEFAULT '',
            reporter_ip             " . $this->sqlAlternatives('VARBINARY', '16') . " DEFAULT NULL
        ) " . $options . ";";

        $result = $this->createTableQuery($schema, $table_name);
        nel_setup_stuff_done($result);
    }

    private function autoincrementColumn($int_column)
    {
        $auto = '';

        if (SQLTYPE === 'MYSQL')
        {
            $auto = 'AUTO_INCREMENT';
        }
        else if (SQLTYPE === 'MARIADB')
        {
            $auto = 'AUTO_INCREMENT';
        }
        else if (SQLTYPE === 'POSTGRESQL')
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

    private function sqlAlternatives($datatype, $length)
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
        else if (SQLTYPE === 'MARIADB')
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
        else if (SQLTYPE === 'POSTGRESQL')
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

    private function tableOptions()
    {
        $options = '';

        if (SQLTYPE === 'MYSQL')
        {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }
        else if (SQLTYPE === 'MARIADB')
        {
            $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
            $options .= ' ENGINE = InnoDB';
        }

        return $options . ';';
    }

    private function createTableQuery($schema, $table_name)
    {
        $dbh = nel_database();

        if ($dbh->tableExists($table_name))
        {
            return false;
        }

        $result = $dbh->query($schema);

        if (!$result)
        {
            $dbh->tableFail($table_name);
        }

        return $result;
    }
}