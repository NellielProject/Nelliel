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

        foreach ($strings as $string)
        {
            $output_string .= "\n";
            $output_string .= 'msgid "' . $string . '"' . "\n";
            $output_string .= 'msgstr ""' . "\n";
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
        $headers .= '"Language: en_US\n"' . "\n";
        $headers .= '"MIME-Version: 1.0\n"' . "\n";
        $headers .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $headers .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
        $headers .= '"Plural-Forms: nplurals=2; plural=n == 1 ? 0 : 1"' . "\n";

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

    private function addIfNotDuplicate(&$strings, $message)
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
            preg_match('#nel_stext\(["\'](.*?)["\']\)#u', $contents, $matches);
            $count = count($matches);

            for ($i = 1; $i < $count; ++ $i)
            {
                $this->addIfNotDuplicate($strings, $matches[$i]);
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
                        $message = trim($node->getAttribute(trim($attribute_name)));
                        $this->addIfNotDuplicate($strings, $message);
                    }
                }
            }

            foreach ($content_node_list as $node)
            {
                if ($node->getAttribute('data-i18n') === 'neltext')
                {
                    $message = $node->getContent();

                    if ($message !== '')
                    {
                        $this->addIfNotDuplicate($strings, $message);
                    }
                }
            }
        }

        return $strings;
    }
}