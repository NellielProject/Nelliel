<?php
declare(strict_types = 1);

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use ChrisUllyott\FileSize;
use IPTools\IP;
use Nelliel\Domains\Domain;

function nel_get_microtime(bool $convert_int = true)
{
    $time = microtime();
    $return_time = ['time' => $time];
    $split_time = explode(' ', $time);

    $seconds = intval($split_time[1]);
    $milliseconds = intval($split_time[0] * 1000);
    $microseconds = intval($split_time[0] * 1000000);

    if ($convert_int) {
        $return_time = ['time' => intval($seconds), 'milli' => intval(round($milliseconds, 3)),
            'micro' => intval($microseconds)];
    } else {
        $return_time = ['time' => (float) $seconds, 'milli' => round($milliseconds, 3),
            'micro' => (float) $microseconds];
    }

    return $return_time;
}

function nel_utf8_to_numeric_html_entities(&$input, bool $non_ascii_only = true)
{
    $regex = ($non_ascii_only) ? '#([^[:ascii:]])#Su' : '#(.)#Su';

    $input = preg_replace_callback($regex, function ($matches) {
        return '&#' . utf8_ord($matches[0]) . ';';
    }, $input);
}

function nel_numeric_html_entities_to_utf8(&$input)
{
    $input = preg_replace_callback('#&\#[0-9]+;#Su',
        function ($matches) {
            return utf8_chr(intval(utf8_substr($matches[0], 2, -1)));
        }, $input);
}

function nel_typecast($value, string $datatype, bool $empty_null = true)
{
    if ($empty_null && nel_true_empty($value)) {
        return null;
    }

    if ($datatype === 'bool' || $datatype === 'boolean') {
        return boolval($value);
    }

    if ($datatype === 'int' || $datatype === 'integer') {
        return intval($value);
    }

    if ($datatype === 'str' || $datatype === 'string') {
        return strval($value);
    }

    if ($datatype === 'float') {
        return floatval($value);
    }

    if ($datatype === 'array') {
        return (array) $value;
    }

    return $value;
}

function nel_true_empty($variable)
{
    return is_null($variable) || $variable === '' || $variable === array();
}

function nel_random_alphanumeric($length)
{
    if ($length <= 0) {
        return '';
    }

    $base = utf8_str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', ceil($length / 36));
    $random = utf8_str_shuffle($base);
    return utf8_substr($random, 0, $length);
}

function nel_form_input_default(array $input)
{
    $value = '';

    if (isset($input['default'])) {
        $value = $input['default'];
    }

    if (isset($input['value'])) {
        $value = $input['value'];
    }

    return $value;
}

function nel_prepare_ip_for_storage(?string $ip_address, bool $unhashed_check = true)
{
    if (is_null($ip_address)) {
        return null;
    }

    if ($unhashed_check && !nel_get_cached_domain(Domain::SITE)->setting('store_unhashed_ip')) {
        return null;
    }

    $packed_ip_address = @inet_pton($ip_address);

    if ($packed_ip_address === false) {
        // Check if the error is simply due to the address already being packed
        if (@inet_ntop($ip_address) !== false) {
            return $ip_address;
        }

        return null;
    }

    return $packed_ip_address;
}

function nel_convert_ip_from_storage(?string $ip_address)
{
    if (is_null($ip_address)) {
        return null;
    }

    $unpacked_ip_address = @inet_ntop($ip_address);

    if ($unpacked_ip_address === false) {
        return null;
    }

    return $unpacked_ip_address;
}

function nel_exec(string $command): array
{
    if (!function_exists('exec')) {
        return array();
    }

    $path_command = '';
    $path = nel_get_cached_domain(Domain::SITE)->setting('shell_path');

    if ($path !== '') {
        $path_command = 'PATH="' . escapeshellcmd($path) . ':$PATH";';
    }

    $full_command = $path_command . $command;
    $output = array();
    $result_code = 0;
    $last_line = exec($full_command, $output, $result_code);
    return ['last_line' => $last_line, 'output' => $output, 'result_code' => $result_code];
}

function nel_shell_exec(string $command): ?string
{
    if (!function_exists('shell_exec')) {
        return null;
    }

    $path_command = '';
    $path = nel_get_cached_domain(Domain::SITE)->setting('shell_path');

    if ($path !== '') {
        $path_command = 'PATH="' . escapeshellcmd($path) . ':$PATH";';
    }

    $full_command = $path_command . $command;
    return shell_exec($full_command);
}

