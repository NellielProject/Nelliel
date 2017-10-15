<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// Filetype data
//

$filetypes = array();

$filetypes['jpg'] = array(
    'supertype' => 'graphics',
    'subtype' => 'jpeg',
    'mime' => 'image/jpeg',
    'id_regex' => '^\xFF\xD8\xFF');

$filetypes['jpeg'] = $filetypes['jpg'];

$filetypes['jpe'] = $filetypes['jpg'];

$filetypes['gif'] = array(
    'supertype' => 'graphics',
    'subtype' => 'gif',
    'mime' => 'image/gif',
    'id_regex' => '^(GIF87a|GIF89a)');

$filetypes['png'] = array(
    'supertype' => 'graphics',
    'subtype' => 'png',
    'mime' => 'image/png',
    'id_regex' => '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A');

$filetypes['jp2'] = array(
    'supertype' => 'graphics',
    'subtype' => 'jpeg2000',
    'mime' => 'image/jp2',
    'id_regex' => '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A');

$filetypes['tiff'] = array(
    'supertype' => 'graphics',
    'subtype' => 'tiff',
    'mime' => 'image/tiff',
    'id_regex' => '^^(II\x2A\x00|MM\x00\x2A)');

$filetypes['tif'] = $filetypes['tiff'];

$filetypes['bmp'] = array(
    'supertype' => 'graphics',
    'subtype' => 'bmp',
    'mime' => 'image/x-bmp',
    'id_regex' => '^BM');

$filetypes['ico'] = array(
    'supertype' => 'graphics',
    'subtype' => 'ico',
    'mime' => 'image/x-icon',
    'id_regex' => '^\x00\x00\x01\x00');

$filetypes['psd'] = array(
    'supertype' => 'graphics',
    'subtype' => 'psd',
    'mime' => 'image/vnd.adobe.photoshop',
    'id_regex' => '^(8BPS\x00\x01)');

$filetypes['tga'] = array(
    'supertype' => 'graphics',
    'subtype' => 'tga',
    'mime' => 'image/x-targa',
    'id_regex' => '^.{1}\x00');

$filetypes['pict'] = array(
    'supertype' => 'graphics',
    'subtype' => 'pict',
    'mime' => 'image/tiff',
    'id_regex' => '^^(II\x2A\x00|MM\x00\x2A)');

$filetypes['pct'] = $filetypes['pict'];

$filetypes['wav'] = array(
    'supertype' => 'audio',
    'subtype' => 'wave',
    'mime' => 'audio/x-wave',
    'id_regex' => '^RIFF.{4}WAVE');

$filetypes['aiff'] = array(
    'supertype' => 'audio',
    'subtype' => 'aiff',
    'mime' => 'audio/aiff',
    'id_regex' => '^FORM.{4}AIFF');

$filetypes['aif'] = $filetypes['aiff'];

$filetypes['mp3'] = array(
    'supertype' => 'audio',
    'subtype' => 'mp3',
    'mime' => 'audio/mpeg',
    'id_regex' => '^\xFF[\xE2-\xE7\xF2-\xF7\xFA-\xFF][\x00-\x0B\x10-\x1B\x20-\x2B\x30-\x3B\x40-\x4B\x50-\x5B\x60-\x6B\x70-\x7B\x80-\x8B\x90-\x9B\xA0-\xAB\xB0-\xBB\xC0-\xCB\xD0-\xDB\xE0-\xEB\xF0-\xFB]');

$filetypes['m4a'] = array(
    'supertype' => 'audio',
    'subtype' => 'm4a',
    'mime' => 'audio/x-m4a',
    'id_regex' => '^\x00{3} ftypM4A');

$filetypes['flac'] = array(
    'supertype' => 'audio',
    'subtype' => 'flac',
    'mime' => 'audio/x-flac',
    'id_regex' => '^fLaC\x00\x00\x00\x22');

$filetypes['aac'] = array(
    'supertype' => 'audio',
    'subtype' => 'aac',
    'mime' => 'audio/aac',
    'id_regex' => '^(ADIF|\xFF[\xF0-\xF1\xF8-\xF9])');

$filetypes['ogg'] = array(
    'supertype' => 'audio',
    'subtype' => 'ogg',
    'mime' => 'audio/ogg',
    'id_regex' => '^OggS');

$filetypes['oga'] = $filetypes['ogg'];

$filetypes['au'] = array(
    'supertype' => 'audio',
    'subtype' => 'au',
    'mime' => 'audio/basic',
    'id_regex' => '^\.snd');

$filetypes['ac3'] = array(
    'supertype' => 'audio',
    'subtype' => 'ac3',
    'mime' => 'audio/ac3',
    'id_regex' => '^\x0B\x77');

$filetypes['wma'] = array(
    'supertype' => 'audio',
    'subtype' => 'wma',
    'mime' => 'audio/x-ms-wma',
    'id_regex' => '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C');

$filetypes['mid'] = array(
    'supertype' => 'audio',
    'subtype' => 'midi',
    'mime' => 'audio/midi',
    'id_regex' => '^MThd');

$filetypes['mpg'] = array(
    'supertype' => 'video',
    'subtype' => 'mpeg',
    'mime' => 'video/mpeg',
    'id_regex' => '^\x00\x00\x01(\xBA|\xB3)');

$filetypes['mpeg'] = $filetypes['mpg'];

$filetypes['mpe'] = $filetypes['mpg'];

$filetypes['mov'] = array(
    'supertype' => 'video',
    'subtype' => 'mov',
    'mime' => 'video/quicktime',
    'id_regex' => '^.{4}(cmov|free|ftyp|mdat|moov|pnot|skip|wide)');

$filetypes['avi'] = array(
    'supertype' => 'video',
    'subtype' => 'avi',
    'mime' => 'video/x-msvideo',
    'id_regex' => '^RIFF.{4}AVI');

