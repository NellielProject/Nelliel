<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Filetype data
//


$filetypes['jpg'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'JPEG',
    'mime' => 'image/jpeg',
    'id_regex' => '^\xFF\xD8\xFF');

$filetypes['jpeg'] = $filetypes['jpg'];

$filetypes['jpe'] = $filetypes['jpg'];

$filetypes['gif'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'GIF',
    'mime' => 'image/gif',
    'id_regex' => '^(GIF87a|GIF89a)');

$filetypes['png'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'PNG',
    'mime' => 'image/png',
    'id_regex' => '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A');

$filetypes['jp2'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'JPEG2000',
    'mime' => 'image/jp2',
    'id_regex' => '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A');

$filetypes['tiff'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'TIFF',
    'mime' => 'image/tiff',
    'id_regex' => '^^(II\x2A\x00|MM\x00\x2A)');

$filetypes['tif'] = $filetypes['tiff'];

$filetypes['bmp'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'BMP',
    'mime' => 'image/x-bmp',
    'id_regex' => '^BM');

$filetypes['ico'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'ICO',
    'mime' => 'image/x-icon',
    'id_regex' => '^\x00\x00\x01\x00');

$filetypes['psd'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'PSD',
    'mime' => 'image/vnd.adobe.photoshop',
    'id_regex' => '^(8BPS\x00\x01)');

$filetypes['tga'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'TGA',
    'mime' => 'image/x-targa',
    'id_regex' => '^.{1}\x00');

$filetypes['pict'] = array(
    'supertype' => 'GRAPHICS',
    'subtype' => 'PICT',
    'mime' => 'image/tiff',
    'id_regex' => '^^(II\x2A\x00|MM\x00\x2A)');

$filetypes['pict'] = $filetypes['pct'];

$filetypes['wav'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'WAVE',
    'mime' => 'audio/x-wave',
    'id_regex' => '^RIFF.{4}WAVE');

$filetypes['aiff'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'AIFF',
    'mime' => 'audio/aiff',
    'id_regex' => '^FORM.{4}AIFF');

$filetypes['aif'] = $filetypes['aiff'];

$filetypes['mp3'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'MP3',
    'mime' => 'audio/mpeg',
    'id_regex' => '^\xFF[\xE2-\xE7\xF2-\xF7\xFA-\xFF][\x00-\x0B\x10-\x1B\x20-\x2B\x30-\x3B\x40-\x4B\x50-\x5B\x60-\x6B\x70-\x7B\x80-\x8B\x90-\x9B\xA0-\xAB\xB0-\xBB\xC0-\xCB\xD0-\xDB\xE0-\xEB\xF0-\xFB]');

$filetypes['m4a'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'M4A',
    'mime' => 'audio/x-m4a',
    'id_regex' => '^\x00{3} ftypM4A');

$filetypes['flac'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'FLAC',
    'mime' => 'audio/x-flac',
    'id_regex' => '^fLaC\x00\x00\x00\x22');

$filetypes['aac'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'AAC',
    'mime' => 'audio/aac',
    'id_regex' => '^(ADIF|\xFF[\xF0-\xF1\xF8-\xF9])');

$filetypes['ogg'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'OGG',
    'mime' => 'audio/ogg',
    'id_regex' => '^OggS');

$filetypes['oga'] = $filetypes['ogg'];

$filetypes['au'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'AU',
    'mime' => 'audio/basic',
    'id_regex' => '^\.snd');

$filetypes['ac3'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'AC3',
    'mime' => 'audio/ac3',
    'id_regex' => '^\x0B\x77');

$filetypes['wma'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'WMA',
    'mime' => 'audio/x-ms-wma',
    'id_regex' => '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C');

$filetypes['mid'] = array(
    'supertype' => 'AUDIO',
    'subtype' => 'MIDI',
    'mime' => 'audio/midi',
    'id_regex' => '^MThd');

$filetypes['mpg'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'MPEG',
    'mime' => 'video/mpeg',
    'id_regex' => '^\x00\x00\x01(\xBA|\xB3)');

$filetypes['mpeg'] = $filetypes['mpg'];

$filetypes['mpe'] = $filetypes['mpg'];

$filetypes['mov'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'MOV',
    'mime' => 'video/quicktime',
    'id_regex' => '^.{4}(cmov|free|ftyp|mdat|moov|pnot|skip|wide)');

$filetypes['avi'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'AVI',
    'mime' => 'video/x-msvideo',
    'id_regex' => '^RIFF.{4}AVI');

$filetypes['wmv'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'WMV',
    'mime' => 'video/x-ms-wmv',
    'id_regex' => '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C');

$filetypes['mp4'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'MP4',
    'mime' => 'video/mp4',
    'id_regex' => '^(\x00{3}.ftyp3gp5|\x00{3}.ftypisom)');

