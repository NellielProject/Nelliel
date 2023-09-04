<?php
declare(strict_types = 1);

namespace Nelliel\AntiSpam;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use Nelliel\Domains\Domain;
use Nelliel\Domains\DomainSite;
use PDO;

class CAPTCHA
{
    private $domain;
    private $site_domain;
    private $database;
    private $file_handler;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
        $this->database = $domain->database();
        $this->site_domain = new DomainSite($this->database);
        $this->file_handler = nel_utilities()->fileHandler();
    }

    public function get()
    {
        $captcha_key = $_COOKIE['captcha-key'] ?? '';

        if (!empty($captcha_key)) {
            if ($this->keyExists($captcha_key, true)) {
                $this->redirectToImage($captcha_key);
                return;
            }
        }

        $this->generate(true);
    }

    protected function rateLimit()
    {
        if ($this->site_domain->setting('captcha_rate_limit') == 0) {
            return;
        }

        $rate_limit = nel_utilities()->rateLimit();
        $hashed_ip_address = nel_request_ip_address(true);
        $attempt_time = time();

        if ($rate_limit->lastTime($hashed_ip_address, 'captcha') > $attempt_time - 60) {
            if ($rate_limit->attempts($hashed_ip_address, 'captcha') < $this->site_domain->setting('captcha_rate_limit')) {
                $rate_limit->updateAttempts($hashed_ip_address, 'captcha');
            } else {
                nel_derp(72, _gettext('Requesting new CAPTCHAs too fast. Wait a minute.'));
            }
        } else {
            $rate_limit->clearAttempts($hashed_ip_address, 'captcha');
        }
    }

    public function generate(bool $display)
    {
        $this->rateLimit();
        $this->cleanupExpired();

        // Pretty basic CAPTCHA
        // We'll leave making a better one to someone who really knows the stuff
        $captcha_text = '';
        // $character_set = $this->site_domain->setting('captcha_characters');
        $character_set = 'bcdfghjkmnop1234567890';
        $set_array = utf8_split($character_set);
        $characters_limit = $this->site_domain->setting('captcha_character_count');
        $selected_indexes = array_rand($set_array, $characters_limit);

        foreach ($selected_indexes as $index) {
            $captcha_text .= $set_array[$index];
        }

        $captcha_image = $this->render($captcha_text);
        $captcha_key = utf8_substr(hash('sha256', (random_bytes(16))), -32);
        setrawcookie('captcha-key', $captcha_key, time() + $this->site_domain->setting('captcha_timeout'),
            NEL_BASE_WEB_PATH);
        $this->file_handler->createDirectory(NEL_CAPTCHA_FILES_PATH); // Just to be sure
        imagejpeg($captcha_image, NEL_CAPTCHA_FILES_PATH . $captcha_key . '.jpg');
        $captcha_data = array();
        $captcha_data['captcha_key'] = $captcha_key;
        $captcha_data['captcha_text'] = $captcha_text;
        $captcha_data['domain_id'] = $this->domain->id();
        $captcha_data['time_created'] = time();
        $this->store($captcha_data);

        if ($display) {
            $this->redirectToImage($captcha_key);
        }
    }

    public function render(string $captcha_text)
    {
        $character_count = utf8_strlen($captcha_text);
        $font_file = NEL_INCLUDE_PATH . 'AntiSpam/Halogen.ttf';
        $image_width = $this->site_domain->setting('captcha_width');
        $image_height = $this->site_domain->setting('captcha_height');
        $font_size = $image_height * 0.5;
        $text_box = imageftbbox($font_size, 0, $font_file, $captcha_text);
        $x_margin = $image_width - $text_box[4];
        $y_margin = $image_height - $text_box[5];
        $character_spacing = ($x_margin / ($character_count + 2));
        $max_lines = $this->site_domain->setting('captcha_max_lines');
        $max_arcs = $this->site_domain->setting('captcha_max_arcs');

        $captcha_image = imagecreatetruecolor($image_width, $image_height);
        $background_color = imagecolorallocate($captcha_image, 230, 230, 230);
        imagefill($captcha_image, 0, 0, $background_color);

        $line_colors = array();
        $line_colors[] = imagecolorallocate($captcha_image, 150, 150, 0);
        $line_colors[] = imagecolorallocate($captcha_image, 120, 175, 180);
        $line_colors[] = imagecolorallocate($captcha_image, 190, 150, 125);
        $line_colors_size = count($line_colors);

        // Generate lines
        for ($i = 0; $i < 6; $i ++) {
            $x_start = 0;
            $y_start = rand(0, $image_height);
            $x_end = $image_width;
            $y_end = rand(0, $image_height);
            $line_color = $line_colors[rand(0, $line_colors_size - 1)];
            imagesetthickness($captcha_image, rand(2, 5));
            imageline($captcha_image, $x_start, $y_start, $x_end, $y_end, $line_color);
        }

        // Generate arcs
        for ($i = 0; $i < 6; $i ++) {
            $x_center = rand(0, $image_width);
            $y_center = rand(0, $image_height);
            $width = rand(0, $image_width);
            $height = rand(0, $image_height);
            $start_angle = rand(0, 250);
            $end_angle = rand(0, 250);

            if (rand(0, 1) === 1) {
                $start_angle *= -1;
                $end_angle *= -1;
            }

            $line_color = $line_colors[rand(0, $line_colors_size - 1)];
            imagesetthickness($captcha_image, rand(1, 5));
            imagearc($captcha_image, $x_center, $y_center, $width, $height, $start_angle, $end_angle, $line_color);
        }

        $x = $x_margin - ($character_spacing * $character_count);
        $y = $y_margin / 2;

        $text_colors = array();
        $text_colors[] = imagecolorallocate($captcha_image, 200, 100, 0);
        $text_colors[] = imagecolorallocate($captcha_image, 70, 125, 180);
        $text_colors[] = imagecolorallocate($captcha_image, 140, 100, 125);
        $text_colors_size = count($text_colors);

        $characters_array = utf8_split($captcha_text);
        $max_rotation = $this->site_domain->setting('captcha_max_character_rotation');

        foreach ($characters_array as $character) {
            $box = imageftbbox($font_size, 0, $font_file, $character);
            $size = $font_size - rand(0, intval($font_size * 0.35));
            $angle = rand(-$max_rotation, $max_rotation);
            $color = $text_colors[rand(0, $text_colors_size - 1)];
            imagefttext($captcha_image, $size, $angle, intval($x), intval($y + rand(0, 5)), $color, $font_file,
                $character);
            $x += $box[4] + $character_spacing;
        }

        return $captcha_image;
    }

    public function redirectToImage(string $key)
    {
        header('Location: ' . NEL_CAPTCHA_WEB_PATH . $key . '.jpg');
    }

    public function keyExists(string $key, bool $check_expired)
    {
        $prepared = $this->database->prepare(
            'SELECT "time_created" FROM "' . NEL_CAPTCHA_TABLE . '" WHERE "captcha_key" = ?');
        $result = $this->database->executePreparedFetch($prepared, [$key], PDO::FETCH_COLUMN);

        if ($result === false) {
            return false;
        }

        if ($check_expired) {
            $expiration = time() - $this->site_domain->setting('captcha_timeout');

            if ($result < $expiration) {
                $this->remove($key);
                return false;
            }

            return true;
        }

        return true;
    }

    public function store(array $captcha_data)
    {
        $prepared = $this->database->prepare(
            'INSERT INTO "' . NEL_CAPTCHA_TABLE .
            '" ("captcha_key", "captcha_text", "domain_id", "time_created")
								VALUES (:captcha_key, :captcha_text, :domain_id, :time_created)');
        $prepared->bindValue(':captcha_key', $captcha_data['captcha_key'], PDO::PARAM_STR);
        $prepared->bindValue(':captcha_text', $captcha_data['captcha_text'], PDO::PARAM_STR);
        $prepared->bindValue(':domain_id', $captcha_data['domain_id'], PDO::PARAM_STR);
        $prepared->bindValue(':time_created', $captcha_data['time_created'], PDO::PARAM_INT);
        $this->database->executePrepared($prepared);
    }

    public function verify(string $key, string $answer)
    {
        $failed = false;
        $failed = nel_plugins()->processHook('nel-inb4-captcha-verify', [$this->domain, $this, $key, $answer], $failed);

        if ($this->site_domain->setting('use_native_captcha') && !$failed) {
            $expiration = time() - $this->site_domain->setting('captcha_timeout');
            $prepared = $this->database->prepare(
                'SELECT * FROM "' . NEL_CAPTCHA_TABLE .
                '" WHERE "captcha_key" = ? AND "captcha_text" = ? AND "time_created" > ?');
            $result = $this->database->executePreparedFetch($prepared, [$key, $answer, $expiration], PDO::FETCH_ASSOC);
            $failed = $result === false;
        }

        if ($failed) {
            nel_derp(70, _gettext('CAPTCHA check failed.'));
        }

        $this->remove($key);
        return true;
    }

    public function remove(string $key)
    {
        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CAPTCHA_TABLE . '" WHERE "captcha_key" = ?');
        $this->database->executePrepared($prepared, [$key]);
        $this->file_handler->eraserGun(NEL_CAPTCHA_WEB_PATH, $key . '.jpg');
    }

    public function cleanupExpired()
    {
        $expiration = time() - $this->site_domain->setting('captcha_timeout');
        $prepared = $this->database->prepare(
            'SELECT "captcha_key" FROM "' . NEL_CAPTCHA_TABLE . '" WHERE "time_created" = ?');
        $result = $this->database->executePreparedFetchAll($prepared, [$expiration], PDO::FETCH_COLUMN);

        foreach ($result as $key) {
            $this->file_handler->eraserGun(NEL_CAPTCHA_WEB_PATH, $key . '.jpg');
        }

        $prepared = $this->database->prepare('DELETE FROM "' . NEL_CAPTCHA_TABLE . '" WHERE "time_created" < ?');
        $this->database->executePrepared($prepared, [$expiration]);
    }
}