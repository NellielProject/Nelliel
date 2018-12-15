<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_settings_panel($domain, $defaults)
{
    $database = nel_database();
    $translator = new \Nelliel\Language\Translator();
    $filetypes = new \Nelliel\FileTypes($database);
    $domain->renderInstance()->startRenderTimer();
    $dom = $domain->renderInstance()->newDOMDocument();
    $domain->renderInstance()->loadTemplateFromFile($dom, 'management/board_settings_panel.html');
    $settings_form = $dom->getElementById('board-settings-form');
    $settings_form_nodes = $settings_form->getElementsByAttributeName('data-parse-id', true);

    if ($defaults === true)
    {
        nel_render_general_header($domain->renderInstance(), null, null,
                array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Default Board Settings')));
        $result = $database->query('SELECT * FROM "' . BOARD_DEFAULTS_TABLE . '"');
        $settings_form->extSetAttribute('action',
                PHP_SELF . '?module=default-board-settings&action=update');
    }
    else
    {
        nel_render_general_header($domain->renderInstance(), null, $domain->id(),
                array('header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')));
        $result = $database->query('SELECT * FROM "' . $domain->reference('config_table') . '"');
        $settings_form->extSetAttribute('action',
                PHP_SELF . '?module=board-settings&action=update&board_id=' . $domain->id());
    }

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    $all_filetypes = $filetypes->getFiletypeData();
    $all_categories = $filetypes->getFiletypeCategories();
    $category_nodes = array();
    $filetype_entries_nodes = array();

    foreach ($all_categories as $category)
    {
        $filetype_category = $dom->copyNode($settings_form_nodes['filetype-category-table'], $settings_form, 'append');
        $category_nodes['category-' . $category['type']] = $filetype_category;
        $filetype_category_nodes = $filetype_category->getElementsByAttributeName('data-parse-id', true);
        $filetype_category_nodes['category-header']->setContent($category['label']);
        $filetype_category->changeId('category-' . $category['type']);
        $filetype_category_nodes['entry-label']->setContent('Allow ' . $category['type']);
        $filetype_entries_nodes[$category['type']] = $filetype_category_nodes['filetype-entry']->getElementsByAttributeName('data-parse-id', true);
    }

    $count = 0;
    $current_entry_row = null;

    foreach ($all_filetypes as $filetype)
    {
        if ($filetype['extension'] == $filetype['parent_extension'])
        {
            if($count > 2)
            {
                $count = 0;
            }

            if($count === 0)
            {
                $parent_category = $category_nodes['category-' . $filetype['type']];
                $current_entry_row = $dom->copyNode($settings_form_nodes['filetype-entry-row'], $parent_category, 'append');
                $current_entry_row->getElementsByAttributeName('data-parse-id', true)['filetype-entry']->remove();
            }

            $current_entry = $dom->copyNode($settings_form_nodes['filetype-entry'], $current_entry_row, 'append');
            $current_entry_nodes = $current_entry->getElementsByAttributeName('data-parse-id', true);
            $current_entry_nodes['entry-label']->addContent($filetype['label'] . ' - .' . $filetype['extension'], 'before');
            $filetype_entries_nodes[$filetype['format']] = $current_entry_nodes;
            $count++;
        }
        else
        {
            $filetype_entries_nodes[$filetype['format']]['entry-label']->addContent(', .' . $filetype['extension'], 'after');
        }
    }

    foreach ($rows as $config_line)
    {
        if ($config_line['data_type'] === 'bool')
        {
            if ($config_line['config_type'] === 'filetype_enable')
            {
                if ($config_line['setting'] == 1)
                {
                    $filetype_entries_nodes[$config_line['config_name']]['entry-checkbox']->extSetAttribute('checked', 'true');
                }
            }
            else
            {
                $config_element = $dom->getElementById($config_line['config_name']);

                if (is_null($config_element))
                {
                    continue;
                }

                if ($config_line['setting'] == 1)
                {
                    $config_element->extSetAttribute('checked', 'true');
                }
            }
        }
        else
        {
            // TODO: Can be simplified
            switch ($config_line['setting'])
            {
                case 'ISO':
                    $dom->getElementById('date_iso')->extSetAttribute('checked', 'true');
                    break;

                case 'COM':
                    $dom->getElementById('date_com')->extSetAttribute('checked', 'true');
                    break;

                case 'US':
                    $dom->getElementById('date_us')->extSetAttribute('checked', 'true');
                    break;

                case 'ARCHIVE':
                    $dom->getElementById('old_threads_a')->extSetAttribute('checked', 'true');
                    break;

                case 'PRUNE':
                    $dom->getElementById('old_threads_p')->extSetAttribute('checked', 'true');
                    break;

                case 'NOTHING':
                    $dom->getElementById('old_threads_n')->extSetAttribute('checked', 'true');
                    break;

                default:
                    $config_element = $dom->getElementById($config_line['config_name']);

                    if (!is_null($config_element))
                    {
                        $config_element->extSetAttribute('value', $config_line['setting']);
                    }
            }
        }
    }

    $settings_form->appendChild($dom->getElementById('bottom-submit'));
    $settings_form_nodes['filetype-category-table']->remove();
    $translator->translateDom($dom, $domain->setting('language'));
    $domain->renderInstance()->appendHTMLFromDOM($dom);
    nel_render_general_footer($domain);
    echo $domain->renderInstance()->outputRenderSet();
    nel_clean_exit();
}