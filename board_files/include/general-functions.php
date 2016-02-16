<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

define('SHA256_AVAILABLE', in_array('sha256', hash_algos()));

// This hashing is probably fine for most imageboards
// If you need something stronger, it can be replaced by a plugin method
function nel_hash($input, $plugins)
{
    $methods = array('sha256', 'md5', FALSE, FALSE);
    $methods = $plugins->plugin_hook('hash-algorithms', TRUE, array($methods));
    
    // If set to TRUE by a plugin, default method will not be used
    if (!$methods[3])
    {
        $half_salt1 = utf8_substr(HASH_SALT, 0, (utf8_strlen(HASH_SALT) / 2));
        $half_salt2 = utf8_substr(HASH_SALT, (utf8_strlen(HASH_SALT) / 2), utf8_strlen(HASH_SALT));
        
        // In case there is a need for something older
        if ($methods[2] || !SHA256_AVAILABLE)
        {
            $hash = hash($methods[1], $half_salt1 . $input . $half_salt2);
        }
        else
        {
            $hash = hash($methods[0], $half_salt1 . $input . $half_salt2);
        }
    }
    
    return $hash;
}

function nel_clean_exit($dataforce, $die)
{
    $dataforce['post_links'] = nel_cache_links($dataforce['post_links']);
    nel_write_multi_cache($dataforce);
    
    if ($die)
    {
        die();
    }
    
    if (STUFF_DONE)
    {
        echo '<meta http-equiv="refresh" content="10;URL=' . PHP_SELF2 . PHP_EXT . '">';
    }
    else
    {
        echo '<meta http-equiv="refresh" content="1;URL=' . PHP_SELF2 . PHP_EXT . '">';
    }
    
    die();
}

function get_millisecond_time()
{
    $time = explode(' ', microtime());
    $time[0] = str_pad(round($time[0] * 1000), 3, '0', STR_PAD_LEFT);
    return $time[1] . $time[0];
}
?>