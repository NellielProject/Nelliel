<?php
declare(strict_types = 1);

namespace Nelliel\Output;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;

class OutputCAPTCHA extends Output
{

    function __construct(Domain $domain, bool $write_mode)
    {
        parent::__construct($domain, $write_mode);
    }

    public function render(array $parameters, bool $data_only)
    {
        $this->renderSetup();
        $this->setupTimer();
        $area = $parameters['area'];
        $this->render_data['captcha_gen_url'] = nel_build_router_url([$this->domain->uri(), 'captcha', 'get']);
        $this->render_data['captcha_regen_url'] = nel_build_router_url([$this->domain->uri(), 'captcha', 'regenerate']);
        $captchas = array();

        if ($this->site_domain->setting('use_native_captcha')) {
            $captchas[]['output'] = $this->output('pieces/native_captcha', $data_only, true, $this->render_data);
        }

        $captchas = nel_plugins()->processHook('nel-in-during-captcha-generation',
            [$this->domain, $this->write_mode, $area], $captchas);
        return $captchas;
    }
}