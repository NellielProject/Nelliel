<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use PDO;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

abstract class Admin
{
    protected $database;
    protected $authorization;
    protected $domain;
    protected $session;
    protected $session_user;
    protected $output_main = true;
    protected $inputs;
    protected $data_table;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->session = $session;
        $this->session->loggedInOrError();
        $this->session_user = $session->user();
    }

    public abstract function renderPanel();

    public abstract function creator();

    public abstract function add();

    public abstract function editor();

    public abstract function update();

    public abstract function remove();

    public abstract function enable();

    public abstract function disable();

    public abstract function makeDefault();

    public abstract function verifyAccess(Domain $domain);

    public abstract function verifyAction(Domain $domain);

    protected function getEntryDomain($id): Domain
    {
        $entry = $this->getEntryByID($id);
        return Domain::getDomainFromID($entry['board_id'], $this->database);
    }

    public function outputMain(bool $value = null)
    {
        if (!is_null($value))
        {
            $this->output_main = $value;
        }

        return $this->output_main;
    }

    protected function getEntryByID($id): array
    {
        $prepared = $this->database->prepare('SELECT * FROM "' . $this->data_table . '" WHERE "entry" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$id], PDO::FETCH_ASSOC);
        return ($result !== false) ? $result : array();
    }

    public function globalIDToNull(string $id, string $permission): ?string
    {
        if (!nel_true_empty($id) && $id !== Domain::GLOBAL && $id !== Domain::SITE)
        {
            return $id;
        }

        if (!$this->session_user->checkPermission(nel_global_domain(), $permission, false))
        {
            nel_derp();
        }

        return null;
    }
}

