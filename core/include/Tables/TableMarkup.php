<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableMarkup extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_MARKUP_TABLE;
        $this->column_types = [
            'markup_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'label' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'type' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'match_regex' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'replacement' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'markup_id' => ['row_check' => true, 'auto_inc' => true, 'update' => false],
            'label' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'type' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'match_regex' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'replacement' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER', false);
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            markup_id   ' . $auto_inc[0] . ' ' . $auto_inc[1] . ' NOT NULL,
            label       VARCHAR(255) NOT NULL,
            type        VARCHAR(255) NOT NULL,
            match_regex TEXT NOT NULL,
            replacement TEXT NOT NULL,
            enabled     SMALLINT NOT NULL DEFAULT 0,
            notes       TEXT DEFAULT NULL,
            moar        ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (markup_id)
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        // Regex should account for escaped and unescaped HTML when practical
        $this->insertDefaultRow(['spoiler', 'simple', '/\|\|(.+?)\|\|/us', '<span class="markup-spoiler">$1</span>', 1]);
        $this->insertDefaultRow(['italic', 'simple', '/(?:(?<!\*))\*{2}(.+?)(?:(?<!\*))\*{2}(?:(?!\*))/us', '<span class="markup-italic">$1</span>', 1]);
        $this->insertDefaultRow(['vichan-italic', 'simple', '/(?:(?<!\'|&#039;|&apos;))(?:\'|&#039;|&apos;){2}(.+?)(?:(?<!\'|&#039;|&apos;))(?:\'|&#039;|&apos;){2}(?:(?!\'|&#039;|&apos;))/us', '<span class="markup-italic">$1</span>', 1]);
        $this->insertDefaultRow(['bold', 'simple', '/(?:(?<!\*))\*{3}(.+?)(?:(?<!\*))\*{3}(?:(?!\*))/us', '<span class="markup-bold">$1</span>', 1]);
        $this->insertDefaultRow(['vichan-bold', 'simple', '/(?:(?<!\'|&#039;|&apos;))(?:\'|&#039;|&apos;){3}(.+?)(?:(?<!\'|&#039;|&apos;))(?:\'|&#039;|&apos;){3}(?:(?!\'|&#039;|&apos;))/us', '<span class="markup-bold">$1</span>', 1]);
        $this->insertDefaultRow(['underline', 'simple', '/__(.+?)__/us', '<span class="markup-underline">$1</span>', 1]);
        $this->insertDefaultRow(['strikethrough', 'simple', '/~~(.+?)~~/us', '<span class="markup-strikethrough">$1</span>', 1]);
        $this->insertDefaultRow(['nested-spoiler', 'loop', '/\[spoiler (\d+)\](.*?)\[\/spoiler \1\]/us', '<span class="markup-spoiler">$2</span>', 1]);
        $this->insertDefaultRow(['greentext', 'line', '/^((>|&gt;)(?!(>|&gt;)\d+|(>>|&gt;&gt;)\/[^\/]+\/).*)$/u', '<span class="markup-greentext">$1</span>', 1]);
        $this->insertDefaultRow(['pinktext', 'line', '/^(&lt;.*)$/u', '<span class="markup-pinktext">$1</span>', 1]); // < could be a valid tag from other markup so we don't match the unescaped version
        $this->insertDefaultRow(['orangetext', 'line', '/^(\^.*)$/u', '<span class="markup-orangetext">$1</span>', 1]);
        $this->insertDefaultRow(['ascii', 'block', '/\[ascii\]|\[\/ascii\]/', '<pre class="ascii-art">$1</pre>', 1]);
        $this->insertDefaultRow(['shift-jis-art', 'block', '/\[sjis]|\[\/sjis\]/', '<pre class="shift-jis-art">$1</pre>', 1]);
    }
}