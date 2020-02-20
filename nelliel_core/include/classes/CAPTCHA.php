<?php

namespace Nelliel;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class CAPTCHA
{
    protected $domain;
    protected $site_domain;
    protected $database;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->site_domain = new DomainSite($this->database);
    }

    public function generate()
    {
        // Pretty basic CAPTCHA
        // We'll leave making a better one to someone who really knows the stuff
        $this->cleanup();
        $throttled = $this->throttle();

        if ($throttled)
        {
            die();
        }

        $generated = nel_plugins()->processHook('nel-captcha-generate', [$this->domain], false);

        if ($generated)
        {
            return;
        }

        $captcha_text = '';
        $character_set = 'bcdfghjkmnpqrstvwxyz23456789';
        $set_array = utf8_split($character_set);
        $characters_limit = $this->site_domain->setting('captcha_character_count');
        $selected_indexes = array_rand($set_array, $characters_limit);

        foreach ($selected_indexes as $index)
        {
            $captcha_text .= $set_array[$index];
        }

        $captcha_image = $this->render($captcha_text);
        $captcha_key = substr(sha1(random_bytes(16)), -12);
        setrawcookie('captcha-key-' . $this->domain->id(), $captcha_key, 0, '/');
        header("Content-Type: image/png");
        imagepng($captcha_image);

        $captcha_data = array();
        $captcha_data['key'] = $captcha_key;
        $captcha_data['text'] = $captcha_text;
        $captcha_data['domain_id'] = $this->domain->id();
        $captcha_data['time_created'] = time();
        $captcha_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $this->store($captcha_data);
    }

    public function render(string $captcha_text)
    {
        $captcha_image = nel_plugins()->processHook('nel-captcha-render', [$this->domain]);

        if (!is_null($captcha_image))
        {
            return $captcha_image;
        }

        $character_count = utf8_strlen($captcha_text);
        $font_file = CORE_FONTS_FILE_PATH . 'Halogen.ttf';
        $image_width = $this->site_domain->setting('captcha_width');
        $image_height = $this->site_domain->setting('captcha_height');
        $font_size = $image_height * 0.5;
        $text_box = imageftbbox($font_size, 0, $font_file, $captcha_text);
        $x_margin = $image_width - $text_box[4];
        $y_margin = $image_height - $text_box[5];
        $character_spacing = ($x_margin / ($character_count + 2));

        $captcha_image = imagecreatetruecolor($image_width, $image_height);
        $background_color = imagecolorallocate($captcha_image, 230, 230, 230);
        imagefill($captcha_image, 0, 0, $background_color);

        $line_colors = array();
        $line_colors[] = imagecolorallocate($captcha_image, 150, 150, 0);
        $line_colors[] = imagecolorallocate($captcha_image, 120, 175, 180);
        $line_colors[] = imagecolorallocate($captcha_image, 190, 150, 125);
        $line_colors_size = count($line_colors);

        for ($i = 0; $i < 8; $i ++)
        {
            $line_color = $line_colors[rand(0, $line_colors_size - 1)];
            imagesetthickness($captcha_image, rand(1, 5));
            imageline($captcha_image, 0, rand(0, $image_height), $image_width, rand(0, $image_height), $line_color);
        }

        $x = $x_margin - ($character_spacing * $character_count);
        $y = $y_margin / 2;

        $text_colors = array();
        $text_colors[] = imagecolorallocate($captcha_image, 200, 100, 0);
        $text_colors[] = imagecolorallocate($captcha_image, 70, 125, 180);
        $text_colors[] = imagecolorallocate($captcha_image, 140, 100, 125);
        $text_colors_size = count($text_colors);

        $characters_array = utf8_split($captcha_text);

        foreach ($characters_array as $character)
        {
            $box = imageftbbox($font_size, 0, $font_file, $character);
            $size = $font_size - rand(0, intval($font_size * 0.35));
            $angle = rand(0, 50) - 25;
            $color = $text_colors[rand(0, $text_colors_size - 1)];
            imagefttext($captcha_image, $size, $angle, $x, $y + rand(0, 5), $color, $font_file, $character);
            $x += $box[4] + $character_spacing;
        }

        return $captcha_image;
    }

    public function throttle()
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $time = time() - 60; // 1 minute period to check
        $prepared = $this->database->prepare(
                'SELECT COUNT(*) FROM "' . CAPTCHA_TABLE . '" WHERE "ip_address" = ? AND "time_created" > ?');
        $result = $this->database->executePreparedFetch($prepared, [$ip_address, $time], PDO::FETCH_COLUMN);
        return $result >= $this->site_domain->setting('captcha_throttle');
    }

    public function store(array $captcha_data)
    {
        $prepared = $this->database->prepare(
                'INSERT INTO "' . CAPTCHA_TABLE .
                '" ("key", "text", "domain_id", "time_created", "ip_address")
								VALUES (:key, :text, :domain_id, :time_created, :ip_address)');
        $prepared->bindParam(':key', $captcha_data['key'], PDO::PARAM_STR);
        $prepared->bindParam(':text', $captcha_data['text'], PDO::PARAM_STR);
        $prepared->bindParam(':domain_id', $captcha_data['domain_id'], PDO::PARAM_STR);
        $prepared->bindParam(':time_created', $captcha_data['time_created'], PDO::PARAM_INT);
        $prepared->bindParam(':ip_address', $captcha_data['ip_address'], PDO::PARAM_LOB);
        $this->database->executePrepared($prepared);
    }

    public function verify(string $key, string $answer)
    {
        $verified = nel_plugins()->processHook('nel-captcha-verify', [$this->domain], false);

        if ($verified)
        {
            return true;
        }

        $expiration = time() - $this->site_domain->setting('captcha_timeout');
        $prepared = $this->database->prepare(
                'SELECT * FROM "' . CAPTCHA_TABLE . '" WHERE "key" = ? AND "text" = ? AND "time_created" > ?');
        $result = $this->database->executePreparedFetch($prepared, [$key, $answer, $expiration], PDO::FETCH_ASSOC);

        if ($result === false)
        {
            return false;
        }

        $prepared = $this->database->prepare('DELETE FROM "' . CAPTCHA_TABLE . '" WHERE "key" = ? AND "text" = ?');
        $this->database->executePreparedFetch($prepared, [$key, $answer], PDO::FETCH_ASSOC);
        return true;
    }

    public function cleanup()
    {
        $done = nel_plugins()->processHook('nel-captcha-cleanup', [$this->domain], false);

        if ($done)
        {
            return;
        }

        $expiration = time() - $this->site_domain->setting('captcha_timeout');
        $prepared = $this->database->prepare('DELETE FROM "' . CAPTCHA_TABLE . '" WHERE "time_created" < ?');
        $this->database->executePrepared($prepared, [$expiration]);
    }

    public function verifyReCAPTCHA()
    {
        $verified = nel_plugins()->processHook('nel-verify-recaptcha', [$this->domain]);

        if ($verified)
        {
            return;
        }

        if (!isset($_POST['g-recaptcha-response']))
        {
            return false;
        }

        $response = $_POST['g-recaptcha-response'];
        $result = file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' .
                $this->site_domain->setting('recaptcha_sekrit_key') . '&response=' . $response);
        $verification = json_decode($result);
        return $verification->success;
    }
}