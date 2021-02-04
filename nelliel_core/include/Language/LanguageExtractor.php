<?php

namespace Nelliel\Language;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use Nelliel\Domains\Domain;
use PDO;

class LanguageExtractor
{
    private $domain;
    private $language;
    private $gettext_helpers;

    function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->language = new Language();
        $this->gettext_helpers = new \SmallPHPGettext\Helpers();
    }

    public function assemblePoString(string $default_textdomain, int $default_category)
    {
        $outputs = array();
        $strings = array();
        $strings = $this->parseHTMLFiles($strings, $default_category);
        $strings = $this->parseSiteFiles($strings, $default_category);
        $strings = $this->parseDatabaseEntries($strings, $default_category);

        foreach ($strings as $category => $entries)
        {
            $output_category = $this->gettext_helpers->categoryToString($category);

            if (!isset($outputs[$output_category]))
            {
                $outputs[$output_category] = array();
            }

            foreach ($entries as $msgid => $data)
            {
                $output_string = '';

                if (isset($data['comments']))
                {
                    foreach ($data['comments'] as $comment => $type)
                    {
                        $output_string .= $type . ' ' . $comment . "\n";
                    }
                }

                if (isset($data['msgctxt']))
                {
                    $output_string .= 'msgctxt "' . $this->gettext_helpers->stringToPo($data['msgctxt']) . '"' . "\n";
                }

                if (isset($data['msgid']))
                {

                    $message = $this->gettext_helpers->stringToPo($data['msgid']);
                    $output_string .= 'msgid "' . $this->wrapLine($message, 79, '') . '"' . "\n";
                }

                if (isset($data['msgid_plural']))
                {
                    $output_string .= 'msgid_plural "' . $this->gettext_helpers->stringToPo($data['msgid_plural']) . '"' .
                            "\n";
                    $output_string .= 'msgstr[0] ""' . "\n";
                    $output_string .= 'msgstr[1] ""' . "\n";
                }
                else
                {
                    $output_string .= 'msgstr ""' . "\n";
                }

                $output_textdomain = ($data['domain']) ?? $default_textdomain;

                if (!isset($outputs[$output_category][$output_textdomain]))
                {
                    $outputs[$output_category][$output_textdomain] = $this->assembleHeaders();
                }

                $outputs[$output_category][$output_textdomain] .= "\n" . $output_string;
            }
        }

        return $outputs;
    }

    private function assembleHeaders()
    {
        $headers = '';
        $headers .= '# SOME DESCRIPTIVE TITLE.' . "\n";
        $headers .= '# Copyright (C) ' . NELLIEL_COPYRIGHT . "\n";
        $headers .= '# This file is distributed under the same license as the ' . NELLIEL_PACKAGE . ' package.' . "\n";
        $headers .= '# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.' . "\n";
        $headers .= '#' . "\n";
        $headers .= '#, fuzzy' . "\n";
        $headers .= 'msgid ""' . "\n";
        $headers .= 'msgstr ""' . "\n";
        $headers .= '"Project-Id-Version: Nelliel ' . NELLIEL_VERSION . '\n"' . "\n";
        $headers .= '"Report-Msgid-Bugs-To: \n"' . "\n";
        $headers .= '"POT-Creation-Date: ' . date("Y-m-d H:i:s T O") . '\n"' . "\n";
        $headers .= '"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"' . "\n";
        $headers .= '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"' . "\n";
        $headers .= '"Language-Team: LANGUAGE <LL@li.org>\n"' . "\n";
        $headers .= '"Language: \n"' . "\n";
        $headers .= '"MIME-Version: 1.0\n"' . "\n";
        $headers .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $headers .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
        $headers .= '"Plural-Forms: nplurals=2; plural=n != 1;\n"';
        $headers .= "\n";
        return $headers;
    }

    private function parseSiteFiles(array $strings, int $default_category)
    {
        $file_handler = nel_utilities()->fileHandler();
        $php_files = $file_handler->recursiveFileList(NEL_BASE_PATH, 0);
        $php_files = array_merge($php_files, $file_handler->recursiveFileList(NEL_INCLUDE_PATH));

        foreach ($php_files as $file)
        {
            $file_id = str_replace(NEL_BASE_PATH, '', $file);

            if ($file->getExtension() !== 'php' && $file->getExtension() !== 'inc')
            {
                continue;
            }

            // New stuff starts here
            $contents = file_get_contents($file);
            $parsed_tokens = token_get_all($contents);
            $function_found = false;
            $last_not_token = '';
            $entry = array();
            $counter = 1;

            foreach ($parsed_tokens as $token)
            {
                if (is_string($token))
                {
                    if ($function_found && $token === ')')
                    {
                        $last_not_token = '';
                        $function_found = false;

                        if (!empty($entry) && isset($entry[1]))
                        {
                            $this->addPHPMatch($strings, $entry, $default_category);
                        }

                        $entry = array();
                        $counter = 1;
                    }
                    else
                    {
                        $last_not_token = $token;
                    }

                    continue;
                }

                $token_name = token_name($token[0]);

                if ($token_name === 'T_STRING' || $token_name === 'T_CONSTANT_ENCAPSED_STRING' ||
                        $token_name === 'T_LNUMBER')
                {
                    if (!$function_found)
                    {
                        $matches = array();

                        if (preg_match('/^_([a-z]*?)gettext$/u', $token[1], $matches))
                        {
                            $entry['file'] = $file_id;
                            $entry['prefix'] = $matches[1];
                            $entry['line_number'] = $token[2];
                            $function_found = true;
                        }
                    }
                    else
                    {
                        if ($last_not_token === '(' || $last_not_token === ',')
                        {
                            $entry[$counter] = preg_replace('/^("|\')|("|\')\s*?$/u', '', $token[1]);
                            $counter ++;
                        }
                    }
                }
            }
        }

        return $strings;
    }

    private function getCategoryValue($category)
    {
        if (is_numeric($category))
        {
            $value = intval($category);
        }
        else
        {
            $value = $this->gettext_helpers->categoryFromString($category);
        }

        if ($value !== false && $value > 1 && $value < 7)
        {
            return $value;
        }

        return false;
    }

    private function addPHPMatch(array &$strings, array $entry, int $default_category)
    {
        $category = null;
        $msgid = null;
        $msgid_plural = null;
        $domain = null;
        $context = null;

        if ($entry['prefix'] === '')
        {
            $msgid = $entry[1];
        }
        else if ($entry['prefix'] === 'n')
        {
            $msgid = $entry[1];
            $msgid_plural = $entry[2];
        }
        else if ($entry['prefix'] === 'p')
        {
            $context = $entry[1];
            $msgid = $entry[2];
        }
        else if ($entry['prefix'] === 'np')
        {
            $context = $entry[1];
            $msgid = $entry[2];
            $msgid_plural = $entry[3];
        }
        else if ($entry['prefix'] === 'p')
        {
            $domain = $entry[1];
            $msgid = $entry[2];
        }
        else if ($entry['prefix'] === 'np')
        {
            $domain = $entry[1];
            $msgid = $entry[2];
            $msgid_plural = $entry[3];
        }
        else if ($entry['prefix'] === 'dc')
        {
            $domain = $entry[1];
            $msgid = $entry[2];
            $category = $entry[3];
        }
        else if ($entry['prefix'] === 'dcn')
        {
            $domain = $entry[1];
            $msgid = $entry[2];
            $msgid_plural = $entry[3];
            $category = $entry[5];
        }

        if (!is_null($category))
        {
            $value = $this->getCategoryValue($category);
            $category = ($value !== false) ? $value : $default_category;
            $strings[$category][$msgid]['category'] = $category;
        }
        else
        {
            $category = $default_category;
        }

        if (!is_null($domain))
        {
            $strings[$category][$msgid]['domain'] = $domain;
        }

        $strings[$category][$msgid]['msgid'] = $msgid;

        if (!is_null($msgid_plural))
        {
            $strings[$category][$msgid]['msgid_plural'] = $msgid_plural;
        }

        $location = $entry['file'] . ':' . $entry['line_number'];
        $strings[$category][$msgid]['comments'][$location] = '#:';

        if (preg_match('/%[-\+ 0\'bcdeEfFgGosuxX]/', $msgid) === 1 ||
                preg_match('/%[-\+ 0\'bcdeEfFgGosuxX]/', $msgid_plural) === 1)
        {
            $strings[$category][$msgid]['comments']['php-format'] = '#,';
        }
    }

    private function parseHTMLFiles(array $strings, string $default_category)
    {
        $file_handler = nel_utilities()->fileHandler();
        $html_files = $file_handler->recursiveFileList(NEL_TEMPLATES_FILES_PATH . 'nelliel_basic/'); // TODO: Be able to parse custom template sets
        $html_files = array_merge($html_files, $file_handler->recursiveFileList(NEL_INCLUDE_PATH));
        $render = new \Nelliel\Render\RenderCoreDOM();

        foreach ($html_files as $file)
        {
            $file_id = str_replace(NEL_BASE_PATH, '', $file);

            if ($file->getExtension() !== 'html')
            {
                continue;
            }

            $dom = $render->newDOMDocument();
            $template = $render->loadTemplateFromFile($file->getPathname());
            $render->loadDOMFromTemplate($dom, $template);
            $content_node_list = $dom->getElementsByAttributeName('data-i18n');
            $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

            foreach ($attribute_node_list as $node)
            {
                $split_attribute = explode('|', $node->getAttribute('data-i18n-attributes'), 2);

                if ($split_attribute[0] === 'gettext')
                {
                    $attribute_list = explode(',', $split_attribute[1]);

                    foreach ($attribute_list as $attribute_name)
                    {
                        $msgid = $node->getAttribute(trim($attribute_name));

                        if ($msgid !== '')
                        {
                            $location = $file_id;
                            $strings[$default_category][$msgid]['msgid'] = $msgid;
                            $strings[$default_category][$msgid]['comments'][$location] = '#:';
                        }
                    }
                }
            }

            foreach ($content_node_list as $node)
            {
                if ($node->getAttribute('data-i18n') === 'gettext')
                {
                    $msgid = $node->getContent();

                    if ($msgid !== '')
                    {
                        $location = $file_id;
                        $strings[$default_category][$msgid]['msgid'] = $msgid;
                        $strings[$default_category][$msgid]['comments'][$location] = '#:';
                    }
                }
            }
        }

        return $strings;
    }

    private function parseDatabaseEntries(array $strings, string $default_category)
    {
        $database = $this->domain->database();
        $filetype_labels = $database->executeFetchAll('SELECT "type_label" FROM "' . NEL_FILETYPES_TABLE . '"',
                PDO::FETCH_COLUMN);

        foreach ($filetype_labels as $label)
        {
            if ($label !== '' && !is_null($label))
            {
                $msgid = $label;
                $strings[$default_category][$msgid]['msgid'] = $label;
                $strings[$default_category][$msgid]['comments']['(Database) Table: ' . NEL_FILETYPES_TABLE .
                        ' | Column: type_label'] = '#:';
            }
        }

        $permission_descriptions = $database->executeFetchAll(
                'SELECT "perm_description" FROM "' . NEL_PERMISSIONS_TABLE . '"', PDO::FETCH_COLUMN);

        foreach ($permission_descriptions as $description)
        {
            if ($description !== '' && !is_null($description))
            {
                $msgid = $description;
                $strings[$default_category][$msgid]['msgid'] = $description;
                $strings[$default_category][$msgid]['comments']['(Database) Table: ' . NEL_PERMISSIONS_TABLE .
                        ' | Column: perm_description'] = '#:';
            }
        }

        $setting_descriptions = $database->executeFetchAll('SELECT "setting_description" FROM "' . NEL_SETTINGS_TABLE . '"',
                PDO::FETCH_COLUMN);

        foreach ($setting_descriptions as $label)
        {
            if ($label !== '' && !is_null($label))
            {
                $msgid = $label;
                $strings[$default_category][$msgid]['msgid'] = $label;
                $strings[$default_category][$msgid]['comments']['(Database) Table: ' . NEL_SETTINGS_TABLE .
                        ' | Column: setting_description'] = '#:';
            }
        }

        return $strings;
    }

    private function wrapLine(string $line, int $width, string $break)
    {
        if (utf8_strlen($line) <= $width)
        {
            return $line;
        }

        $words = explode(' ', $line);

        if ($words === false)
        {
            return $line;
        }

        $final_string = '';
        $lines = [0 => ''];
        $index = 0;

        foreach ($words as $word)
        {
            if (utf8_strlen($lines[$index] . ' ' . $word) > $width && utf8_strlen($word) <= $width)
            {
                $index ++;
                $lines[$index] = $word;
            }
            else
            {
                if (!empty($lines[$index]))
                {
                    $lines[$index] .= ' ';
                }

                $lines[$index] .= $word;
            }
        }

        return implode(' "' . "\n" . '"', $lines);
    }
}