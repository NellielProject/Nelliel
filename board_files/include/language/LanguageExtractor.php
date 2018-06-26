<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

class LanguageExtractor
{

    public function assemblePoString()
    {
        $output_string = '';
        $output_array = array();
        $output_string .= $this->assembleHeaders();
        $strings = array();
        $strings = $this->parseHTMLFiles($strings);
        $strings = $this->parseSiteFiles($strings);

        foreach ($strings as $context => $message_data)
        {
            foreach ($message_data as $msgid => $data)
            {
                $output_string .= "\n";
                $comments = array('#' => array(), '#:' => array(), '#,' => array());

                foreach ($data['comments'] as $comment => $type)
                {
                    $comments[$type][] = $comment;
                }

                if(!empty($comments['#']))
                {
                    $output_string .= '# ' . implode("\n# ", $comments['#']) . "\n";
                }

                if(!empty($comments['#:']))
                {
                    $output_string .= '#: ' . implode("\n#: ", $comments['#:']) . "\n";
                }

                if(!empty($comments['#,']))
                {
                    $output_string .= '#, ' . implode("\n#, ", $comments['#,']) . "\n";
                }

                if($context !== 'default')
                {
                    $output_string .= 'msgctxt "' . $context . '"' . "\n";
                }

                $output_string .= 'msgid "' . $msgid . '"' . "\n";

                if (isset($data['msgid_plural']))
                {
                    $output_string .= 'msgid_plural "' . $data['msgid_plural'] . '"' . "\n";
                    $output_string .= 'msgstr[0] ""' . "\n";
                    $output_string .= 'msgstr[1] ""' . "\n";
                }
                else
                {
                    $output_string .= 'msgstr ""' . "\n";
                }
            }
        }

        return $output_string;
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

        return $headers;
    }

    private function addIfNotDuplicate(&$strings, $message, $plural)
    {
        if (!in_array($message, $strings))
        {
            $strings[] = $message;
        }
    }

    private function parseSiteFiles($strings = array())
    {
        $file_handler = new \Nelliel\FileHandler();
        $php_files = $file_handler->recursiveFileList(BASE_PATH,  false, ['php']);

        foreach ($php_files as $file)
        {
            $contents = file_get_contents($file);
            preg_match_all('#_gettext\(["\'](.*?)["\']\)#u', $contents, $matches, PREG_SET_ORDER);
            $count = count($matches);

            foreach ($matches as $set)
            {
                $context = 'default';
                $strings[$context][$set[1]]['comments'][str_replace(BASE_PATH, '', $file)] = '#:';
                $strings[$context][$set[1]]['comments']['php-format'] = '#,';
            }

            $contents = file_get_contents($file);
            preg_match_all('#_ngettext\(["\'](.*?)["\'][\s]*?,[\s]*?["\'](.*?)["\'].*?\)#u', $contents, $matches, PREG_SET_ORDER);
            $count = count($matches);

            foreach ($matches as $set)
            {
                $context = 'default';
                $strings[$context][$set[1]]['msgid_plural'] = $set[2];
                $strings[$context][$set[1]]['comments'][str_replace(BASE_PATH, '', $file)] = '#:';
                $strings[$context][$set[1]]['comments']['php-format'] = '#,';
            }
        }

        return $strings;
    }

    private function parseHTMLFiles($strings = array())
    {
        $file_handler = new \Nelliel\FileHandler();
        $html_files = $file_handler->recursiveFileList(BASE_PATH,  false, ['html']);
        $render = new \NellielTemplates\RenderCore();

        foreach ($html_files as $file)
        {
            $dom = $render->newDOMDocument();
            $render->loadTemplateFromFile($dom, $file);
            $content_node_list = $dom->getElementsByAttributeName('data-i18n');
            $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

            foreach ($attribute_node_list as $node)
            {
                if ($node->getAttribute('data-i18n') === 'gettext')
                {
                    $attribute_list = explode(',', $node->getAttribute('data-i18n-attributes'));

                    foreach ($attribute_list as $attribute_name)
                    {
                        $msgid = $node->getAttribute(trim($attribute_name));

                        if ($msgid !== '')
                        {
                            $context = 'default';
                            $strings[$context][$msgid]['comments'][str_replace(BASE_PATH, '', $file)] = '#:';
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
                        $context = 'default';
                        $strings[$context][$msgid]['comments'][str_replace(BASE_PATH, '', $file)] = '#:';
                    }
                }
            }
        }

        return $strings;
    }
}