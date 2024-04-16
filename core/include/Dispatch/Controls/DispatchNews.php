<?php
declare(strict_types = 1);

namespace Nelliel\Dispatch\Controls;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\NewsArticle;
use Nelliel\Regen;
use Nelliel\Account\Session;
use Nelliel\Auth\Authorization;
use Nelliel\Dispatch\Dispatch;
use Nelliel\Domains\Domain;
use Nelliel\Output\OutputPanelNews;

class DispatchNews extends Dispatch
{

    function __construct(Authorization $authorization, Domain $domain, Session $session)
    {
        parent::__construct($authorization, $domain, $session);
        $this->session->init(true);
        $this->session->loggedInOrError();
    }

    public function dispatch(array $inputs): void
    {
        $article_id = intval($inputs['id'] ?? 0);

        switch ($inputs['section']) {
            case 'new':
                if ($inputs['method'] === 'POST') {
                    $this->verifyPermissions($this->domain, 'perm_manage_news');
                    $news_article = new NewsArticle($this->domain->database());
                    $news_article->changeData('username', $this->session->user()->id());
                    $name = $_POST['name'] ?? '';

                    if ($name === '' || !$this->session->user()->checkPermission($this->domain, 'perm_custom_name')) {
                        $news_article->changeData('name', $this->session->user()->id());
                    } else {
                        $news_article->changeData('name', $name);
                    }

                    $news_article->changeData('headline', $_POST['headline'] ?? '');
                    $news_article->changeData('time', time());
                    $news_article->changeData('text', $_POST['text'] ?? '');
                    $news_article->save();
                    $regen = new Regen();
                    $regen->news($this->domain);
                }

                break;

            case 'delete':
                $this->verifyPermissions($this->domain, 'perm_manage_news');
                $news_article = new NewsArticle($this->domain->database(), $article_id);
                $news_article->delete();
                $regen = new Regen();
                $regen->news($this->domain);
                break;

            default:
                ;
        }

        $this->verifyPermissions($this->domain, 'perm_manage_news');
        $output_panel = new OutputPanelNews($this->domain, false);
        $output_panel->render([], false);
    }

    protected function verifyPermissions(Domain $domain, string $perm): void
    {
        if ($this->session->user()->checkPermission($domain, $perm)) {
            return;
        }

        switch ($perm) {
            case 'perm_manage_news':
                nel_derp(360, _gettext('You are not allowed to manage news entries.'), 403);
                break;

            default:
                $this->defaultPermissionError();
        }
    }
}