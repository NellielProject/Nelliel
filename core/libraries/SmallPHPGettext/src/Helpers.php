<?php
declare(strict_types = 1);

namespace SmallPHPGettext;

trait Helpers
{

    public function poDecode(string $string): string
    {
        $conversions = ['\\"' => '"', '\\\\' => '\\', '\n' => "\n", '\r' => "\r", '\t' => "\t", '\v' => "\v",
            '\f' => "\f", '\e' => "\e", '\a' => "\x07", '\b' => "\x08"];
        return strtr($string, $conversions);
    }

    public function poEncode(string $string): string
    {
        $conversions = ['"' => '\\"', '\\' => '\\\\', "\0" => '', "\n" => '\n', "\r" => '\r', "\t" => '\t'];
        return strtr($string, $conversions);
    }

    private function parsePluralRule(string $rule_string): string
    {
        $plural_rule = preg_replace('/nplurals.*?;|plural.*?=/', '', $rule_string);
        $plural_rule = preg_replace('/[^\sn0-9:;\(\)\?\|\&=!<>\/\%-*\/]/', '', $plural_rule);
        $plural_rule = preg_replace('/(n)/', '$$1', $plural_rule);
        $plural_rule = 'return ' . $this->wrapTenary($plural_rule) . ';';
        return $plural_rule;
    }

    private function wrapTenary(string $expression): string
    {
        if (preg_match('/((?:[^?]+)\?(?:[^:]+):)([^;]+)/', $expression, $matches) === 1)
        {
            return $matches[1] . '(' . $this->wrapTenary($matches[2]) . ')';
        }

        return $expression;
    }
}