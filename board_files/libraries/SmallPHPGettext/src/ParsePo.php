<?php

namespace SmallPHPGettext;

class ParsePo
{

    public function parseFile($file, $return_object = true)
    {
        $string = '';

        if(file_exists($file))
        {
            $string = file_get_contents($file);
        }

        return $this->parseString($string, $return_object = true);
    }

    public function parseString($string, $return_object = true)
    {
        $domain_array = ['name' => 'messages', 'headers' => [], 'contexts' => [], 'plural_rule' => ''];
        $asploded = explode('\n"', $string);

        foreach ($asploded as $line)
        {
            $line = trim($line);

            if (preg_match('#^"([^:]*):#ui', $line) === 1)
            {
                $domain_array = $this->parseHeaders($line, $domain_array);
            }
            else
            {
                $translation_lines = preg_split('#[\n\r]#', $line);
                $line_count = count($translation_lines);
                $translation = array();
                $context = 'default';

                for ($i = 0; $i < $line_count; ++ $i)
                {
                    $tline = $this->combineMultiline($translation_lines[$i]);

                    if ($tline === '' || $tline === 'msgid ""' || $tline === 'msgstr ""')
                    {
                        continue;
                    }
                    else
                    {
                        while (isset($translation_lines[$i + 1]) && stripos($translation_lines[$i + 1], '"') === 0)
                        {
                            $tline = trim($tline, '"') . trim($translation_lines[$i + 1], '"');
                            ++ $i;
                        }

                        $split_line = preg_split('#\s+#', $tline, 2);

                        if(!isset($split_line[1]))
                        {
                            continue;
                        }

                        $split_line[1] = trim($split_line[1], '"');
                        $translation = $this->parseMessage($split_line, $translation);
                        $translation = $this->parseComment($split_line, $translation);
                    }
                }

                $domain_array['contexts'][$context][$translation['msgid']] = $translation;
            }
        }

        return $domain_array;
    }

    private function parseMessage($split_line, $translation)
    {
        $message = $split_line[1];

        switch ($split_line[0])
        {
            case 'msgid':
                $translation['msgid'] = $message;
                break;

            case 'msgid_plural':
                $translation['msgid_plural'] = $message;
                break;

            case 'msgstr':
                $translation['msgstr'] = $message;
                break;

            case 'msgctxt':
                $context = $message;
                $domain_array['contexts'][$message] = array();
                break;

            default:
                if (strpos($split_line[0], 'msgstr[') === 0)
                {
                    preg_match('#\[(\d+)\]#u', $split_line[0], $match);
                    $index = intval($match[1]);

                    if($index === 0)
                    {
                        $translation['msgstr'] = $message;
                    }

                    $translation['plurals'][$index] = $message;
                }
        }

        return $translation;
    }

    private function parseComment($split_line, $translation)
    {
        if (substr($split_line[0], 0, 1) === '#')
        {
            $translation['comments'][$split_line[0]] = $split_line[1];
        }

        return $translation;
    }

    private function combineMultiline($line)
    {
        return preg_replace('#"[\r\n]*"#', '', $line);
    }

    private function parsePluralRule($header)
    {
        $plural_rule = preg_replace('@[^a-zA-Z0-9_:;\(\)\?\|\&=!<>+*/\%-]@', '', $header);
        $plural_rule = preg_replace('#(nplurals|plural|n)#u', '$$1', $plural_rule);
        $plural_rule .= ';';
        $plural_rule = str_replace('?', '?(', $plural_rule);
        $plural_rule = str_replace(':', '):(', $plural_rule);
        $open = substr_count($plural_rule, '?');
        $plural_rule = str_replace(';;', str_repeat(')', $open) . ';', $plural_rule);
        return $plural_rule;
    }

    private function parseHeaders($line, $domain_array)
    {
        $line = $this->combineMultiline($line);
        preg_match('#^"([^:]*):[\s]*(.*)$#ui', $line, $matches);
        $header_name = trim($matches[1]);
        $header = trim($matches[2]);
        $domain_array['headers'][$header_name] = $header;

        if (strcmp('Plural-Forms', $header_name) === 0)
        {
            $domain_array['plural_rule'] = $this->parsePluralRule($header);
        }

        return $domain_array;
    }
}