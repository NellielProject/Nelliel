<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

function nel_render_board_settings_panel($user, \Nelliel\Domain $domain, $defaults)
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
        $output_header = new \Nelliel\Output\OutputHeader($domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Default Board Settings')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $result = $database->query('SELECT * FROM "' . BOARD_DEFAULTS_TABLE . '"');
        $settings_form->extSetAttribute('action', MAIN_SCRIPT . '?module=default-board-settings&action=update');
    }
    else
    {
        $output_header = new \Nelliel\Output\OutputHeader($domain);
        $extra_data = ['header' => _gettext('Board Management'), 'sub_header' => _gettext('Board Settings')];
        $output_header->render(['header_type' => 'general', 'dotdot' => '', 'extra_data' => $extra_data]);
        $result = $database->query('SELECT * FROM "' . $domain->reference('config_table') . '"');
        $settings_form->extSetAttribute('action',
                MAIN_SCRIPT . '?module=board-settings&action=update&board_id=' . $domain->id());
    }

    $rows = $result->fetchAll(PDO::FETCH_ASSOC);
    unset($result);

    $all_filetypes = $filetypes->getFiletypeData();
    $all_categories = $filetypes->getFiletypeCategories();
    $category_nodes = array();
    $filetype_entries_nodes = array();
    $category_row_count = array();

    foreach ($all_categories as $category)
    {
        $filetype_category = $dom->copyNode($settings_form_nodes['filetype-category-table'], $settings_form, 'append');
        $category_nodes['category-' . $category['type']] = $filetype_category;
        $filetype_category_nodes = $filetype_category->getElementsByAttributeName('data-parse-id', true);
        $filetype_category_nodes['category-header']->setContent($category['label']);
        $filetype_category->changeId('category-' . $category['type']);
        $filetype_category_nodes['entry-label']->setContent('Allow ' . $category['type']);
        $filetype_entries_nodes[$category['type']] = $filetype_category_nodes['filetype-entry']->getElementsByAttributeName(
                'data-parse-id', true);
        $filetype_entries_nodes[$category['type']]['entry-hidden-checkbox']->extSetAttribute('name', $category['type']);
        $filetype_entries_nodes[$category['type']]['entry-checkbox']->extSetAttribute('name', $category['type']);
        $category_row_count[$category['type']] = 0;
    }

    $current_entry_row = null;

    foreach ($all_filetypes as $filetype)
    {
        if ($filetype['extension'] == $filetype['parent_extension'])
        {
            if ($category_row_count[$filetype['type']] >= 3)
            {
                $category_row_count[$filetype['type']] = 0;
            }

            if ($category_row_count[$filetype['type']] === 0)
            {
                $parent_category = $category_nodes['category-' . $filetype['type']];
                $current_entry_row = $dom->copyNode($settings_form_nodes['filetype-entry-row'], $parent_category,
                        'append');
                $current_entry_row->getElementsByAttributeName('data-parse-id', true)['filetype-entry']->remove();
            }

            $current_entry = $dom->copyNode($settings_form_nodes['filetype-entry'], $current_entry_row, 'append');
            $current_entry_nodes = $current_entry->getElementsByAttributeName('data-parse-id', true);
            $current_entry_nodes['entry-label']->addContent($filetype['label'] . ' - .' . $filetype['extension'],
                    'before');
            $filetype_entries_nodes[$filetype['format']] = $current_entry_nodes;
            $current_entry_nodes['entry-hidden-checkbox']->extSetAttribute('name', $filetype['format']);
            $current_entry_nodes['entry-checkbox']->extSetAttribute('name', $filetype['format']);
            $category_row_count[$filetype['type']] ++;
        }
        else
        {
            $filetype_entries_nodes[$filetype['format']]['entry-label']->addContent(', .' . $filetype['extension'],
                    'after');
        }
    }

    $user_lock_override = $user->domainPermission($domain, 'perm_board_config_lock_override');

    foreach ($rows as $config_line)
    {
        if ($config_line['data_type'] == 'boolean')
        {
            if ($config_line['setting'] == 1)
            {
                if ($config_line['config_type'] == 'filetype_enable')
                {
                    $filetype_entries_nodes[$config_line['config_name']]['entry-checkbox']->extSetAttribute('checked',
                            'true');
                }
                else
                {
                    $config_element = $dom->getElementById($config_line['config_name']);

                    if (!is_null($config_element))
                    {
                        $config_element->extSetAttribute('checked', 'true');
                    }
                }
            }
        }
        else
        {
            if ($config_line['select_type'] == 1)
            {
                $config_element = $dom->getElementById($config_line['config_name'] . '_' . $config_line['setting']);
                $config_element->extSetAttribute('checked', 'true');
            }
            else
            {
                $config_element = $dom->getElementById($config_line['config_name']);

                if (!is_null($config_element))
                {
                    $config_element->extSetAttribute('value', $config_line['setting']);
                }
            }
        }

        if (!$defaults)
        {
            if ($config_line['edit_lock'] == 1 && !$user_lock_override)
            {
                $config_element->extSetAttribute('disabled', 'true');
            }
        }

        $config_element_lock = $dom->getElementById($config_line['config_name'] . '_lock');

        if (!is_null($config_element_lock))
        {
            if ($defaults)
            {
                if ($config_line['edit_lock'] == 1)
                {
                    $config_element_lock->extSetAttribute('checked', 'true');
                }
            }
            else
            {
                if ($config_line['select_type'] == 1)
                {
                    $blank_lock_element = $dom->getElementById($config_line['config_name'] . '_blank_lock');

                    if (!is_null($blank_lock_element))
                    {
                        $blank_lock_element->remove();
                    }
                }

                $config_element_lock->parentNode->remove();
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