$filetypes['wmv'] = array(
    'supertype' => 'video',
    'subtype' => 'wmv',
    'mime' => 'video/x-ms-wmv',
    'id_regex' => '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C');

$filetypes['mp4'] = array(
    'supertype' => 'video',
    'subtype' => 'mp4',
    'mime' => 'video/mp4',
    'id_regex' => '^(\x00{3}.ftyp3gp5|\x00{3}.ftypisom)');

$filetypes['m4v'] = array(
    'supertype' => 'video',
    'subtype' => 'm4v',
    'mime' => 'video/x-m4v',
    'id_regex' => '^\x00{3}.ftypmp42');

$filetypes['mkv'] = array(
    'supertype' => 'video',
    'subtype' => 'mkv',
    'mime' => 'video/x-matroska',
    'id_regex' => '^\x1A\x45\xDF\xA3');

$filetypes['flv'] = array(
    'supertype' => 'video',
    'subtype' => 'flv',
    'mime' => 'video/x-flv',
    'id_regex' => '^FLV\x01');

$filetypes['rtf'] = array(
    'supertype' => 'document',
    'subtype' => 'rtf',
    'mime' => 'application/rtf',
    'id_regex' => '^\x7B\x5C\x72\x74\x66');

$filetypes['pdf'] = array(
    'supertype' => 'document',
    'subtype' => 'pdf',
    'mime' => 'application/pdf',
    'id_regex' => '^%PDF-1');

$filetypes['doc'] = array(
    'supertype' => 'document',
    'subtype' => 'doc',
    'mime' => 'application/msword',
    'id_regex' => '^(\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1\x00\x00\x00\x00|\xDB\xA5\x2D\x00)');

$filetypes['ppt'] = array(
    'supertype' => 'document',
    'subtype' => 'ppt',
    'mime' => 'application/vnd.ms-powerpoint',
    'id_regex' => '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1\x00\x00\x00\x00');

$filetypes['XLS'] = array(
    'supertype' => 'document',
    'subtype' => 'xls',
    'mime' => 'application/vnd.ms-excel',
    'id_regex' => '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1\x00\x00\x00\x00');

$filetypes['txt'] = array(
    'supertype' => 'document',
    'subtype' => 'text',
    'mime' => 'text/plain',
    'id_regex' => '');

$filetypes['swf'] = array(
    'supertype' => 'OTHER',
    'subtype' => 'swf',
    'mime' => 'application/x-shockwave-flash',
    'id_regex' => '^(FWS|CWS)');

$filetypes['blorb'] = array(
'supertype' => 'OTHER',
'subtype' => 'blorb',
'mime' => 'application/x-blorb',
'id_regex' => '^FORM.{4}IFRSRIdx');

$filetypes['gblorb'] = $filetypes['blorb'];

$filetypes['zblorb'] = $filetypes['blorb'];

$filetypes['glb'] = $filetypes['blorb'];

$filetypes['blb'] = $filetypes['blorb'];

$filetypes['zlb'] = $filetypes['blorb'];

$filetypes['gz'] = array(
    'supertype' => 'archive',
    'subtype' => 'gzip',
    'mime' => 'application/gzip',
    'id_regex' => '^\x1F\x8B\x08');

$filetypes['tgz'] = $filetypes['gz'];

$filetypes['gzip'] = $filetypes['gz'];

$filetypes['bz2'] = array(
    'supertype' => 'archive',
    'subtype' => 'bzip',
    'mime' => 'application/x-bzip2',
    'id_regex' => '\x42\x5A\x68');

$filetypes['tbz2'] = $filetypes['bz2'];

$filetypes['tb2'] = $filetypes['bz2'];

$filetypes['tar'] = array(
    'supertype' => 'archive',
    'subtype' => 'tar',
    'mime' => 'application/x-tar',
    'id_regex' => '^.{257}ustar)');

$filetypes['7z'] = array(
    'supertype' => 'archive',
    'subtype' => '7zip',
    'mime' => 'application/x-7z-compressed',
    'id_regex' => '^\x37\x7A\xBC\xAF\x27\x1C');

$filetypes['hqx'] = array(
    'supertype' => 'archive',
    'subtype' => 'binhex',
    'mime' => 'aapplication/binhex',
    'id_regex' => '^\(This file must be converted with BinHex');

$filetypes['lzh'] = array(
    'supertype' => 'archive',
    'subtype' => 'lzh',
    'mime' => 'application/x-lzh-compressed',
    'id_regex' => '^.{2}\-lh');

$filetypes['lha'] = $filetypes['lzh'];

$filetypes['zip'] = array(
    'supertype' => 'archive',
    'subtype' => 'zip',
    'mime' => 'application/zip',
    'id_regex' => '^(PK\x03\x04|PK\x05\x06|PK\x07\x08|.{29152}WinZip)');

$filetypes['rar'] = array(
    'supertype' => 'archive',
    'subtype' => 'rar',
    'mime' => 'application/x-rar-compressed',
    'id_regex' => '^Rar!\x1A\x07\x00');

$filetypes['sit'] = array(
    'supertype' => 'archive',
    'subtype' => 'stuffit',
    'mime' => 'application/x-stuffit',
    'id_regex' => '^(StuffIt \(c\)1997-|SIT\!)');

$filetypes['iso'] = array(
    'supertype' => 'archive',
    'subtype' => 'iso',
    'mime' => 'application/x-iso-image',
    'id_regex' => '^(.{32769}CD001|.{34817}CD001#|.{36865}CD001)');

$filetypes['dmg'] = array(
    'supertype' => 'archive',
    'subtype' => 'dmg',
    'mime' => 'application/x-apple-diskimage',
    'id_regex' => '^x');
