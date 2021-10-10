<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableFiletypes extends Table
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_FILETYPES_TABLE;
        $this->column_types = [
            'entry' => ['php_type' => 'integer', 'pdo_type' => PDO::PARAM_INT],
            'format' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'extensions' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'category' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'mime' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'magic_regex' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'type_label' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR],
            'is_category' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'enabled' => ['php_type' => 'boolean', 'pdo_type' => PDO::PARAM_INT],
            'moar' => ['php_type' => 'string', 'pdo_type' => PDO::PARAM_STR]];
        $this->column_checks = [
            'entry' => ['row_check' => false, 'auto_inc' => true],
            'format' => ['row_check' => true, 'auto_inc' => false],
            'extensions' => ['row_check' => false, 'auto_inc' => false],
            'category' => ['row_check' => true, 'auto_inc' => false],
            'mime' => ['row_check' => false, 'auto_inc' => false],
            'magic_regex' => ['row_check' => false, 'auto_inc' => false],
            'type_label' => ['row_check' => false, 'auto_inc' => false],
            'is_category' => ['row_check' => true, 'auto_inc' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            format          VARCHAR(50) NOT NULL,
            extensions      TEXT DEFAULT NULL,
            category        VARCHAR(50) NOT NULL,
            mime            VARCHAR(255) DEFAULT NULL,
            magic_regex     TEXT DEFAULT NULL,
            type_label      VARCHAR(255) NOT NULL,
            is_category     SMALLINT NOT NULL DEFAULT 0,
            enabled         SMALLINT NOT NULL DEFAULT 0,
            moar            TEXT DEFAULT NULL
        ) " . $options . ";";

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        // These regexes should not be considered authoritative for processing a format!
        // They are designed to simply identify files in combination with a known extension
        // IMPORTANT: If the regex has / in it, that needs to be escaped since that's the delimiter Nelliel uses

        // Graphics formats
        $this->insertDefaultRow(['', null, 'graphics', null, null, 'Graphics', 1, 1]);
        $this->insertDefaultRow(['jpeg', '["jpg","jpe","jpeg"]', 'graphics', 'image/jpeg', '^\xFF\xD8\xFF', 'JPEG', 0, 1]);
        $this->insertDefaultRow(['gif', '["gif"]', 'graphics', 'image/gif', '^(?:GIF87a|GIF89a)', 'GIF', 0, 1]);
        $this->insertDefaultRow(['png', '["png"]', 'graphics', 'image/png', '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A', 'PNG', 0, 1]);
        $this->insertDefaultRow(['jpeg2000', '["jp2"]', 'graphics', 'image/jp2', '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A', 'JPEG2000', 0, 1]);
        $this->insertDefaultRow(['tiff', '["tif","tiff"]', 'graphics', 'image/tiff', '^II\*\x00|^MM\x00\*', 'TIFF', 0, 1]);
        $this->insertDefaultRow(['bmp', '["bmp"]', 'graphics', 'image/bmp', '^BM.{10}\x00\x00.{1}\x00\x00\x00.{8}\x01\x00.{1}\x00', 'BMP', 0, 1]);
        $this->insertDefaultRow(['icon', '["ico"]', 'graphics', 'image/x-icon', '^\x00\x00\x01\x00', 'Icon', 0, 1]);
        $this->insertDefaultRow(['photoshop', '["psd"]', 'graphics', 'image/vnd.adobe.photoshop', '^8BPS\x00\x01', 'Photoshop', 0, 1]);
        $this->insertDefaultRow(['tga', '["tga"]', 'graphics', 'image/x-tga', '^.{1}\x01[\x01\x09].{4}[\x0F\x10\x18\x20]|^.{1}\x00[\x02\x03\x0A\x0B]\x00{5}|TRUEVISION-XFILE.\x00$', 'Truevision TGA', 0, 1]);
        $this->insertDefaultRow(['pict', '["pct", "pic"]', 'graphics', 'image/x-pict', '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00\xFF)', 'QuickDraw/PICT', 0, 1]);
        $this->insertDefaultRow(['art', '["art"]', 'graphics', 'application/octet-stream', '^JG[\x03-\x04]\x0E', 'AOL ART', 0, 1]);
        $this->insertDefaultRow(['cel', '["cel"]', 'graphics', 'application/octet-stream', '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)', 'Kisekae CEL', 0, 1]);
        $this->insertDefaultRow(['kcf', '["kcf"]', 'graphics', 'application/octet-stream', '^KiSS\x10)', 'Kisekae Pallete', 0, 1]);
        $this->insertDefaultRow(['ani', '["ani"]', 'graphics', 'application/x-navi-animation', '^RIFF.{4}ACON', 'Windows Animated Cursor', 0, 1]);
        $this->insertDefaultRow(['icns', '["icns"]', 'graphics', 'image/x-icns', '^icns', 'Mac OS Icon', 0, 1]);
        $this->insertDefaultRow(['illustrator', '["ai"]', 'graphics', 'application/postscript', '^%!PS-Adobe|%PDF', 'Adobe Illustrator', 0, 1]);
        $this->insertDefaultRow(['postscript', '["ps"]', 'graphics', 'application/postscript', '%!PS', 'PostScript', 0, 1]);
        $this->insertDefaultRow(['eps', '["eps", "epsf"]', 'graphics', 'application/postscript', '^\xC5\xD0\xD3\xC6|^%!PS-Adobe-[0-9]\.[0-9] EPSF-[0-9]\.[0-9]', 'Encapsulated PostScript', 0, 1]);
        $this->insertDefaultRow(['webp', '["webp"]', 'graphics', 'image/webp', '^RIFF.{4}WEBP', 'WebP', 0, 1]);
        $this->insertDefaultRow(['wbmp', '["wbmp"]', 'graphics', 'image/vnd.wap.wbmp', '^\x00[\x00|\x01]', 'Wireless Bitmap', 0, 1]);
        $this->insertDefaultRow(['xbitmap', '["xbm"]', 'graphics', 'image/x-xbitmap', '^#define ', 'X BitMap', 0, 1]);
        $this->insertDefaultRow(['xpixmap', '["xpm"]', 'graphics', 'image/x-xpixmap', '^\/\* XPM \*\/\x0D\x0Astatic char \*', 'X PixMap', 0, 1]);

        // Audio formats
        $this->insertDefaultRow(['', null, 'audio', null, null, 'Audio', 1, 1]);
        $this->insertDefaultRow(['wave', '["wav"]', 'audio', 'audio/x-wav', '^RIFF.{4}WAVEfmt', 'WAVE', 0, 1]);
        $this->insertDefaultRow(['aiff', '["aif","aiff"]', 'audio', 'audio/x-aiff', '^FORM.{4}AIFF', 'AIFF', 0, 1]);
        $this->insertDefaultRow(['mp3', '["mp3"]', 'audio', 'audio/mpeg', '^ID3|\xFF[\xE3\xF2\xF3\xFA\FB]{1}', 'MP3', 0, 1]);
        $this->insertDefaultRow(['m4a', '["m4a"]', 'audio', 'audio/x-m4a', '^.{4}ftypM4A', 'MPEG-4 Audio', 0, 1]);
        $this->insertDefaultRow(['flac', '["flac"]', 'audio', 'audio/x-flac', '^fLaC\x00\x00\x00\x22', 'FLAC', 0, 1]);
        $this->insertDefaultRow(['aac', '["aac"]', 'audio', 'audio/aac', '^ADIF|^\xFF(?:\xF1|\xF9)', 'AAC', 0, 1]);
        $this->insertDefaultRow(['ogg-audio', '["oga","ogg"]', 'audio', 'audio/ogg', '^OggS', 'OGG Audio', 0, 1]);
        $this->insertDefaultRow(['au', '["au","snd"]', 'audio', 'audio/basic', '^\.snd', 'AU', 0, 1]);
        $this->insertDefaultRow(['ac3', '["ac3"]', 'audio', 'audio/ac3', '^\x0B\x77', 'AC3', 0, 1]);
        $this->insertDefaultRow(['wma', '["wma"]', 'audio', 'audio/x-ms-wma', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Audio', 0, 1]);
        $this->insertDefaultRow(['midi', '["mid","midi"]', 'audio', 'audio/midi', '^MThd\x00{3}\x06\x00[\x00-\x02].{4}MTrk', 'MIDI', 0, 1]);
        $this->insertDefaultRow(['mka', '["mka"]', 'audio', 'audio/x-matroska', '^\x1A\x45\xDF\xA3.{0,32}\x42\x82\x88matroska\x42\x87', 'Matroska Audio', 0, 1]);

        // Video formats
        $this->insertDefaultRow(['', null, 'video', null, null, 'Video', 1, 1]);
        $this->insertDefaultRow(['mpeg', '["mpg","mpeg","mpe","m1v","m2v"]', 'video', 'video/mpeg', '^\x00\x00\x01[\xB3\xBA]', 'MPEG-1/MPEG-2', 0, 1]);
        $this->insertDefaultRow(['quicktime', '["mov"]', 'video', 'video/quicktime', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime', 0, 1]);
        $this->insertDefaultRow(['avi', '["avi"]', 'video', 'video/x-msvideo', '^RIFF.{4}AVI\x20LIST', 'AVI', 0, 1]);
        $this->insertDefaultRow(['wmv', '["wmv"]', 'video', 'video/x-ms-wmv', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Video', 0, 1]);
        $this->insertDefaultRow(['mpeg4', '["mp4"]', 'video', 'video/mp4', '^.{4}ftyp.{0,64}(?:mp42|mp41|isom|iso2)', 'MPEG-4', 0, 1]);
        $this->insertDefaultRow(['m4v', '["m4v"]', 'video', 'video/x-m4v', '^.{4}ftypM4V', 'Apple MPEG-4', 0, 1]);
        $this->insertDefaultRow(['mkv', '["mkv"]', 'video', 'video/x-matroska', '^\x1A\x45\xDF\xA3.{0,32}\x42\x82\x88matroska\x42\x87', 'Matroska Video', 0, 1]);
        $this->insertDefaultRow(['flv', '["flv"]', 'video', 'video/x-flv', '^FLV\x01', 'Flash Video', 0, 1]);
        $this->insertDefaultRow(['webm', '["webm"]', 'video', 'video/webm', '^\x1A\x45\xDF\xA3.{0,32}\x42\x82\x84webm\x42\x87', 'WebM', 0, 1]);
        $this->insertDefaultRow(['3gp', '["3gp"]', 'video', 'video/3gpp', '^.{4}ftyp3gp', '3GP', 0, 1]);
        $this->insertDefaultRow(['ogg-video', '["ogv"]', 'video', 'video/ogg', '^OggS', 'Ogg Video', 0, 1]);

        // General document formats
        $this->insertDefaultRow(['', null, 'document', null, null, 'Document', 1, 1]);
        $this->insertDefaultRow(['rtf', '["rtf"]', 'document', 'text/rtf', '{\\rtf[1]?', 'Rich Text', 0, 1]);
        $this->insertDefaultRow(['pdf', '["pdf"]', 'document', 'application/pdf', '^%PDF-[0-9]\.[0-9]', 'PDF', 0, 1]);
        $this->insertDefaultRow(['doc', '["doc"]', 'document', 'application/msword', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1.{20}\xFE\xFF', 'MS Word', 0, 1]);
        $this->insertDefaultRow(['docx', '["docx"]', 'document', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '^PK\x03\x04', 'MS Open XML Word', 0, 1]);
        $this->insertDefaultRow(['ppt', '["ppt"]', 'document', 'application/vnd.ms-powerpoint', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1.{20}\xFE\xFF', 'MS PowerPoint', 0, 1]);
        $this->insertDefaultRow(['pptx', '["pptx"]', 'document', 'application/vnd.openxmlformats-officedocument.presentationml.document', '^PK\x03\x04', 'MS Open XML Presentation', 0, 1]);
        $this->insertDefaultRow(['xls', '["xls"]', 'document', 'application/vnd.ms-excel', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1.{20}\xFE\xFF', 'MS Excel', 0, 1]);
        $this->insertDefaultRow(['xlsx', '["xlsx"]', 'document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.document', '^PK\x03\x04', 'MS Open XML Spreadsheet', 0, 1]);
        $this->insertDefaultRow(['plaintext', '["txt"]', 'document', 'text/plain', '', 'Plaintext', 0, 1]);

        // Archive formats
        $this->insertDefaultRow(['', null, 'archive', null, null, 'Archive', 1, 1]);
        $this->insertDefaultRow(['gzip', '["gz","tgz","gzip"]', 'archive', 'application/x-gzip', '^\x1F[\x8B\x9E]\x08', 'GZip', 0, 1]);
        $this->insertDefaultRow(['bzip2', '["bz2","tbz2","tbz"]', 'archive', 'application/x-bzip2', '^BZh.{1}1AY&SY', 'bzip2', 0, 1]);
        $this->insertDefaultRow(['tar', '["tar"]', 'archive', 'application/x-tar', '^.{257}ustar|[\x21-\xEF].{104}[\x30-\x37][\20-\x37]\x00.{5}{2}', 'TAR', 0, 1]);
        $this->insertDefaultRow(['7zip', '["7z"]', 'archive', 'application/x-7z-compressed', '^\x37\x7A\xBC\xAF\x27\x1C', '7z', 0, 1]);
        $this->insertDefaultRow(['binhex', '["hqx"]', 'archive', 'application/mac-binhex40', '^\(This file must be converted with BinHex', 'BinHex', 0, 1]);
        $this->insertDefaultRow(['lha', '["lzh","lha"]', 'archive', 'application/x-lzh-compressed', '^.{2}-(?:lh[0-7])-', 'LHA', 0, 1]);
        $this->insertDefaultRow(['zip', '["zip"]', 'archive', 'application/zip', '^.{0,4}PK(?:[\x03\x04]|[\x05\x06]|[\x07\x08])', 'Zip', 0, 1]);
        $this->insertDefaultRow(['rar', '["rar"]', 'archive', 'application/vnd.rar', '^Rar\x21\x1A\x07[\x00\x01]', 'RAR', 0, 1]);
        $this->insertDefaultRow(['stuffit', '["sit","sitx"]', 'archive', 'application/x-stuffit', '^StuffIt|^SIT\!', 'StuffIt', 0, 1]);
        $this->insertDefaultRow(['iso', '["iso"]', 'archive', 'application/x-iso9660-image', '^(?:.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image', 0, 1]);
        $this->insertDefaultRow(['dmg', '["dmg"]', 'archive', 'application/x-apple-diskimage', 'koly.{508}$', 'Apple Disk Image', 0, 1]);

        // Other formats
        $this->insertDefaultRow(['', null, 'other', null, null, 'Other', 1, 1]);
        $this->insertDefaultRow(['swf', '["swf"]', 'other', 'application/x-shockwave-flash', '^CWS|FWS|ZWS', 'Flash/Shockwave', 0, 1]);
        $this->insertDefaultRow(['blorb', '["blorb","blb","gblorb","glb","zblorb","zlb"]', 'other', 'application/x-blorb', '^FORM.{4}IFRSRIdx', 'Blorb', 0, 1]);
    }
}