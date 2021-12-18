<?php
declare(strict_types = 1);

namespace Nelliel\Language;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use SmallPHPGettext\SmallPHPGettext;
use Mustache_Parser;
use Mustache_Tokenizer;
use PDO;

class LanguageExtractor
{
    private $domain;
    private $gettext;

    function __construct(Domain $domain, SmallPHPGettext $gettext)
    {
        $this->domain = $domain;
        $this->gettext = $gettext;
    }

    public function assemblePoString(string $default_textdomain, int $default_category)
    {
        $outputs = array();
        $strings = array();
        $strings = array_replace_recursive($strings, $this->parseSiteFiles($default_category));
        $strings = array_replace_recursive($strings, $this->parseMustacheTemplates($default_category));
        $strings = array_replace_recursive($strings, $this->parseHTMLFiles($default_category));
        $strings = array_replace_recursive($strings, $this->parseDatabaseEntries($default_category));

        foreach ($strings as $category => $entries) {
            $output_category = $category;

            if (!isset($outputs[$output_category])) {
                $outputs[$output_category] = array();
            }

            foreach ($entries as $data) {
                $output_string = '';

                if (isset($data['comments'])) {
                    foreach ($data['comments'] as $comment => $type) {
                        $output_string .= $type . ' ' . $comment . "\n";
                    }
                }

                if (isset($data['msgctxt'])) {
                    $output_string .= 'msgctxt "' . $this->gettext->poEncode($data['msgctxt']) . '"' . "\n";
                }

                if (isset($data['msgid'])) {

                    $message = $this->gettext->poEncode($data['msgid']);
                    $output_string .= 'msgid "' . $this->wrapLine($message, 79, '') . '"' . "\n";
                }

                if (isset($data['msgid_plural'])) {
                    $output_string .= 'msgid_plural "' . $this->gettext->poEncode($data['msgid_plural']) . '"' . "\n";
                    $output_string .= 'msgstr[0] ""' . "\n";
                    $output_string .= 'msgstr[1] ""' . "\n";
                } else {
                    $output_string .= 'msgstr ""' . "\n";
                }

                $output_textdomain = ($data['domain']) ?? $default_textdomain;

                if (!isset($outputs[$output_category][$output_textdomain])) {
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

    private function parseSiteFiles(int $default_category): array
    {
        $entries = array();
        $file_handler = nel_utilities()->fileHandler();
        $php_files = $file_handler->recursiveFileList(NEL_BASE_PATH, 0);
        $php_files = array_merge($php_files, $file_handler->recursiveFileList(NEL_INCLUDE_PATH));

        foreach ($php_files as $file) {
            $file_id = utf8_str_replace(NEL_BASE_PATH, '', $file);

            if ($file->getExtension() !== 'php' && $file->getExtension() !== 'inc') {
                continue;
            }

            // New stuff starts here
            $contents = file_get_contents($file->getPathname());
            $parsed_tokens = token_get_all($contents);
            $function_found = false;
            $last_not_token = '';
            $entry = array();
            $counter = 1;

            foreach ($parsed_tokens as $token) {
                if (is_string($token)) {
                    if ($function_found && $token === ')') {
                        $last_not_token = '';
                        $function_found = false;

                        if (!empty($entry) && isset($entry[1])) {
                            $entry_data = $this->getFunctionParameters($entry, $default_category);
                            $current_data = $entries[$entry_data['category']][$entry_data['msgid']] ?? array();
                            $entries[$entry_data['category']][$entry_data['msgid']] = array_replace_recursive(
                                $current_data, $entry_data);
                        }

                        $entry = array();
                        $counter = 1;
                    } else {
                        $last_not_token = $token;
                    }

                    continue;
                }

                $token_name = token_name($token[0]);

                if ($token_name === 'T_STRING' || $token_name === 'T_CONSTANT_ENCAPSED_STRING' ||
                    $token_name === 'T_LNUMBER') {
                    if (!$function_found) {
                        $matches = array();

                        if (preg_match('/^_*?(__|np|n|p|dn|dcn|dc|d)*?(?:gettext)*?$/u', $token[1], $matches)) {
                            $entry['file'] = $file_id;
                            $entry['prefix'] = $matches[1] ?? '';
                            $entry['line_number'] = $token[2];
                            $function_found = true;
                        }
                    } else {
                        if ($last_not_token === '(' || $last_not_token === ',') {
                            $entry[$counter] = preg_replace('/^("|\')|("|\')\s*?$/u', '', $token[1]);
                            $counter ++;
                        }
                    }
                }
            }
        }

        return $entries;
    }

    private function getFunctionParameters(array $entry, int $default_category)
    {
        $data = array();
        $category = $default_category;
        $msgid = '';
        $msgid_plural = '';
        $domain = '';
        $context = '';

        if ($entry['prefix'] === '') {
            $msgid = $entry[1];
        } else if ($entry['prefix'] === 'n') {
            $msgid = $entry[1];
            $msgid_plural = $entry[2];
        } else if ($entry['prefix'] === 'p') {
            $context = $entry[1];
            $msgid = $entry[2];
        } else if ($entry['prefix'] === 'np') {
            $context = $entry[1];
            $msgid = $entry[2];
            $msgid_plural = $entry[3];
        } else if ($entry['prefix'] === 'd') {
            $domain = $entry[1];
            $msgid = $entry[2];
        } else if ($entry['prefix'] === 'dn') {
            $domain = $entry[1];
            $msgid = $entry[2];
            $msgid_plural = $entry[3];
        } else if ($entry['prefix'] === 'dc') {
            $domain = $entry[1];
            $msgid = $entry[2];
            $category = $entry[3];
        } else if ($entry['prefix'] === 'dcn') {
            $domain = $entry[1];
            $msgid = $entry[2];
            $msgid_plural = $entry[3];
            $category = $entry[5];
        }

        $data['category'] = $category;
        $data['msgid'] = $msgid;

        if ($msgid_plural !== '') {
            $data['msgid_plural'] = $msgid_plural;
        }

        if ($domain !== '') {
            $data['domain'] = $domain;
        }

        if ($context !== '') {
            $data['context'] = $context;
        }

        $location = $entry['file'] . ':' . $entry['line_number'];
        $data['comments'][$location] = '#:';

        if (preg_match('/%[-\+ 0\'bcdeEfFgGosuxX]/', $msgid) === 1 ||
            preg_match('/%[-\+ 0\'bcdeEfFgGosuxX]/', $msgid_plural) === 1) {
            $data['comments']['php-format'] = '#,';
        }

        return $data;
    }

    private function parseMustacheTemplates(int $default_category): array
    {
        $entries = array();
        $file_handler = nel_utilities()->fileHandler();
        $template_files = $file_handler->recursiveFileList(NEL_TEMPLATES_FILES_PATH . 'nelliel_basic/');
        $template_files = array_merge($template_files, $file_handler->recursiveFileList(NEL_INCLUDE_PATH));

        foreach ($template_files as $file) {
            if ($file->getExtension() !== 'html') {
                continue;
            }

            $tokenizer = new Mustache_Tokenizer();
            $parser = new Mustache_Parser();
            $file_id = utf8_str_replace(NEL_BASE_PATH, '', $file->getPathname());
            $template = file_get_contents($file->getPathname());
            $entry = array();

            foreach ($parser->parse($tokenizer->scan($template)) as $node) {
                if ($node['type'] === Mustache_Tokenizer::T_SECTION && $node['name'] === 'gettext') {
                    $start = $node[Mustache_Tokenizer::INDEX];
                    $end = $node[Mustache_Tokenizer::END];
                    $msgid = utf8_substr($template, $start, $end - $start);
                    $entry['msgid'] = $file_id;
                    $entry['file'] = $file_id;
                    $entry['prefix'] = ''; // TODO: Change this handler when we add more support
                    $entry['line_number'] = $node['line'];
                    $location = $entry['file'] . ':' . ($entry['line_number'] + 1);
                    $entry['comments'][$location] = '#:';
                    $entries[$default_category][$msgid] = $entry;
                }
            }
        }

        return $entries;
    }

    private function parseHTMLFiles(int $default_category): array
    {
        $entries = array();
        $file_handler = nel_utilities()->fileHandler();
        $html_files = $file_handler->recursiveFileList(NEL_TEMPLATES_FILES_PATH . 'nelliel_basic/');
        $html_files = array_merge($html_files, $file_handler->recursiveFileList(NEL_INCLUDE_PATH));
        $render = new \Nelliel\Render\RenderCoreDOM();

        foreach ($html_files as $file) {
            $file_id = utf8_str_replace(NEL_BASE_PATH, '', $file->getPathname());

            if ($file->getExtension() !== 'html') {
                continue;
            }

            $dom = $render->newDOMDocument();
            $template = $render->loadTemplateFromFile($file->getPathname());
            $render->loadDOMFromTemplate($dom, $template);
            $content_node_list = $dom->getElementsByAttributeName('data-i18n');
            $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

            foreach ($content_node_list as $node) {
                $msgid = $node->getContent();

                if ($msgid !== '') {
                    $location = $file_id;
                    $entries[$default_category][$msgid]['msgid'] = $msgid;
                    $entries[$default_category][$msgid]['comments'][$location] = '#:';
                }
            }

            foreach ($attribute_node_list as $node) {
                $attribute_list = explode('|', $node->getAttribute('data-i18n-attributes'));

                foreach ($attribute_list as $attribute_name) {
                    $msgid = $node->getAttribute(trim($attribute_name));

                    if ($msgid !== '') {
                        $location = $file_id;
                        $entries[$default_category][$msgid]['msgid'] = $msgid;
                        $entries[$default_category][$msgid]['comments'][$location] = '#:';
                    }
                }
            }
        }

        return $entries;
    }

    private function parseDatabaseEntries(int $default_category)
    {
        $entries = array();
        $database = $this->domain->database();
        $filetype_labels = $database->executeFetchAll('SELECT "type_label" FROM "' . NEL_FILETYPES_TABLE . '"',
            PDO::FETCH_COLUMN);

        foreach ($filetype_labels as $label) {
            if ($label !== '' && !is_null($label)) {
                $msgid = $label;
                $entries[$default_category][$msgid]['msgid'] = $label;
                $entries[$default_category][$msgid]['comments']['(Database) Table: ' . NEL_FILETYPES_TABLE .
                    ' | Column: type_label'] = '#:';
            }
        }

        $permission_descriptions = $database->executeFetchAll(
            'SELECT "description" FROM "' . NEL_PERMISSIONS_TABLE . '"', PDO::FETCH_COLUMN);

        foreach ($permission_descriptions as $description) {
            if ($description !== '' && !is_null($description)) {
                $msgid = $description;
                $entries[$default_category][$msgid]['msgid'] = $description;
                $entries[$default_category][$msgid]['comments']['(Database) Table: ' . NEL_PERMISSIONS_TABLE .
                    ' | Column: description'] = '#:';
            }
        }

        $setting_descriptions = $database->executeFetchAll(
            'SELECT "setting_description" FROM "' . NEL_SETTINGS_TABLE . '"', PDO::FETCH_COLUMN);

        foreach ($setting_descriptions as $label) {
            if ($label !== '' && !is_null($label)) {
                $msgid = $label;
                $entries[$default_category][$msgid]['msgid'] = $label;
                $entries[$default_category][$msgid]['comments']['(Database) Table: ' . NEL_SETTINGS_TABLE .
                    ' | Column: setting_description'] = '#:';
            }
        }

        return $entries;
    }

    private function wrapLine(string $line, int $width, string $break)
    {
        if (utf8_strlen($line) <= $width) {
            return $line;
        }

        $words = explode(' ', $line);

        if ($words === false) {
            return $line;
        }

        $final_string = '';
        $lines = [0 => ''];
        $index = 0;

        foreach ($words as $word) {
            if (utf8_strlen($lines[$index] . ' ' . $word) > $width && utf8_strlen($word) <= $width) {
                $index ++;
                $lines[$index] = $word;
            } else {
                if (!empty($lines[$index])) {
                    $lines[$index] .= ' ';
                }

                $lines[$index] .= $word;
            }
        }

        return implode(' "' . "\n" . '"', $lines);
    }
}