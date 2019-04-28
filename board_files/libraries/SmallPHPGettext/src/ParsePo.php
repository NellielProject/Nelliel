<?php

namespace SmallPHPGettext;

class ParsePo
{
	private $helpers;

	function __construct()
	{
		$this->helpers = new Helpers();
	}

    public function parseFile(string $file, string $domain = 'messages')
    {
        $string = '';

        if(file_exists($file))
        {
            $string = file_get_contents($file);
        }

        return $this->parseString($string, $domain);
    }

    public function parseString(string $string, string $domain)
    {
        $domain_array = ['domain' => $domain, 'headers' => array(), 'translations' => array(), 'plural_rule' => ''];
        $asploded = explode("\n", $string);
        $translation = array();
        $entry = array();

        foreach ($asploded as $line)
        {
            $line = trim($line);
            $first_character = substr($line, 0,1);

            // Empty lines usually occur between info blocks
            if(empty($line))
            {
                if(!empty($entry))
                {
                    $translation = $this->parseMessage($entry, $translation);
                }

                // If there is a valid translation in progress, store it
                if(!empty($translation) && isset($translation['msgid']))
                {
                    $domain_array = $this->storeTranslation($translation, $domain_array);
                }

                $translation = array();
                $entry = array();
                continue;
            }

            $split_line = preg_split('/\s+/u', $line, 2, PREG_SPLIT_NO_EMPTY);
            $split_line[0] = (!empty($split_line[0])) ? $this->helpers->unquoteLine(trim($split_line[0])) : '';
            $split_line[1] = (!empty($split_line[1])) ? $this->helpers->unquoteLine(trim($split_line[1])) : '';

            if($first_character === '"') // Check for header lines or partial strings
            {
                if (preg_match('/^[^:]*:/u', $split_line[0]) === 1)
                {
                    $domain_array = $this->parseHeaders($split_line, $domain_array);
                }
                else
                {
                    $entry[1] .= $split_line[0];
                }
            }
            else
            {
                if($first_character === '#') // Check for comment lines
                {
                    $translation = $this->parseComment($split_line, $translation);
                }
                else if($first_character === 'm') // Check for the start of msg lines
                {
                    // We have finished combining the message line and store it before starting a new one
                    if(!empty($entry))
                    {
                        $translation = $this->parseMessage($entry, $translation);
                    }

                    $entry[0] = $split_line[0];
                    $entry[1] = $split_line[1];
                }
            }
        }

        return $domain_array;
    }

    private function parseMessage(array $split_line, array $translation)
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
                $translation['msgctxt'] = $message;
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

    private function parseComment(array $split_line, array $translation)
    {
        $translation['comments'][$split_line[0]][] = $split_line[1];
        return $translation;
    }

    private function combineMultiline(string $line)
    {
        return preg_replace('/"[\r\n]"/', '', $line);
    }

    private function parsePluralRule(string $header)
    {
        $plural_rule = preg_replace('/[^a-zA-Z0-9_:;\(\)\?\|\&=!<>+*\/\%-]/u', '', $header);
        $plural_rule = preg_replace('/(nplurals|plural|n)/u', '$$1', $plural_rule);
        $plural_rule .= ';';
        $plural_rule = str_replace('?', '?(', $plural_rule);
        $plural_rule = str_replace(':', '):(', $plural_rule);
        $open = substr_count($plural_rule, '?');
        $plural_rule = str_replace(';;', str_repeat(')', $open) . ';', $plural_rule);
        return $plural_rule;
    }

    private function parseHeaders(array $split_line, array $domain_array)
    {
        $header_name = trim(trim($split_line[0]), ':');
        $header_info = trim(trim($split_line[1]), '\n');
        $domain_array['headers'][$header_name] = $header_info;

        if (strcmp('Plural-Forms', $header_name) === 0)
        {
            $domain_array['plural_rule'] = $this->parsePluralRule($header_info);
        }

        return $domain_array;
    }

    private function storeTranslation(array $translation, array $domain_array)
    {
        $id = $translation['msgid'];

        if(isset($translation['msgctxt']))
        {
            $current_translation = array();
            $new_translation = ['contexts' => [$translation['msgctxt'] => $translation]];

            if(isset($domain_array['translations'][$id]))
            {
                $current_translation = $domain_array['translations'][$id];
            }

            $domain_array['translations'][$id] = array_merge($current_translation, $new_translation);
        }
        else
        {
            $domain_array['translations'][$id] = $translation;
        }

        return $domain_array;
    }
}