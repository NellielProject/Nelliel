<?php

declare(strict_types=1);


namespace Nelliel\Tables;

defined('NELLIEL_VERSION') or die('NOPE.AVI');

use PDO;

class TableFiletypes extends Table
{
    public const SCHEMA_VERSION = 1;
    public const PHP_TYPES = [
        'format' => 'string',
        'extensions' => 'string',
        'category' => 'string',
        'mimetypes' => 'string',
        'magic_regex' => 'string',
        'label' => 'string',
        'enabled' => 'boolean',
        'moar' => 'string'];

    public const PDO_TYPES = [
        'format' => PDO::PARAM_STR,
        'extensions' => PDO::PARAM_STR,
        'category' => PDO::PARAM_STR,
        'mimetypes' => PDO::PARAM_STR,
        'magic_regex' => PDO::PARAM_STR,
        'label' => PDO::PARAM_STR,
        'enabled' => PDO::PARAM_INT,
        'moar' => PDO::PARAM_STR];

    function __construct($database, $sql_compatibility)
    {
        parent::__construct($database, $sql_compatibility);
        $this->table_name = NEL_FILETYPES_TABLE;
        $this->column_checks = [
            'format' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'extensions' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'category' => ['row_check' => true, 'auto_inc' => false, 'update' => false],
            'mimetypes' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'magic_regex' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'label' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'enabled' => ['row_check' => false, 'auto_inc' => false, 'update' => false],
            'moar' => ['row_check' => false, 'auto_inc' => false, 'update' => false]];
    }

    public function buildSchema(array $other_tables = null)
    {
        $options = $this->sql_compatibility->tableOptions();
        $schema = '
        CREATE TABLE ' . $this->table_name . ' (
            format          VARCHAR(50) NOT NULL,
            extensions      TEXT NOT NULL,
            category        VARCHAR(50) DEFAULT NULL,
            mimetypes       TEXT NOT NULL,
            magic_regex     TEXT NOT NULL,
            label           VARCHAR(255) NOT NULL,
            enabled         SMALLINT NOT NULL DEFAULT 0,
            moar            ' . $this->sql_compatibility->textType('LONGTEXT') . ' DEFAULT NULL,
            CONSTRAINT pk_' . $this->table_name . ' PRIMARY KEY (format),
            CONSTRAINT fk_filetypes__filetype_categories
            FOREIGN KEY (category) REFERENCES ' . NEL_FILETYPE_CATEGORIES_TABLE . ' (category)
            ON UPDATE CASCADE
            ON DELETE SET NULL
        ) ' . $options . ';';

        return $schema;
    }

    public function postCreate(array $other_tables = null)
    {
    }