function nel_magick_available(): array
{
    $magicks = array();

    if (extension_loaded('gmagick')) {
        $magicks[] = 'gmagick';
    }

    if (extension_loaded('imagick')) {
        $magicks[] = 'imagick';
    }

    $results = nel_exec('gm -version');

    if (!empty($results) && $results['result_code'] === 0) {
        $magicks[] = 'graphicsmagick';
    }

    $results = nel_exec('convert -version');

    if (!empty($results) && $results['result_code'] === 0) {
        $magicks[] = 'imagemagick';
    }

    return $magicks;
}

function nel_build_router_url(array $uris, bool $end_slash = false, string $query_string = ''): string
{
    $url = NEL_MAIN_SCRIPT_QUERY_WEB_PATH . 'route=';

    foreach ($uris as $uri) {
        $url .= '/' . rawurlencode((string) $uri);
    }

    if ($end_slash) {
        $url .= '/';
    }

    if ($query_string !== '') {
        $url .= '?' . $query_string;
    }

    return $url;
}

function nel_size_format(int $bytes, bool $use_iec, bool $binary, int $precision, ?string $units = null): string
{
    $filesize = new FileSize($bytes . ' B', $binary ? 2 : 10);
    $output = '';

    if (is_null($units)) {
        $output = $filesize->asAuto($precision);
    } else {
        $output = $filesize->as($units, $precision) . ' ' . $units;
    }

    if ($use_iec) {
        $output = preg_replace('/([KkMmGgTtPpEeZzYy])B/', '$1iB', $output);
    } else {
        $output = preg_replace('/([KkMmGgTtPpEeZzYy])iB/', '$1B', $output);
    }

    return $output;
}

function nel_is_unhashed_ip(string $ip): bool
{
    try {
        new IP($ip);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function nel_strtotime(string $time): int
{
    $time = preg_replace('/(\d+)\s?ye?a?r?s?/', '$1years', $time);
    $time = preg_replace('/(\d+)\s?mont?h?s?/', '$1months', $time);
    $time = preg_replace('/(\d+)\s?we?e?k?s?/', '$1weeks', $time);
    $time = preg_replace('/(\d+)\s?da?y?s?/', '$1days', $time);
    $time = preg_replace('/(\d+)\s?ho?u?r?s?/', '$1hours', $time);
    $time = preg_replace('/(\d+)\s?m[^o]i?n?u?t?e?s?/', '$1minutes', $time);
    $time = preg_replace('/(\d+)\s?se?c?o?n?d?s/', '$1seconds', $time);

    return intval(strtotime($time));
}

function nel_config_var_export(array $array, string $prefix = '')
{
    $config_output = '';

    foreach ($array as $key => $value) {
        if (is_int($key) && is_string($value)) {
            $config_output .= "\n" . $value . ';';
        } else if (is_array($value)) {
            $prefix .= '[\'' . $key . '\']';
            $keys = array_keys($value);
            $values_array = '';

            foreach ($keys as $index) {
                if (is_int($index) && !is_array($value[$index])) {
                    $values_array .= $value[$index] . ', ';
                }
            }

            if (empty($values_array)) {
                $line = nel_config_var_export($value, $prefix);
                $config_output .= $line;
            } else {
                $config_output .= "\n" . $prefix . ' = [' . utf8_rtrim($values_array, ' ,') . '];';
            }

            $prefix = utf8_str_replace('[\'' . $key . '\']', '', $prefix);
        } else if (is_string($value)) {
            $config_output .= "\n" . $prefix . '[\'' . $key . '\'] = \'' . $value . '\';';
        } else if (is_null($value)) {
            $config_output .= "\n" . $prefix . '[\'' . $key . '\'] = null;';
        } else if (is_bool($value)) {
            $boolval = ($value === false) ? 'false' : 'true';
            $config_output .= "\n" . $prefix . '[\'' . $key . '\'] = ' . $boolval . ';';
        } else {
            $config_output .= "\n" . $prefix . '[\'' . $key . '\'] = ' . $value . ';';
        }
    }

    return $config_output;
}

function nel_is_absolute_url(string $url): bool
{
    return preg_match('/^(?:.+:)?\/\//u', $url) === 1;
}

function nel_key_array_by_column(string $column, array $array): array
{
    $new_array = array();

    foreach ($array as $sub_array) {
        $new_array[$sub_array[$column]] = $sub_array;
    }

    return $new_array;
}

function nel_array_htmlspecialchars(array $array, int $flags): array
{
    $html_filter = function (&$value, $key) use ($flags) {
        if (is_string($value)) {
            $value = htmlspecialchars($value, $flags, 'UTF-8');
        }
    };

    array_walk_recursive($array, $html_filter);
    return $array;
}
