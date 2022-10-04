<?php
declare(strict_types = 1);

namespace Nelliel\Admin;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Domains\Domain;
use PDO;

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
    protected $panel_name = '';
    protected $id_column = 'entry';

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        $this->database = $domain->database();
        $this->authorization = $authorization;
        $this->domain = $domain;
        $this->session = $session;
        $this->session->loggedInOrError();
        $this->session_user = $session->user();
    }

    //public abstract function panel(): void;

    protected abstract function verifyPermissions(Domain $domain, string $perm): void;

    protected function getEntryDomain($id): Domain
    {
        $entry = $this->getEntryByID($id);
        $domain_id = $entry['board_id'] ?? Domain::SITE;
        return Domain::getDomainFromID($domain_id, $this->database);
    }

    protected function getEntryByID($id): array
    {
        $prepared = $this->database->prepare(
            'SELECT * FROM "' . $this->data_table . '" WHERE "' . $this->id_column . '" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$id], PDO::FETCH_ASSOC);
        return ($result !== false) ? $result : array();
    }

    protected function defaultPermissionError()
    {
        nel_derp(300, _gettext('You do not have permission for that.'));
    }
}

