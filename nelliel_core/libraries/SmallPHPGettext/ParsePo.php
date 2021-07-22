<?php
declare(strict_types = 1);

namespace SmallPHPGettext;

class ParsePo
{
    use Helpers;

    function __construct()
    {
    }

    public function parseFile(string $file, string $domain = 'messages'): array
    {
        $string = '';

        if (file_exists($file))
        {
            $string = file_get_contents($file);
        }

        return $this->parseString($string, $domain);
    }

    public function parseString(string $string, string $domain = 'messages'): array
    {
        $domain_array = ['domain' => $domain, 'headers' => array(), 'translations' => array(), 'plural_rule' => ''];
        $asploded = explode("\n", $string);
        $translation = array();
        $entry = array();
        // Mark headers as processed once they're done so normal lines aren't assumed to be a header
        $headers_in_progress = false;
        $headers_done = false;
        $header_entry = array();

        foreach ($asploded as $line)
        {
            $line = trim($line);
            $first_character = substr($line, 0, 1);

            // Empty lines usually occur between info blocks
            if (empty($line))
            {
                if (!empty($entry))
                {
                    $translation = $this->parseMessage($entry, $translation);
                }

                // If there is a valid translation in progress, store it
                if (!empty($translation) && isset($translation['msgid']))
                {
                    $domain_array = $this->storeTranslation($translation, $domain_array);
                }

                $translation = array();
                $entry = array();
                continue;
            }

            if ($first_character === '"') // Check for header lines or partial strings
            {
                $header_start = preg_match('/^[^:]*:/u', $line) === 1;

                if (!$headers_done && $header_start)
                {
                    $headers_in_progress = true;
                }

                if ($headers_in_progress)
                {
                    // A header is starting
                    if ($header_start)
                    {
                        $header_data = $line;

                        if (!isset($header_entry[0]) || $header_entry[0] === '')
                        {
                            preg_match('/([^:]+):(.+)/', $line, $matches);
                            $header_entry[0] = $this->unquoteLine(trim($matches[1]));
                            $header_entry[1] = '';
                            $header_data = $matches[2];
                        }
                    }

                    $header_entry[1] .= $this->unquoteLine(trim(str_replace('\n"', '', trim($header_data))));

                    if (substr($line, -3) === '\n"')
                    {
                        $domain_array['headers'][$header_entry[0]] = $header_entry[1];

                        if ($header_entry[0] === 'Plural-Forms')
                        {
                            $domain_array['plural_rule'] = $this->parsePluralRule($header_entry[1]);
                        }

                        $header_entry[0] = '';
                        $header_entry[1] = '';
                    }
                }

                $entry[1] .= $this->unquoteLine(trim($line));
            }
            else
            {
                if ($headers_in_progress)
                {
                    $headers_in_progress = false;
                    $headers_done = true;
                }

                $split_line = $this->splitLine($line);

                if ($first_character === '#') // Check for comment lines
                {
                    $translation = $this->parseComment($split_line, $translation);
                }
                else if ($first_character === 'm') // Check for the start of msg lines
                {
                    // We have finished combining the message line and store it before starting a new one
                    if (!empty($entry))
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

    private function parseMessage(array $split_line, array $translation): array
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

                    if ($index === 0)
                    {
                        $translation['msgstr'] = $message;
                    }

                    $translation['plurals'][$index] = $message;
                }
        }

        return $translation;
    }

    private function parseComment(array $split_line, array $translation): array
    {
        $translation['comments'][$split_line[0]][] = $split_line[1];
        return $translation;
    }

    private function storeTranslation(array $translation, array $domain_array): array
    {
        $id = $translation['msgid'];

        if (isset($translation['msgctxt']))
        {
            $current_translation = array();
            $new_translation = ['contexts' => [$translation['msgctxt'] => $translation]];

            if (isset($domain_array['translations'][$id]))
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

    private function splitLine(string $line): array
    {
        $split_line = preg_split('/\s+/u', $line, 2, PREG_SPLIT_NO_EMPTY);
        $split_line[0] = (!empty($split_line[0])) ? $this->unquoteLine(trim($split_line[0])) : '';
        $split_line[1] = (!empty($split_line[1])) ? $this->unquoteLine(trim($split_line[1])) : '';
        return $split_line;
    }

    private function unquoteLine(string $string): string
    {
        return preg_replace('/^"|"\s*?$/u', '', $string);
    }
}