    public function insertDefaults()
    {
        // These regexes should not be considered authoritative for processing a format!
        // They are designed to identify files in combination with a known extension
        // IMPORTANT: If the regex has / in it, that needs to be escaped since that's the delimiter Nelliel uses

        // Graphics formats
        $this->insertDefaultRow(['jpeg', '["jpg", "jpe", "jpeg"]', 'graphics', '["image/jpeg"]', '^\xFF\xD8\xFF', 'JPEG', 1]);
        $this->insertDefaultRow(['gif', '["gif"]', 'graphics', '["image/gif"]', '^(?:GIF87a|GIF89a)', 'GIF', 1]);
        $this->insertDefaultRow(['png', '["png"]', 'graphics', '["image/png"]', '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A', 'PNG', 1]);
        $this->insertDefaultRow(['jpeg2000', '["jp2"]', 'graphics', '["image/jp2"]', '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A', 'JPEG 2000', 1]);
        $this->insertDefaultRow(['tiff', '["tif", "tiff"]', 'graphics', '["image/tiff"]', '^II\*\x00|^MM\x00\*', 'TIFF', 1]);
        $this->insertDefaultRow(['bmp', '["bmp"]', 'graphics', '["image/bmp", "image/x-bmp"]', '^BM.{10}\x00\x00.{1}\x00\x00\x00.{8}\x01\x00.{1}\x00', 'BMP', 1]);
        $this->insertDefaultRow(['icon', '["ico"]', 'graphics', '["image/vnd.microsoft.icon", "image/x-icon"]', '^\x00\x00\x01\x00', 'Icon', 1]);
        $this->insertDefaultRow(['photoshop', '["psd"]', 'graphics', '["image/vnd.adobe.photoshop"]', '^8BPS\x00\x01', 'Photoshop', 1]);
        $this->insertDefaultRow(['tga', '["tga"]', 'graphics', '["image/targa", "image/x-tga"]', '^.{1}\x01[\x01\x09].{4}[\x0F\x10\x18\x20]|^.{1}\x00[\x02\x03\x0A\x0B]\x00{5}|TRUEVISION-XFILE.\x00$', 'Truevision TGA', 1]);
        $this->insertDefaultRow(['pict', '["pct", "pic"]', 'graphics', '["image/pict", "image/x-pict"]', '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00\xFF)', 'QuickDraw/PICT', 1]);
        $this->insertDefaultRow(['art', '["art"]', 'graphics', '["application/octet-stream"]', '^JG[\x03-\x04]\x0E', 'AOL ART', 1]);
        $this->insertDefaultRow(['cel', '["cel"]', 'graphics', '["application/octet-stream"]', '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)', 'Kisekae CEL', 1]);
        $this->insertDefaultRow(['kcf', '["kcf"]', 'graphics', '["application/octet-stream"]', '^KiSS\x10)', 'Kisekae Pallete', 1]);
        $this->insertDefaultRow(['ani', '["ani"]', 'graphics', '["application/x-navi-animation"]', '^RIFF.{4}ACON', 'Windows Animated Cursor', 1]);
        $this->insertDefaultRow(['icns', '["icns"]', 'graphics', '["image/x-icns"]', '^icns', 'Mac OS Icon', 1]);
        $this->insertDefaultRow(['illustrator', '["ai"]', 'graphics', '["application/postscript"]', '^%!PS-Adobe|%PDF', 'Adobe Illustrator', 1]);
        $this->insertDefaultRow(['postscript', '["ps"]', 'graphics', '["application/postscript"]', '%!PS', 'PostScript', 1]);
        $this->insertDefaultRow(['eps', '["eps", "epsf"]', 'graphics', '["application/postscript"]', '^\xC5\xD0\xD3\xC6|^%!PS-Adobe-[0-9]\.[0-9] EPSF-[0-9]\.[0-9]', 'Encapsulated PostScript', 1]);
        $this->insertDefaultRow(['webp', '["webp"]', 'graphics', '["image/webp"]', '^RIFF.{4}WEBP', 'WebP', 1]);
        $this->insertDefaultRow(['wbmp', '["wbmp"]', 'graphics', '["image/vnd.wap.wbmp"]', '^\x00[\x00|\x01]', 'Wireless Bitmap', 1]);
        $this->insertDefaultRow(['xbitmap', '["xbm"]', 'graphics', '["image/x-xbitmap"]', '^#define ', 'X BitMap', 1]);
        $this->insertDefaultRow(['xpixmap', '["xpm"]', 'graphics', '["image/x-xpixmap"]', '^\/\* XPM \*\/\x0D\x0Astatic char \*', 'X PixMap', 1]);
        $this->insertDefaultRow(['avif', '["avif"]', 'graphics', '["image/avif"]', '^.{4}ftyp(?:avif|avis|mif1)', 'AVIF', 1]);
        $this->insertDefaultRow(['apng', '["png", "apng"]', 'graphics', '["image/apng", "image/vnd.mozilla.apng"]', '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A.*acTL', 'APNG', 1]);
        $this->insertDefaultRow(['jpx', '["jpx", "jpf"]', 'graphics', '["image/jpx"]', '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A\x87\x0A.{4)\x66\x74\x79\x70\x78\x20', 'JPX', 1]);
        $this->insertDefaultRow(['flif', '["flif"]', 'graphics', '["image/flif"]', '^FLIF[1|3|4|A|C|D|Q|S|T|a|c|d][0|1|2]', 'Free Lossless Image Format', 1]);
        $this->insertDefaultRow(['xcf', '["xcf"]', 'graphics', '["image/x-xcf"]', '^gimp xcf', 'GIMP XCF', 1]);
        $this->insertDefaultRow(['jpeg-xr', '["jxr", "hdp", "wdp"]', 'graphics', '["image/jxr", "image/vnd.ms-photo"]', '^\x49\x49\xBC\x01', 'JPEG XR', 1]);
        $this->insertDefaultRow(['jpeg-xl', '["jxl"]', 'graphics', '["image/jxl"]', '^(?:\xFF\x0A|\x00\x00\x00\x0C\x4A\x58\x4C\x20\x0D\x0A\x87\x0A)', 'JPEG XL', 1]);
        $this->insertDefaultRow(['bpg', '["bpg"]', 'graphics', '["image/bpg"]', '^\x42\x50\x47\xFB', 'Better Portable Graphics', 1]);
        $this->insertDefaultRow(['heif', '["heif", "heic"]', 'graphics', '["image/heif", "image/heic"]', '^.{4}ftyp(?:mif1|msf1|heic|heix|hevc|hevx)', 'HEIF/HEIC', 1]);
        $this->insertDefaultRow(['cur', '["cur"]', 'graphics', '["image/x-win-bitmap"]', '^\x00\x00\x02\x00', 'Windows Cursor', 1]);

        // Audio formats
        $this->insertDefaultRow(['wave', '["wav"]', 'audio', '["audio/wav", "audio/x-wav"]', '^RIFF.{4}WAVEfmt', 'WAVE', 1]);
        $this->insertDefaultRow(['aiff', '["aif", "aiff"]', 'audio', '["audio/aiff", "audio/x-aiff"]', '^FORM.{4}AIFF', 'AIFF', 1]);
        $this->insertDefaultRow(['mp3', '["mp3"]', 'audio', '["audio/mpeg"]', '^ID3|\xFF[\xE3\xF2\xF3\xFA\FB]{1}', 'MP3', 1]);
        $this->insertDefaultRow(['m4a', '["m4a"]', 'audio', '["audio/mp4", "audio/x-m4a"]', '^.{4}ftypM4A', 'MPEG-4 Audio', 1]);
        $this->insertDefaultRow(['flac', '["flac"]', 'audio', '["audio/flac", "audio/x-flac"]', '^fLaC\x00\x00\x00\x22', 'FLAC', 1]);
        $this->insertDefaultRow(['aac', '["aac"]', 'audio', '["audio/aac"]', '^ADIF|^\xFF(?:\xF1|\xF9)', 'AAC', 1]);
        $this->insertDefaultRow(['ogg-audio', '["oga", "ogg"]', 'audio', '["audio/ogg"]', '^OggS', 'OGG Audio', 1]);
        $this->insertDefaultRow(['au', '["au","snd"]', 'audio', '["audio/basic"]', '^\.snd', 'AU', 1]);
        $this->insertDefaultRow(['ac3', '["ac3"]', 'audio', '["audio/ac3"]', '^\x0B\x77', 'AC3', 1]);
        $this->insertDefaultRow(['wma', '["wma"]', 'audio', '["audio/x-ms-wma"]', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Audio', 1]);
        $this->insertDefaultRow(['midi', '["mid", "midi"]', 'audio', '["audio/midi", "audio/x-midi"]', '^MThd\x00{3}\x06\x00[\x00-\x02].{4}MTrk', 'MIDI', 1]);
        $this->insertDefaultRow(['mka', '["mka"]', 'audio', '["audio/x-matroska"]', '^\x1A\x45\xDF\xA3.{0,32}\x42\x82\x88matroska\x42\x87', 'Matroska Audio', 1]);
        $this->insertDefaultRow(['mp1', '["mp1"]', 'audio', '["audio/mpeg", "audio/MPA"]', '^\xFF[\xF6\xF7\xFE\xFF]{1}', 'MP1', 1]);
        $this->insertDefaultRow(['mp2', '["mp2", "mpa", "mpw"]', 'audio', '["audio/mpeg", "audio/MPA"]', '^\xFF[\xF4\xF5\xFC\xFD]{1}', 'MP2', 1]);

        // Video formats
        $this->insertDefaultRow(['mpeg', '["mpg", "mpeg", "mpe", "m1v", "m2v"]', 'video', '["video/mpeg"]', '^\x00\x00\x01[\xB3\xBA]', 'MPEG-1/MPEG-2', 1]);
        $this->insertDefaultRow(['quicktime', '["mov"]', 'video', '["video/quicktime"]', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime', 1]);
        $this->insertDefaultRow(['avi', '["avi"]', 'video', '["video/x-msvideo"]', '^RIFF.{4}AVI\x20LIST', 'AVI', 1]);
        $this->insertDefaultRow(['wmv', '["wmv"]', 'video', '["video/x-ms-wmv"]', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Video', 1]);
        $this->insertDefaultRow(['mpeg4', '["mp4"]', 'video', '["video/mp4"]', '^.{4}ftyp.{0,64}(?:mp42|mp41|isom|iso2)', 'MPEG-4', 1]);
        $this->insertDefaultRow(['m4v', '["m4v"]', 'video', '["video/x-m4v"]', '^.{4}ftypM4V', 'Apple MPEG-4', 1]);
        $this->insertDefaultRow(['mkv', '["mkv"]', 'video', '["video/x-matroska"]', '^\x1A\x45\xDF\xA3.{0,32}\x42\x82\x88matroska\x42\x87', 'Matroska Video', 1]);
        $this->insertDefaultRow(['flv', '["flv"]', 'video', '["video/x-flv"]', '^FLV\x01', 'Flash Video', 1]);
        $this->insertDefaultRow(['webm', '["webm"]', 'video', '["video/webm"]', '^\x1A\x45\xDF\xA3.{0,32}\x42\x82\x84webm\x42\x87', 'WebM', 1]);
        $this->insertDefaultRow(['3gp', '["3gp", "3gpp"]', 'video', '["video/3gpp"]', '^.{4}ftyp3gp', '3GP', 1]);
        $this->insertDefaultRow(['ogg-video', '["ogv"]', 'video', '["video/ogg"]', '^OggS', 'Ogg Video', 1]);
        $this->insertDefaultRow(['mjpeg2000', '["mj2", "mjp2"]', 'video', '["video/mj2"]', '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A\x87\x0A.{4}\x66\x74\x79\x70\x6D\x6A\x70\x32', 'Motion JPEG 2000', 1]);

        // General document formats
        $this->insertDefaultRow(['rtf', '["rtf"]', 'document', '["text/rtf", "application/rtf"]', '{\\rtf[1]?', 'Rich Text', 1]);
        $this->insertDefaultRow(['pdf', '["pdf"]', 'document', '["application/pdf"]', '^%PDF-[0-9]\.[0-9]', 'PDF', 1]);
        $this->insertDefaultRow(['doc', '["doc"]', 'document', '["application/vnd.ms-word", "application/msword"]', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1.{20}\xFE\xFF', 'MS Word', 1]);
        $this->insertDefaultRow(['docx', '["docx"]', 'document', '["application/vnd.openxmlformats-officedocument.wordprocessingml.document"]', '^PK\x03\x04', 'MS Open XML Word', 1]);
        $this->insertDefaultRow(['ppt', '["ppt"]', 'document', '["application/vnd.ms-powerpoint"]', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1.{20}\xFE\xFF', 'MS PowerPoint', 1]);
        $this->insertDefaultRow(['pptx', '["pptx"]', 'document', '["application/vnd.openxmlformats-officedocument.presentationml.document"]', '^PK\x03\x04', 'MS Open XML Presentation', 1]);
        $this->insertDefaultRow(['xls', '["xls"]', 'document', '["application/vnd.ms-excel"]', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1.{20}\xFE\xFF', 'MS Excel', 1]);
        $this->insertDefaultRow(['xlsx', '["xlsx"]', 'document', '["application/vnd.openxmlformats-officedocument.spreadsheetml.document"]', '^PK\x03\x04', 'MS Open XML Spreadsheet', 1]);
        $this->insertDefaultRow(['plaintext', '["txt"]', 'document', '["text/plain"]', '', 'Plaintext', 1]);

        // Archive formats
        $this->insertDefaultRow(['gzip', '["gz", "tgz", "gzip"]', 'archive', '["application/gzip", "application/x-gzip"]', '^\x1F[\x8B\x9E]\x08', 'GZip', 1]);
        $this->insertDefaultRow(['bzip2', '["bz2", "tbz2", "tbz"]', 'archive', '["application/x-bzip2"]', '^BZh.{1}1AY&SY', 'bzip2', 1]);
        $this->insertDefaultRow(['tar', '["tar"]', 'archive', '["application/x-tar"]', '^.{257}ustar|[\x21-\xEF].{104}[\x30-\x37][\20-\x37]\x00.{5}{2}', 'TAR', 1]);
        $this->insertDefaultRow(['7zip', '["7z"]', 'archive', '["application/x-7z-compressed"]', '^\x37\x7A\xBC\xAF\x27\x1C', '7z', 1]);
        $this->insertDefaultRow(['binhex', '["hqx"]', 'archive', '["application/mac-binhex40"]', '^\(This file must be converted with BinHex', 'BinHex', 1]);
        $this->insertDefaultRow(['lha', '["lzh", "lha"]', 'archive', '["application/x-lzh-compressed"]', '^.{2}-(?:lh[0-7])-', 'LHA', 1]);
        $this->insertDefaultRow(['zip', '["zip"]', 'archive', '["application/zip"]', '^.{0,4}PK(?:[\x03\x04]|[\x05\x06]|[\x07\x08])', 'Zip', 1]);
        $this->insertDefaultRow(['rar', '["rar"]', 'archive', '["application/vnd.rar", "application/x-rar-compressed"]', '^Rar\x21\x1A\x07[\x00\x01]', 'RAR', 1]);
        $this->insertDefaultRow(['stuffit', '["sit", "sitx"]', 'archive', '["application/x-stuffit", "application/x-sit"]', '^StuffIt|^SIT\!', 'StuffIt', 1]);
        $this->insertDefaultRow(['iso', '["iso"]', 'archive', '["application/x-iso9660-image"]', '^(?:.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image', 1]);
        $this->insertDefaultRow(['dmg', '["dmg"]', 'archive', '["application/x-apple-diskimage"]', 'koly.{508}$', 'Apple Disk Image', 1]);
        $this->insertDefaultRow(['compress', '["z"]', 'archive', '["application/x-compress"]', '^\x1F\x9D', 'compress', 1]);

        // Font formats
        $this->insertDefaultRow(['truetype', '["ttf"]', 'font', '["font/ttf"]', '^\x00\x01\x00\x00\x00', 'TrueType', 1]);
        $this->insertDefaultRow(['opentype', '["otf", "ttf"]', 'font', '["font/otf"]', '^(?:\x00\x01\x00\x00\x00|OTTO)', 'OpenType', 1]);
        $this->insertDefaultRow(['woff', '["woff", "woff2"]', 'font', '["font/woff", "font/woff2"]', '^(?:wOFF|wOF2)', 'WOFF', 1]);

        // Other formats
        $this->insertDefaultRow(['swf', '["swf"]', 'other', '["application/vnd.adobe.flash-movie", "application/x-shockwave-flash"]', '^CWS|FWS|ZWS', 'Flash/Shockwave', 1]);
        $this->insertDefaultRow(['blorb', '["blorb","blb","gblorb","glb","zblorb","zlb"]', 'other', '["application/x-blorb"]', '^FORM.{4}IFRSRIdx', 'Blorb', 1]);
    }
}