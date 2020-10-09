<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableFiletypes extends TableHandler
{

    function __construct($database, $sql_compatibility)
    {
        $this->database = $database;
        $this->sql_compatibility = $sql_compatibility;
        $this->table_name = NEL_FILETYPES_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'base_extension' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'format' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'mime' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'sub_extensions' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'id_regex' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'label' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'type_def' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => true, 'auto_inc' => false],
            'enabled' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function buildSchema(array $other_tables = null)
    {
        $auto_inc = $this->sql_compatibility->autoincrementColumn('INTEGER');
        $options = $this->sql_compatibility->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry           " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            base_extension  VARCHAR(255) NOT NULL,
            type            VARCHAR(255) DEFAULT NULL,
            format          VARCHAR(255) DEFAULT NULL,
            mime            VARCHAR(255) DEFAULT NULL,
            sub_extensions  TEXT DEFAULT NULL,
            id_regex        TEXT DEFAULT NULL,
            label           VARCHAR(255) DEFAULT NULL,
            type_def        SMALLINT NOT NULL DEFAULT 0,
            enabled         SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $schema;
    }

    public function insertDefaults()
    {
        // IMPORTANT: If the regex has / in it, that needs to be escaped since that's the delimiter Nelliel uses
        $this->insertDefaultRow(['', 'graphics', null, null, null, null, 'Graphics files', 1, 1]);
        $this->insertDefaultRow(['jpg', 'graphics', 'jpeg', 'image/jpeg', '["jpe","jpeg"]', '^\xFF\xD8\xFF', 'JPEG', 0, 1]);
        $this->insertDefaultRow(['gif', 'graphics', 'gif', 'image/gif', '[]', '^(?:GIF87a|GIF89a)', 'GIF', 0, 1]);
        $this->insertDefaultRow(['png', 'graphics', 'png', 'image/png', '[]', '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A', 'PNG', 0, 1]);
        $this->insertDefaultRow(['jp2', 'graphics', 'jpeg2000', 'image/jp2', '["j2k"]', '^\x00\x00\x00\x0C\x6A\x50\x2\\x20\x0D\x0A', 'JPEG2000', 0, 1]);
        $this->insertDefaultRow(['tiff', 'graphics', 'tiff', 'image/tiff', '["tif"]', '^I\x20?I\x2A\x00|^MM\x00[\x2A-\x2B]', 'TIFF', 0, 1]);
        $this->insertDefaultRow(['bmp', 'graphics', 'bmp', 'image/x-bmp', '[]', '^BM', 'BMP', 0, 1]);
        $this->insertDefaultRow(['ico', 'graphics', 'icon', 'image/x-icon', '[]', '^\x00\x00\x01\x00', 'Icon', 0, 1]);
        $this->insertDefaultRow(['psd', 'graphics', 'photoshop', 'image/vnd.adobe.photoshop', '[]', '^8BPS\x00\x01', 'PSD (Photoshop)', 0, 1]);
        $this->insertDefaultRow(['tgs', 'graphics', 'tga', 'image/x-targa', '[]', '^.{1}\x00', 'Truevision TGA', 0, 1]);
        $this->insertDefaultRow(['pict', 'graphics', 'pict', 'image/x-pict', '[]', '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00)', 'PICT', 0, 1]);
        $this->insertDefaultRow(['art', 'graphics', 'art', 'image/x-jg', '[]', '^JG[\x03-\x04]\x0E', 'AOL ART', 0, 1]);
        $this->insertDefaultRow(['cel', 'graphics', 'cel', 'application/octet-stream', '[]', '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)', 'Kisekae CEL', 0, 1]);
        $this->insertDefaultRow(['kcf', 'graphics', 'kcf', 'application/octet-stream', '[]', '^KiSS\x10)', 'Kisekae Pallete', 0, 1]);
        $this->insertDefaultRow(['ani', 'graphics', 'ani', 'application/x-navi-animation', '[]', '^RIFF\xF2\x19\x00\x00ACONLIST', 'Windows Animated Cursor', 0, 1]);
        $this->insertDefaultRow(['icns', 'graphics', 'icns', 'image/icns', '[]', '^icns', 'Mac OS Icon', 0, 1]);
        $this->insertDefaultRow(['ai', 'graphics', 'illustrator', 'application/postscript', '[]', '^%PDF', 'Adobe Illustrator', 0, 1]);
        $this->insertDefaultRow(['ps', 'graphics', 'postscript', 'application/postscript', '[]', '%!PS', 'PostScript', 0, 1]);
        $this->insertDefaultRow(['eps', 'graphics', 'eps', 'application/postscript', '[]', '^\xC5\xD0\xD3\xC6|%!PS-Adobe-[0-9]\.[0-9] EPSF-[0-9]\.[0-9]', 'Encapsulated PostScript', 0, 1]);
        $this->insertDefaultRow(['webp', 'graphics', 'webp', 'image/webp', '[]', '^RIFF.{4}WEBP', 'WebP', 0, 1]);
        $this->insertDefaultRow(['wbmp', 'graphics', 'wbmp', 'image/vnd.wap.wbmp', '[]', '^\x00[\x00|\x01]', 'Wireless Bitmap', 0, 1]);
        $this->insertDefaultRow(['xbm', 'graphics', 'xbm', 'image/x-xbitmap', '[]', '^#define ', 'X Bitmap', 0, 1]);
        $this->insertDefaultRow(['xpm', 'graphics', 'xpm', 'image/x-xpixmap', '[]', '^\/\* XPM \*\/\x0D\x0Astatic char \*', 'X PixMap', 0, 1]);
        $this->insertDefaultRow(['', 'audio', null, null, null, null, 'Audio files', 1, 1]);
        $this->insertDefaultRow(['wav', 'audio', 'wave', 'audio/x-wave', '[]', '^RIFF.{4}WAVEfmt', 'WAVE', 0, 1]);
        $this->insertDefaultRow(['aif', 'audio', 'aiff', 'audio/aiff', '["aiff"]', '^FORM.{4}AIFF', 'AIFF', 0, 1]);
        $this->insertDefaultRow(['mp3', 'audio', 'mp3', 'audio/mpeg', '[]', '^ID3|\xFF[\xE0-\xFF]{1}', 'MP3', 0, 1]);
        $this->insertDefaultRow(['m4a', 'audio', 'm4a', 'audio/m4a', '[]', '^.{4}ftypM4A', 'MPEG-4 Audio', 0, 1]);
        $this->insertDefaultRow(['flac', 'audio', 'flac', 'audio/x-flac', '[]', '^fLaC\x00\x00\x00\x22', 'FLAC', 0, 1]);
        $this->insertDefaultRow(['aac', 'audio', 'aac', 'audio/aac', '[]', '^ADIF|^\xFF(?:\xF1|\xF9)', 'AAC', 0, 1]);
        $this->insertDefaultRow(['ogg', 'audio', 'ogg-audio', 'audio/ogg', '[]', '^OggS', 'OGG Audio', 0, 1]);
        $this->insertDefaultRow(['au', 'audio', 'au', 'audio/basic', '["snd"]', '^\.snd', 'AU', 0, 1]);
        $this->insertDefaultRow(['ac3', 'audio', 'ac3', 'audio/ac3', '[]', '^\x0B\x77', 'AC3', 0, 1]);
        $this->insertDefaultRow(['wma', 'audio', 'wma', 'audio/x-ms-wma', '[]', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Audio', 0, 1]);
        $this->insertDefaultRow(['midi', 'audio', 'midi', 'audio/midi', '["mid"]', '^MThd', 'MIDI', 0, 1]);
        $this->insertDefaultRow(['', 'video', null, null, null, null, 'Video files', 1, 1]);
        $this->insertDefaultRow(['mpg', 'video', 'mpeg', 'video/mpeg', '["mpeg","mpe"]', '^\x00\x00\x01[\xB0-\xBF]', 'MPEG-1/MPEG-2', 0, 1]);
        $this->insertDefaultRow(['mov', 'video', 'quicktime', 'video/quicktime', '[]', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime Movie', 0, 1]);
        $this->insertDefaultRow(['avi', 'video', 'avi', 'video/x-msvideo', '[]', '^RIFF.{4}AVI\sx20LIST', 'AVI', 0, 1]);
        $this->insertDefaultRow(['wmv', 'video', 'wmv', 'video/x-ms-wmv', '[]', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Video', 0, 1]);
        $this->insertDefaultRow(['mp4', 'video', 'mpeg4', 'video/mp4', '[]', '^.{4}ftyp(?:iso2|isom|mp41|mp42)', 'MPEG-4 Media', 0, 1]);
        $this->insertDefaultRow(['m4v', 'video', 'm4v', 'video/x-m4v', '[]', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video', 0, 1]);
        $this->insertDefaultRow(['mkv', 'video', 'mkv', 'video/x-matroska', '[]', '^\x1A\x45\xDF\xA3', 'Matroska Media', 0, 1]);
        $this->insertDefaultRow(['flv', 'video', 'flv', 'video/x-flv', '[]', '^FLV\x01', 'Flash Video', 0, 1]);
        $this->insertDefaultRow(['webm', 'video', 'webm', 'video/webm', '[]', '^\x1A\x45\xDF\xA3', 'WebM', 0, 1]);
        $this->insertDefaultRow(['3gp', 'video', '3gp', 'video/3gpp', '[]', '^.{4}ftyp3gp', '3GP', 0, 1]);
        $this->insertDefaultRow(['ogv', 'video', 'ogg-video', 'video/ogg', '[]', '^OggS', 'Ogg Video', 0, 1]);
        $this->insertDefaultRow(['', 'document', null, null, null, null, 'Text and document files', 1, 1]);
        $this->insertDefaultRow(['rtf', 'document', 'rtf', 'application/rtf', '["rtf"]', '^\x7B\x5C\x72\x74\x66\x31', 'Rich Text', 0, 1]);
        $this->insertDefaultRow(['pdf', 'document', 'pdf', 'application/pdf', '["pdf"]', '^\x25PDF', 'PDF', 0, 1]);
        $this->insertDefaultRow(['doc', 'document', 'msword', 'application/msword', '[]', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1', 'Microsoft Word', 0, 1]);
        $this->insertDefaultRow(['docx', 'document', 'msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '[]', '^PK\x03\x04', 'MS Open XML Word', 0, 1]);
        $this->insertDefaultRow(['ppt', 'document', 'powerpoint', 'application/ms-powerpoint', '[]', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1', 'PowerPoint', 0, 1]);
        $this->insertDefaultRow(['pptx', 'document', 'powerpoint', 'application/vnd.openxmlformats-officedocument.spreadsheetml.document', '[]', '^PK\x03\x04', 'MS Open XML Presentation', 0, 1]);
        $this->insertDefaultRow(['xls', 'document', 'msexcel', 'application/ms-excel', '[]', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1', 'Microsoft Excel', 0, 1]);
        $this->insertDefaultRow(['xlsx', 'document', 'msexcel', 'application/vnd.openxmlformats-officedocument.presentationml.document', '[]', '^PK\x03\x04', 'MS Open XML Spreadsheet', 0, 1]);
        $this->insertDefaultRow(['txt', 'document', 'txt', 'text/plain', '[]', '', 'Plaintext', 0, 1]);
        $this->insertDefaultRow(['', 'archive', null, null, null, null, 'Archive files', 1, 1]);
        $this->insertDefaultRow(['gz', 'archive', 'gzip', 'application/gzip', '["tgz","gzip"]', '^\x1F\x8B\x08', 'GZip', 0, 1]);
        $this->insertDefaultRow(['bz2', 'archive', 'bzip2', 'application/x-bzip2', '["tbz2","tbz"]', '^BZh.{1}\x31\x41\x59\x26\x53\x59', 'bzip2', 0, 1]);
        $this->insertDefaultRow(['tar', 'archive', 'tar', 'application/x-tar', '[]', '^.{257}ustar', 'TAR', 0, 1]);
        $this->insertDefaultRow(['7z', 'archive', '7z', 'application/x-7z-compressed', '[]', '^\x37\x7A\xBC\xAF\x27\x1C', '7z', 0, 1]);
        $this->insertDefaultRow(['hqx', 'archive', 'binhex', 'application/binhex', '[]', '^\(This file must be converted with BinHex', 'Binhex', 0, 1]);
        $this->insertDefaultRow(['lzh', 'archive', 'lzh', 'application/x-lzh-compressed', '["lha"]', '^.{2}\x2D\x6C\x68', 'LZH', 0, 1]);
        $this->insertDefaultRow(['zip', 'archive', 'zip', 'application/zip', '[]', '^PK\x03\x04', 'Zip', 0, 1]);
        $this->insertDefaultRow(['rar', 'archive', 'rar', 'application/x-rar-compressed', '[]', '^Rar\x21\x1A\x07\x00', 'RAR', 0, 1]);
        $this->insertDefaultRow(['sit', 'archive', 'stuffit', 'application/x-stuffit', '["sitx"]', '^StuffIt \(c\)1997-|StuffIt\!|^SIT\!', 'StuffIt', 0, 1]);
        $this->insertDefaultRow(['iso', 'archive', 'iso', 'application/x-iso-image', '[]', '^(.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image', 0, 1]);
        $this->insertDefaultRow(['dmg', 'archive', 'dmg', 'application/x-apple-diskimage', '[]', 'koly.{508}$', 'Apple Disk Image', 0, 1]);
        $this->insertDefaultRow(['', 'other', null, null, null, null, 'Other files', 1, 1]);
        $this->insertDefaultRow(['swf', 'other', 'swf', 'application/x-shockwave-flash', '[]', '^CWS|FWS|ZWS', 'Flash/Shockwave', 0, 1]);
        $this->insertDefaultRow(['blorb', 'other', 'blorb', 'application/x-blorb', '["blb","gblorb","glb","zblorb","zlb"]', '^FORM.{4}IFRSRIdx', 'Blorb', 0, 1]);
    }
}