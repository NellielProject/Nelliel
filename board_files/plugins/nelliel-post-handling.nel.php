<?php
$nelliel_post_handling = new nelliel_post_handling();
class nelliel_post_handling
{
    static $plugin_id;

    public function __construct()
    {
        global $plugins;
        self::$plugin_id = $plugins->register_plugin('Default Post Handling', 'Nelliel', 'v1.0');
        $plugins->register_hook_function('tripcode-processing', array($this, 'nel_standard_tripcode'), 10, self::$plugin_id);
        $plugins->register_hook_function('secure-tripcode-processing', array($this, 'nel_secure_tripcode'), 10, self::$plugin_id);
    
    }

    public function nel_standard_tripcode($poster_info, $name_pieces)
    {
        if ($name_pieces[3] !== '' && BS1_ALLOW_TRIPKEYS)
        {
            $cap = strtr($name_pieces[3], '&amp;', '&');
            $cap = strtr($cap, '&#44;', ',');
            $salt = substr($cap . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.#', $salt);
            $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $poster_info['tripcode'] = substr(crypt($cap, $salt), -10);
        }
        
        return $poster_info;
    
    }

    public function nel_secure_tripcode($poster_info, $name_pieces, $modpostc)
    {
        if ($name_pieces[5] !== '' || $modpostc > 0)
        {
            $trip = nel_hash($name_pieces[5]);
            $poster_info['secure_tripcode'] = substr(crypt($trip, '42'), -12);
        }
        
        return $poster_info;
    
    }
}
?>