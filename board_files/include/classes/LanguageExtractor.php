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

        foreach ($strings as $context)
        {
            foreach ($context as $msgid => $data)
            {
                $output_string .= "\n";

                foreach($data['comments'] as $comment => $type)
                {
                    $output_string .= $type . ' ' . $comment . "\n";
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

    public function recursiveFileList($path, $include_directories = false, $valid_extensions = array())
    {
        $file_list = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($iterator as $file)
        {
            $path_info = pathinfo('_' . $file->getPathname()); // Underscore is added as a workaround for pathinfo not handling Unicode properly

            if (!$include_directories && $file->isDir())
            {
                continue;
            }

            if (isset($path_info['extension']) && !empty($valid_extensions))
            {
                if (!in_array($path_info['extension'], $valid_extensions))
                {
                    continue;
                }
            }

            $file_list[] = $file->getPathname();
        }

        return $file_list;
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
        $php_files = $this->recursiveFileList(BASE_PATH, false, ['php']);

        foreach ($php_files as $file)
        {
            $contents = file_get_contents($file);
            preg_match_all('#nel_stext\(["\'](.*?)["\']\)#u', $contents, $matches, PREG_SET_ORDER);
            $count = count($matches);

            foreach($matches as $set)
            {
                $context = 'default';
                $strings[$context][$set[1]]['comments'][str_replace(BASE_PATH, '', $file)] = '#:';
            }

            $contents = file_get_contents($file);
            preg_match_all('#nel_ptext\(["\'](.*?)["\'][\s]*?,[\s]*?["\'](.*?)["\'].*?\)#u', $contents, $matches, PREG_SET_ORDER);
            $count = count($matches);

            foreach($matches as $set)
            {
                $context = 'default';
                $strings[$context][$set[1]]['msgid_plural'] = $set[2];
                $strings[$context][$set[1]]['comments'][str_replace(BASE_PATH, '', $file)] = '#:';
            }
        }

        return $strings;
    }

    private function parseHTMLFiles($strings = array())
    {
        $html_files = $this->recursiveFileList(TEMPLATE_PATH, false, ['html']);
        $render = new \NellielTemplates\RenderCore();

        foreach ($html_files as $file)
        {
            $dom = $render->newDOMDocument();
            $render->loadTemplateFromFile($dom, $file);
            $content_node_list = $dom->getElementsByAttributeName('data-i18n');
            $attribute_node_list = $dom->getElementsByAttributeName('data-i18n-attributes');

            foreach ($attribute_node_list as $node)
            {
                if ($node->getAttribute('data-i18n') === 'neltext')
                {
                    $attribute_list = explode(',', $node->getAttribute('data-i18n-attributes'));

                    foreach ($attribute_list as $attribute_name)
                    {
                        $msgid = trim($node->getAttribute(trim($attribute_name)));

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
                if ($node->getAttribute('data-i18n') === 'neltext')
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