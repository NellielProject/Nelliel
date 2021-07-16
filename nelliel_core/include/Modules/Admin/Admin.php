<?php
declare(strict_types = 1);

namespace Nelliel\Modules\Admin;

use Nelliel\Modules\DispatchErrors;
use Nelliel\Modules\Account\Session;
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
    protected $id_field = 'id';
    protected $id_column = 'entry';

    use DispatchErrors;

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->session = $session;
        $this->session->loggedInOrError();
        $this->session_user = $session->user();
    }

    public function dispatch(array $inputs): void
    {
        foreach ($inputs['actions'] as $action)
        {
            switch ($action)
            {
                case 'panel':
                    $this->panel();
                    break;

                case 'new':
                    $this->creator();
                    break;

                case 'add':
                    $this->add();
                    break;

                case 'edit':
                    $this->editor();
                    break;

                case 'update':
                    $this->update();
                    break;

                case 'remove':
                    $this->remove();
                    break;
            }
        }
    }

    public abstract function panel(): void;

    public abstract function creator(): void;

    public abstract function add(): void;

    public abstract function editor(): void;

    public abstract function update(): void;

    public abstract function remove(): void;

    public abstract function verifyAccess(Domain $domain);

    public abstract function verifyAction(Domain $domain);

    protected function getEntryDomain($id): Domain
    {
        $entry = $this->getEntryByID($id);
        $domain_id = $entry['board_id'] ?? Domain::SITE;
        return Domain::getDomainFromID($domain_id, $this->database);
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