$filetypes['m4v'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'M4V',
    'mime' => 'video/x-m4v',
    'id_regex' => '^\x00{3}.ftypmp42');

$filetypes['mkv'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'MKV',
    'mime' => 'video/x-matroska',
    'id_regex' => '^\x1A\x45\xDF\xA3');

$filetypes['flv'] = array(
    'supertype' => 'VIDEO',
    'subtype' => 'FLV',
    'mime' => 'video/x-flv',
    'id_regex' => '^FLV\x01');

$filetypes['rtf'] = array(
    'supertype' => 'DOCUMENT',
    'subtype' => 'RTF',
    'mime' => 'application/rtf',
    'id_regex' => '^\x7B\x5C\x72\x74\x66');

$filetypes['pdf'] = array(
    'supertype' => 'DOCUMENT',
    'subtype' => 'PDF',
    'mime' => 'application/pdf',
    'id_regex' => '^%PDF-1');

$filetypes['doc'] = array(
    'supertype' => 'DOCUMENT',
    'subtype' => 'DOC',
    'mime' => 'application/msword',
    'id_regex' => '^(\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1\x00\x00\x00\x00|\xDB\xA5\x2D\x00)');

$filetypes['ppt'] = array(
    'supertype' => 'DOCUMENT',
    'subtype' => 'PPT',
    'mime' => 'application/vnd.ms-powerpoint',
    'id_regex' => '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1\x00\x00\x00\x00');

$filetypes['XLS'] = array(
    'supertype' => 'DOCUMENT',
    'subtype' => 'XLS',
    'mime' => 'application/vnd.ms-excel',
    'id_regex' => '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1\x00\x00\x00\x00');

$filetypes['txt'] = array(
    'supertype' => 'DOCUMENT',
    'subtype' => 'TEXT',
    'mime' => 'text/plain',
    'id_regex' => '');

$filetypes['swf'] = array(
    'supertype' => 'OTHER',
    'subtype' => 'SWF',
    'mime' => 'application/x-shockwave-flash',
    'id_regex' => '^(FWS|CWS)');

$filetypes['gblorb'] = $filetypes['blorb'];

$filetypes['zblorb'] = $filetypes['blorb'];

$filetypes['glb'] = $filetypes['blorb'];

$filetypes['blb'] = $filetypes['blorb'];

$filetypes['zlb'] = $filetypes['blorb'];

$filetypes['blorb'] = array(
    'supertype' => 'OTHER',
    'subtype' => 'BLORB',
    'mime' => 'application/x-blorb',
    'id_regex' => '^FORM.{4}IFRSRIdx');

$filetypes['gz'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'GZIP',
    'mime' => 'application/gzip',
    'id_regex' => '^\x1F\x8B\x08');

$filetypes['tgz'] = $filetypes['gz'];

$filetypes['gzip'] = $filetypes['gz'];

$filetypes['bz2'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'BZIP',
    'mime' => 'application/x-bzip2',
    'id_regex' => '\x42\x5A\x68');

$filetypes['tbz2'] = $filetypes['bz2'];

$filetypes['tb2'] = $filetypes['bz2'];

$filetypes['tar'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'TAR',
    'mime' => 'application/x-tar',
    'id_regex' => '^.{257}ustar)');

$filetypes['7z'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => '7ZIP',
    'mime' => 'application/x-7z-compressed',
    'id_regex' => '^\x37\x7A\xBC\xAF\x27\x1C');

$filetypes['hqx'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'BINHEX',
    'mime' => 'aapplication/binhex',
    'id_regex' => '^\(This file must be converted with BinHex');

$filetypes['lzh'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'LZH',
    'mime' => 'application/x-lzh-compressed',
    'id_regex' => '^.{2}\-lh');

$filetypes['lha'] = $filetypes['lzh'];

$filetypes['zip'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'ZIP',
    'mime' => 'application/zip',
    'id_regex' => '^(PK\x03\x04|PK\x05\x06|PK\x07\x08|.{29152}WinZip)');

$filetypes['rar'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'RAR',
    'mime' => 'application/x-rar-compressed',
    'id_regex' => '^Rar!\x1A\x07\x00');

$filetypes['sit'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'STUFFIT',
    'mime' => 'application/x-stuffit',
    'id_regex' => '^(StuffIt \(c\)1997-|SIT\!)');

$filetypes['iso'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'ISO',
    'mime' => 'application/x-iso-image',
    'id_regex' => '^(.{32769}CD001|.{34817}CD001#|.{36865}CD001)');

$filetypes['dmg'] = array(
    'supertype' => 'ARCHIVE',
    'subtype' => 'DMG',
    'mime' => 'application/x-apple-diskimage',
    'id_regex' => '^x');
