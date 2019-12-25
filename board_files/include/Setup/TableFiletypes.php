<?php

namespace Nelliel\Setup;

if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

use PDO;

class TableFiletypes extends TableHandler
{

    function __construct($database, $sql_helpers)
    {
        $this->database = $database;
        $this->sql_helpers = $sql_helpers;
        $this->table_name = FILETYPES_TABLE;
        $this->columns_data = [
            'entry' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => false, 'auto_inc' => true],
            'extension' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'parent_extension' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'type' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => true, 'auto_inc' => false],
            'format' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'mime' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'id_regex' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'label' => ['pdo_type' => PDO::PARAM_STR, 'row_check' => false, 'auto_inc' => false],
            'type_def' => ['pdo_type' => PDO::PARAM_INT, 'row_check' => true, 'auto_inc' => false]];
        $this->schema_version = 1;
    }

    public function setup()
    {
        $this->createTable();
        $this->insertDefaults();
    }

    public function createTable(array $other_tables = null)
    {
        $auto_inc = $this->sql_helpers->autoincrementColumn('INTEGER');
        $options = $this->sql_helpers->tableOptions();
        $schema = "
        CREATE TABLE " . $this->table_name . " (
            entry               " . $auto_inc[0] . " PRIMARY KEY " . $auto_inc[1] . " NOT NULL,
            extension           VARCHAR(255) NOT NULL,
            parent_extension    VARCHAR(255) DEFAULT NULL,
            type                VARCHAR(255) DEFAULT NULL,
            format              VARCHAR(255) DEFAULT NULL,
            mime                VARCHAR(255) DEFAULT NULL,
            id_regex            VARCHAR(512) DEFAULT NULL,
            label               VARCHAR(255) DEFAULT NULL,
            type_def            SMALLINT NOT NULL DEFAULT 0
        ) " . $options . ";";

        return $this->sql_helpers->createTableQuery($schema, $this->table_name);
    }

    public function insertDefaults()
    {
        $this->insertDefaultRow(['', null, 'graphics', null, null, null, 'Graphics files', 1]);
        $this->insertDefaultRow(['jpg', 'jpg', 'graphics', 'jpeg', 'image/jpeg', '^\xFF\xD8\xFF', 'JPEG', 0]);
        $this->insertDefaultRow(['jpeg', 'jpg', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['jpe', 'jpg', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['gif', 'gif', 'graphics', 'gif', 'image/gif', '^(?:GIF87a|GIF89a)', 'GIF', 0]);
        $this->insertDefaultRow(['png', 'png', 'graphics', 'png', 'image/png', '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A', 'PNG', 0]);
        $this->insertDefaultRow(['jp2', 'jp2', 'graphics', 'jpeg2000', 'image/jp2', '^\x00\x00\x00\x0C\x6A\x50\x2\\x20\x0D\x0A', 'JPEG2000', 0]);
        $this->insertDefaultRow(['j2k', 'jp2', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['tiff', 'tiff', 'graphics', 'tiff', 'image/tiff', '^I\x20?I\x2A\x00|^MM\x00[\x2A-\x2B]', 'TIFF', 0]);
        $this->insertDefaultRow(['tif', 'tiff', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['bmp', 'bmp', 'graphics', 'bmp', 'image/x-bmp', '^BM', 'BMP', 0]);
        $this->insertDefaultRow(['ico', 'ico', 'graphics', 'icon', 'image/x-icon', '^\x00\x00\x01\x00', 'Icon', 0]);
        $this->insertDefaultRow(['psd', 'psd', 'graphics', 'photoshop', 'image/vnd.adobe.photoshop', '^8BPS\x00\x01', 'PSD (Photoshop)', 0]);
        $this->insertDefaultRow(['tga', 'tga', 'graphics', 'tga', 'image/x-targa', '^.{1}\x00', 'Truevision TGA', 0]);
        $this->insertDefaultRow(['pict', 'pict', 'graphics', 'pict', 'image/x-pict', '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00)', 'PICT', 0]);
        $this->insertDefaultRow(['art', 'art', 'graphics', 'art', 'image/x-jg', '^JG[\x03-\x04]\x0E', 'AOL ART', 0]);
        $this->insertDefaultRow(['cel', 'cel', 'graphics', 'cel', 'application/octet-stream', '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)', 'Kisekae CEL', 0]);
        $this->insertDefaultRow(['kcf', 'kcf', 'graphics', 'kcf', 'application/octet-stream', '^KiSS\x10)', 'Kisekae Pallete', 0]);
        $this->insertDefaultRow(['ani', 'ani', 'graphics', 'ani', 'application/x-navi-animation', '^RIFF\xF2\x19\x00\x00ACONLIST', 'Windows Animated Cursor', 0]);
        $this->insertDefaultRow(['icns', 'icns', 'graphics', 'icns', 'image/icns', '^icns', 'Mac OS Icon', 0]);
        $this->insertDefaultRow(['ai', 'ai', 'graphics', 'illustrator', 'application/postscript', '^%PDF', 'Adobe Illustrator', 0]);
        $this->insertDefaultRow(['ps', 'ps', 'graphics', 'postscript', 'application/postscript', '%!PS', 'PostScript', 0]);
        $this->insertDefaultRow(['eps', 'eps', 'graphics', 'eps', 'application/postscript', '^\xC5\xD0\xD3\xC6|%!PS-Adobe-[0-9]\.[0-9] EPSF-[0-9]\.[0-9]', 'Encapsulated PostScript', 0]);
        $this->insertDefaultRow(['', null, 'audio', null, null, null, 'Audio files', 1]);
        $this->insertDefaultRow(['wav', 'wav', 'audio', 'wave', 'audio/x-wave', '^RIFF.{4}WAVEfmt', 'WAVE', 0]);
        $this->insertDefaultRow(['aif', 'aif', 'audio', 'aiff', 'audio/aiff', '^FORM.{4}AIFF', 'AIFF', 0]);
        $this->insertDefaultRow(['aiff', 'aif', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['mp3', 'mp3', 'audio', 'mp3', 'audio/mpeg', '^ID3|\xFF[\xE0-\xFF]{1}', 'MP3', 0]);
        $this->insertDefaultRow(['m4a', 'm4a', 'audio', 'm4a', 'audio/m4a', '^.{4}ftypM4A', 'MPEG-4 Audio', 0]);
        $this->insertDefaultRow(['flac', 'flac', 'audio', 'flac', 'audio/x-flac', '^fLaC\x00\x00\x00\x22', 'FLAC', 0]);
        $this->insertDefaultRow(['aac', 'aac', 'audio', 'aac', 'audio/aac', '^ADIF|^\xFF(?:\xF1|\xF9)', 'AAC', 0]);
        $this->insertDefaultRow(['ogg', 'ogg', 'audio', 'ogg-audio', 'audio/ogg', '^OggS', 'OGG Audio', 0]);
        $this->insertDefaultRow(['au', 'au', 'audio', 'au', 'audio/basic', '^\.snd', 'AU', 0]);
        $this->insertDefaultRow(['snd', 'au', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['ac3', 'ac3', 'audio', 'ac3', 'audio/ac3', '^\x0B\x77', 'AC3', 0]);
        $this->insertDefaultRow(['wma', 'wma', 'audio', 'wma', 'audio/x-ms-wma', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Audio', 0]);
        $this->insertDefaultRow(['midi', 'midi', 'audio', 'midi', 'audio/midi', '^MThd', 'MIDI', 0]);
        $this->insertDefaultRow(['mid', 'midi', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['', null, 'video', null, null, null, 'Video files', 1]);
        $this->insertDefaultRow(['mpg', 'mpg', 'video', 'mpeg', 'video/mpeg', '^\x00\x00\x01[\xB0-\xBF]', 'MPEG-1/MPEG-2', 0]);
        $this->insertDefaultRow(['mpeg', 'mpg', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['mpe', 'mpg', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['mov', 'mov', 'video', 'quicktime', 'video/quicktime', '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)', 'Quicktime Movie', 0]);
        $this->insertDefaultRow(['avi', 'avi', 'video', 'avi', 'video/x-msvideo', '^RIFF.{4}AVI\sx20LIST', 'AVI', 0]);
        $this->insertDefaultRow(['wmv', 'wmv', 'video', 'wmv', 'video/x-ms-wmv', '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C', 'Windows Media Video', 0]);
        $this->insertDefaultRow(['mp4', 'mp4', 'video', 'mpeg4', 'video/mp4', '^.{4}ftyp(?:iso2|isom|mp41|mp42)', 'MPEG-4 Media', 0]);
        $this->insertDefaultRow(['m4v', 'm4v', 'video', 'm4v', 'video/x-m4v', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video', 0]);
        $this->insertDefaultRow(['m4v', 'm4v', 'video', 'm4v', 'video/x-m4v', '^.{4}ftypmp(?:41|42|71)', 'MPEG-4 Video', 0]);
        $this->insertDefaultRow(['mkv', 'mkv', 'video', 'mkv', 'video/x-matroska', '^\x1A\x45\xDF\xA3', 'Matroska Media', 0]);
        $this->insertDefaultRow(['flv', 'flv', 'video', 'flv', 'video/x-flv', '^FLV\x01', 'Flash Video', 0]);
        $this->insertDefaultRow(['webm', 'webm', 'video', 'webm', 'video/webm', '^\x1A\x45\xDF\xA3', 'WebM', 0]);
        $this->insertDefaultRow(['3gp', '3gp', 'video', '3gp', 'video/3gpp', '^.{4}ftyp3gp', '3GP', 0]);
        $this->insertDefaultRow(['ogv', 'ogv', 'video', 'ogg-video', 'video/ogg', '^OggS', 'Ogg Video', 0]);
        $this->insertDefaultRow(['', null, 'document', null, null, null, 'Text and document files', 1]);
        $this->insertDefaultRow(['rtf', 'rtf', 'document', 'rtf', 'application/rtf', '^\x7B\x5C\x72\x74\x66\x31', 'Rich Text', 0]);
        $this->insertDefaultRow(['pdf', 'pdf', 'document', 'pdf', 'application/pdf', '^\x25PDF', 'PDF', 0]);
        $this->insertDefaultRow(['doc', 'doc', 'document', 'msword', 'application/msword', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^\xDB\xA5\x2D\x00|^PK\x03\x04', 'Microsoft Word', 0]);
        $this->insertDefaultRow(['docx', 'doc', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['ppt', 'ppt', 'document', 'powerpoint', 'application/ms-powerpoint', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^PK\x03\x04', 'PowerPoint', 0]);
        $this->insertDefaultRow(['pptx', 'ppt', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['xls', 'xls', 'document', 'msexcel', 'application/ms-excel', '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|^PK\x03\x04', 'Microsoft Excel', 0]);
        $this->insertDefaultRow(['xlsx', 'xls', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['txt', 'txt', 'document', 'txt', 'text/plain', '', 'Plaintext', 0]);
        $this->insertDefaultRow(['', null, 'archive', null, null, null, 'Archive files', 1]);
        $this->insertDefaultRow(['gz', 'gz', 'archive', 'gzip', 'application/gzip', '^\x1F\x8B\x08', 'GZip', 0]);
        $this->insertDefaultRow(['tgz', 'gz', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['gzip', 'gz', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['bz2', 'bz2', 'archive', 'bzip2', 'application/x-bzip2', '^BZh.{1}\x31\x41\x59\x26\x53\x59', 'bzip2', 0]);
        $this->insertDefaultRow(['tbz2', 'bz2', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['tbz', 'bz2', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['tar', 'tar', 'archive', 'tar', 'application/x-tar', '^.{257}ustar', 'TAR', 0]);
        $this->insertDefaultRow(['7z', '7z', 'archive', '7z', 'application/x-7z-compressed', '^\x37\x7A\xBC\xAF\x27\x1C', '7z', 0]);
        $this->insertDefaultRow(['hqx', 'hqx', 'archive', 'binhex', 'application/binhex', '^\(This file must be converted with BinHex', 'Binhex', 0]);
        $this->insertDefaultRow(['lzh', 'lzh', 'archive', 'lzh', 'application/x-lzh-compressed', '^.{2}\x2D\x6C\x68', 'LZH', 0]);
        $this->insertDefaultRow(['lha', 'lzh', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['zip', 'zip', 'archive', 'zip', 'application/zip', '^PK\x03\x04', 'Zip', 0]);
        $this->insertDefaultRow(['rar', 'rar', 'archive', 'rar', 'application/x-rar-compressed', '^Rar\x21\x1A\x07\x00', 'RAR', 0]);
        $this->insertDefaultRow(['sit', 'sit', 'archive', 'stuffit', 'application/x-stuffit', '^StuffIt \(c\)1997-|StuffIt\!|^SIT\!', 'StuffIt', 0]);
        $this->insertDefaultRow(['sitx', 'sit', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['iso', 'iso', 'archive', 'iso', 'application/x-iso-image', '^(.{32769}|.{34817}|.{36865})CD001', 'ISO Disk Image', 0]);
        $this->insertDefaultRow(['dmg', 'dmg', 'archive', 'dmg', 'application/x-apple-diskimage', 'koly.{508}$', 'Apple Disk Image', 0]);
        $this->insertDefaultRow(['', null, 'other', null, null, null, 'Other files', 1]);
        $this->insertDefaultRow(['swf', 'swf', 'other', 'swf', 'application/x-shockwave-flash', '^CWS|FWS|ZWS', 'Flash/Shockwave', 0]);
        $this->insertDefaultRow(['blorb', 'blorb', 'other', 'blorb', 'application/x-blorb', '^FORM.{4}IFRSRIdx', 'Blorb', 0]);
        $this->insertDefaultRow(['blb', 'blorb', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['gblorb', 'blorb', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['glb', 'blorb', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['zblorb', 'blorb', null, null, null, null, null, 0]);
        $this->insertDefaultRow(['zlb', 'blorb', null, null, null, null, null, 0]);
    }
}