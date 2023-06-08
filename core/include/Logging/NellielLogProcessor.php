<?php
declare(strict_types = 1);

namespace Nelliel\Logging;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Monolog\Processor\ProcessorInterface;
use Nelliel\IPInfo;
use Nelliel\Domains\Domain;

class NellielLogProcessor implements ProcessorInterface
{

    function __invoke($record): array
    {
        $record['extra']['event'] = $record['context']['event'] ?? '';
        $ip_info = new IPInfo(nel_request_ip_address(true), nel_request_ip_address());
        $record['extra']['hashed_ip_address'] = $ip_info->getInfo('hashed_ip_address');
        $record['extra']['ip_address'] = $ip_info->getInfo('ip_address');
        $record['extra']['message_values'] = json_encode($record['context']['values'] ?? '');
        $record['extra']['visitor_id'] = nel_visitor_id();
        $record['extra']['domain_id'] = $record['context']['domain_id'] ?? Domain::SITE;
        $record['extra']['username'] = $record['context']['username'] ?? nel_session()->user()->id();
        $record['extra']['moar'] = $record['context']['moar'] ?? null;
        return $record;
    }
}