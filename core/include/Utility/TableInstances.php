<?php
declare(strict_types = 1);

namespace Nelliel\Utility;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Database\NellielPDO;

class TableInstances
{
    private NellielPDO $database;

    function __construct(NellielPDO $database)
    {
        $this->database = $database;
    }

    public function getByClassName(string $class_name, NellielPDO $database = null): object
    {
        $class = '\Nelliel\Tables\\' . $class_name;
        return new $class($database ?? $this->database, nel_utilities()->sqlCompatibility());
    }
}
