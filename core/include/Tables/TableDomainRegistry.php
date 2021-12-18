<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use PDO;

class TableDomainRegistry extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_DOMAIN_REGISTRY_TABLE;
        $this->column_types = [
            'domain_id' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'notes' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'domain_id' => ['row_check' => true, 'auto_inc' => false],
            'notes' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            domain_id   VARCHAR(50) PRIMARY KEY NOT NULL,
            notes       TEXT DEFAULT NULL,
            moar        TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow([Domain::SITE, hash('sha256', Domain::SITE), 'System domain. NEVER DELETE!']);
        $this->insertDefaultRow([Domain::GLOBAL, hash('sha256', Domain::GLOBAL), 'System domain. NEVER DELETE!']);
    }
}