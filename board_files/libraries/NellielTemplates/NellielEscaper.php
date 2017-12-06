<?php

namespace NellielTemplates;

class NellielEscaper
{

    function __construct()
    {
        ;
    }

    public function doEscaping(&$content, $escape_type = 'html')
    {
        switch ($escape_type)
        {
            case 'none':
                break;

            case 'html':
                $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8', FALSE);
                break;

            // When processed through DOM, three of these are escaped automatically and we don't want to double-escape
            case 'attribute-dom':
                $content = preg_replace_callback('#[^a-zA-Z0-9,\._&<>]#Su',
                function ($matches)
                {
                    switch ($matches[0])
                    {
                        case '"':
                            return '&quot';
                    }

                    if (strlen($matches[0]) === 1)
                    {
                        return '&#x' . bin2hex($matches[0]);
                    }
                    else
                    {
                        return '&#x' . substr(trim(json_encode($matches[0]), '"'), 2);
                    }

                    return $matches[0];
                }, $content);
                break;

            case 'attribute':
                $content = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su',
                        function ($matches)
                        {
                            switch ($matches[0])
                            {
                                case '&':
                                    return '&amp;';
                                case '<':
                                    return '&lt;';
                                case '>':
                                    return '&gt;';
                                case '"':
                                    return '&quot';
                            }

                            if (strlen($matches[0]) === 1)
                            {
                                return '&#x' . bin2hex($matches[0]);
                            }
                            else
                            {
                                return '&#x' . substr(trim(json_encode($matches[0]), '"'), 2);
                            }

                            return $matches[0];
                        }, $content);
                break;

            case 'url':
                $content = rawurlencode($content);
                break;

            case 'js':
                $content = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su',
                        function ($matches)
                        {
                            if (strlen($matches[0]) === 1)
                            {
                                return '\x' . strtoupper(substr('00' . bin2hex($matches[0]), -2));
                            }
                            else
                            {
                                return trim(json_encode($matches[0]), '"');
                            }

                            return $matches[0];
                        }, $content);
                break;
            case 'css':
                $content = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su',
                        function ($matches)
                        {
                            if (strlen($matches[0]) === 1)
                            {
                                return '\x' . strtoupper(substr('00' . bin2hex($matches[0]), -2));
                            }
                            else
                            {
                                return trim(json_encode($matches[0]), '"');
                            }

                            return $matches[0];
                        }, $content);
                break;
        }
    }
}