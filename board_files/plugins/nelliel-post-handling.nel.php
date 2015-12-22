<?php
$nelliel_post_handling = new nelliel_post_handling($plugins);

class nelliel_post_handling
{
    private $plugin_id;

    public function __construct($plugins)
    {
        $this->plugin_id = $plugins->register_plugin($this, 'Default Post Handling', 'Nelliel', 'v1.0');
        $plugins->register_hook_function('tripcode-processing', array($this, 'nel_standard_tripcode'), 10, $this->plugin_id);
        $plugins->register_hook_function('secure-tripcode-processing', array($this, 'nel_secure_tripcode'), 10, $this->plugin_id);
    }

    public function nel_standard_tripcode($poster_info, $name_pieces)
    {
        if ($name_pieces[3] !== '' && BS1_ALLOW_TRIPKEYS)
        {
            $cap = utf8_strtr($name_pieces[3], '&amp;', '&');
            $cap = utf8_strtr($cap, '&#44;', ',');
            $salt = utf8_substr($cap . 'H.', 1, 2);
            $salt = preg_replace('#[^\.-z]#', '.#', $salt);
            $salt = utf8_strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
            $poster_info['tripcode'] = utf8_substr(crypt($cap, $salt), -10);
        }
        
        return $poster_info;
    }

    public function nel_secure_tripcode($poster_info, $name_pieces, $modpostc)
    {
        if ($name_pieces[5] !== '' || $modpostc > 0)
        {
            $trip = nel_hash($name_pieces[5]);
            $poster_info['secure_tripcode'] = utf8_substr(crypt($trip, '42'), -12);
        }
        
        return $poster_info;
    }
}
?>