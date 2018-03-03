<?php
if (!defined('NELLIEL_VERSION'))
{
    die("NOPE.AVI");
}

//
// File type data
// Array is keyed by the extension for the file type
// If more than one extension exists, a base entry for standard or most common extension
// Alternates can then be assigned using the base entry (e.g. $filetypes['jpeg'] = $filetypes['jpg'])
//

$filetypes = array();

$filetypes['jpg']['type'] = 'graphics';
$filetypes['jpg']['format'] = 'jpeg';
$filetypes['jpg']['mime'] = 'image/jpeg';
$filetypes['jpg']['id_regex'] = '^\xFF\xD8\xFF';

$filetypes['jpeg'] = $filetypes['jpg'];

$filetypes['jpe'] = $filetypes['jpg'];

$filetypes['gif']['type'] = 'graphics';
$filetypes['gif']['format'] = 'gif';
$filetypes['gif']['mime'] = 'image/gif';
$filetypes['gif']['id_regex'] = '^(?:GIF87a|GIF89a)';

$filetypes['png']['type'] = 'graphics';
$filetypes['png']['format'] = 'png';
$filetypes['png']['mime'] = 'image/png';
$filetypes['png']['id_regex'] = '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A';

$filetypes['jp2']['type'] = 'graphics';
$filetypes['jp2']['format'] = 'jpeg2000';
$filetypes['jp2']['mime'] = 'image/jp2';
$filetypes['jp2']['id_regex'] = '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A';

$filetypes['tiff']['type'] = 'graphics';
$filetypes['tiff']['format'] = 'tiff';
$filetypes['tiff']['mime'] = 'image/tiff';
$filetypes['tiff']['id_regex'] = '^I\x20?I\x2A\x00|^MM\x00[\x2A-\x2B]';

$filetypes['tif'] = $filetypes['tiff'];

$filetypes['bmp']['type'] = 'graphics';
$filetypes['bmp']['format'] = 'bmp';
$filetypes['bmp']['mime'] = 'image/x-bmp';
$filetypes['bmp']['id_regex'] = '^BM';

$filetypes['ico']['type'] = 'graphics';
$filetypes['ico']['format'] = 'ico';
$filetypes['ico']['mime'] = 'image/x-icon';
$filetypes['ico']['id_regex'] = '^\x00\x00\x01\x00';

$filetypes['psd']['type'] = 'graphics';
$filetypes['psd']['format'] = 'psd';
$filetypes['psd']['mime'] = 'image/vnd.adobe.photoshop';
$filetypes['psd']['id_regex'] = '^8BPS\x00\x01';

$filetypes['tga']['type'] = 'graphics';
$filetypes['tga']['format'] = 'tga';
$filetypes['tga']['mime'] = 'image/x-targa';
$filetypes['tga']['id_regex'] = '^.{1}\x00';

$filetypes['pict']['type'] = 'graphics';
$filetypes['pict']['format'] = 'pict';
$filetypes['pict']['mime'] = 'image/x-pict';
$filetypes['pict']['id_regex'] = '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00)';

$filetypes['pct'] = $filetypes['pict'];

$filetypes['art']['type'] = 'graphics';
$filetypes['art']['format'] = 'aol-art';
$filetypes['art']['mime'] = 'image/x-jg';
$filetypes['art']['id_regex'] = '^JG[\x03-\x04]\x0E';

$filetypes['cel']['type'] = 'graphics';
$filetypes['cel']['format'] = 'kiss-cel';
$filetypes['cel']['mime'] = 'application/octet-stream';
$filetypes['cel']['id_regex'] = '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)';

$filetypes['kcf']['type'] = 'graphics';
$filetypes['kcf']['format'] = 'kiss-palette';
$filetypes['kcf']['mime'] = 'application/octet-stream';
$filetypes['kcf']['id_regex'] = '^KiSS\x10)';

$filetypes['wav']['type'] = 'audio';
$filetypes['wav']['format'] = 'wave';
$filetypes['wav']['mime'] = 'audio/x-wave';
$filetypes['wav']['id_regex'] = '^RIFF.{4}WAVEfmt';

$filetypes['aiff']['type'] = 'audio';
$filetypes['aiff']['format'] = 'aiff';
$filetypes['aiff']['mime'] = 'audio/aiff';
$filetypes['aiff']['id_regex'] = '^FORM.{4}AIFF';

$filetypes['aif'] = $filetypes['aiff'];

$filetypes['mp3']['type'] = 'audio';
$filetypes['mp3']['format'] = 'mp3';
$filetypes['mp3']['mime'] = 'audio/mpeg';
$filetypes['mp3']['id_regex'] = '^ID3|\xFF[\xE0-\xFF]{1}';

$filetypes['m4a']['type'] = 'audio';
$filetypes['m4a']['format'] = 'm4a';
$filetypes['m4a']['mime'] = 'audio/x-m4a';
$filetypes['m4a']['id_regex'] = '^.{4}ftypM4A';

$filetypes['flac']['type'] = 'audio';
$filetypes['flac']['format'] = 'flac';
$filetypes['flac']['mime'] = 'audio/x-flac';
$filetypes['flac']['id_regex'] = '^fLaC\x00\x00\x00\x22';

$filetypes['aac']['type'] = 'audio';
$filetypes['aac']['format'] = 'aac';
$filetypes['aac']['mime'] = 'audio/aac';
$filetypes['aac']['id_regex'] = '^ADIF|^\xFF(?:\xF1|\xF9)';

$filetypes['ogg']['type'] = 'audio';
$filetypes['ogg']['format'] = 'ogg';
$filetypes['ogg']['mime'] = 'audio/ogg';
$filetypes['ogg']['id_regex'] = '^OggS';

$filetypes['oga'] = $filetypes['ogg'];

$filetypes['au']['type'] = 'audio';
$filetypes['au']['format'] = 'au';
$filetypes['au']['mime'] = 'audio/basic';
$filetypes['au']['id_regex'] = '^\.snd';

$filetypes['ac3']['type'] = 'audio';
$filetypes['ac3']['format'] = 'ac3';
$filetypes['ac3']['mime'] = 'audio/ac3';
$filetypes['ac3']['id_regex'] = '^\x0B\x77';

$filetypes['wma']['type'] = 'audio';
$filetypes['wma']['format'] = 'wma';
$filetypes['wma']['mime'] = 'audio/x-ms-wma';
$filetypes['wma']['id_regex'] = '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C';

$filetypes['mid']['type'] = 'audio';
$filetypes['mid']['format'] = 'midi';
$filetypes['mid']['mime'] = 'audio/midi';
$filetypes['mid']['id_regex'] = '^MThd';

$filetypes['midi'] = $filetypes['mid'];

$filetypes['mpg']['type'] = 'video';
$filetypes['mpg']['format'] = 'mpeg';
$filetypes['mpg']['mime'] = 'video/mpeg';
$filetypes['mpg']['id_regex'] = '^\x00\x00\x01[\xB0-\xBF]';

$filetypes['mpeg'] = $filetypes['mpg'];

$filetypes['mpe'] = $filetypes['mpg'];

$filetypes['mov']['type'] = 'video';
$filetypes['mov']['format'] = 'mov';
$filetypes['mov']['mime'] = 'video/quicktime';
$filetypes['mov']['id_regex'] = '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)';

$filetypes['avi']['type'] = 'video';
$filetypes['avi']['format'] = 'avi';
$filetypes['avi']['mime'] = 'video/x-msvideo';
$filetypes['avi']['id_regex'] = '^RIFF.{4}AVI\x20LIST';

$filetypes['wmv']['type'] = 'video';
$filetypes['wmv']['format'] = 'wmv';
$filetypes['wmv']['mime'] = 'video/x-ms-wmv';
$filetypes['wmv']['id_regex'] = '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C';

$filetypes['mp4']['type'] = 'video';
$filetypes['mp4']['format'] = 'mp4';
$filetypes['mp4']['mime'] = 'video/mp4';
$filetypes['mp4']['id_regex'] = '^.{4}(?:ftypiso2|ftypisom)';

$filetypes['m4v']['type'] = 'video';
$filetypes['m4v']['format'] = 'm4v';
$filetypes['m4v']['mime'] = 'video/x-m4v';
$filetypes['m4v']['id_regex'] = '^.{4}ftypmp(?:41|42|71)';

$filetypes['mkv']['type'] = 'video';
$filetypes['mkv']['format'] = 'mkv';
$filetypes['mkv']['mime'] = 'video/x-matroska';
$filetypes['mkv']['id_regex'] = '^\x1A\x45\xDF\xA3';

$filetypes['flv']['type'] = 'video';
$filetypes['flv']['format'] = 'flv';
$filetypes['flv']['mime'] = 'video/x-flv';
$filetypes['flv']['id_regex'] = '^FLV\x01';

$filetypes['webm']['type'] = 'video';
$filetypes['webm']['format'] = 'webm';
$filetypes['webm']['mime'] = 'video/webm';
$filetypes['webm']['id_regex'] = '^\x1A\x45\xDF\xA3';

$filetypes['3gp']['type'] = 'video';
$filetypes['3gp']['format'] = '3gp';
$filetypes['3gp']['mime'] = 'video/3gpp';
$filetypes['3gp']['id_regex'] = '^.{4}ftyp3gp';

$filetypes['ogv']['type'] = 'video';
$filetypes['ogv']['format'] = 'ogv';
$filetypes['ogv']['mime'] = 'video/ogg';
$filetypes['ogv']['id_regex'] = '^OggS';

$filetypes['rtf']['type'] = 'document';
$filetypes['rtf']['format'] = 'rtf';
$filetypes['rtf']['mime'] = 'application/rtf';
$filetypes['rtf']['id_regex'] = '^\x7B\x5C\x72\x74\x66\x31';

$filetypes['pdf']['type'] = 'document';
$filetypes['pdf']['format'] = 'pdf';
$filetypes['pdf']['mime'] = 'application/pdf';
$filetypes['pdf']['id_regex'] = '^\x25PDF';

$filetypes['doc']['type'] = 'document';
$filetypes['doc']['format'] = 'doc';
$filetypes['doc']['mime'] = 'application/msword';
$filetypes['doc']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|\xDB\xA5\x2D\x00';

$filetypes['ppt']['type'] = 'document';
$filetypes['ppt']['format'] = 'ppt';
$filetypes['ppt']['mime'] = 'application/ms-powerpoint';
$filetypes['ppt']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1';

$filetypes['xls']['type'] = 'document';
$filetypes['xls']['format'] = 'xls';
$filetypes['xls']['mime'] = 'application/ms-excel';
$filetypes['xls']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1';

$filetypes['txt']['type'] = 'document';
$filetypes['txt']['format'] = 'text';
$filetypes['txt']['mime'] = 'text/plain';
$filetypes['txt']['id_regex'] = '';

$filetypes['swf']['type'] = 'other';
$filetypes['swf']['format'] = 'swf';
$filetypes['swf']['mime'] = 'application/x-shockwave-flash';
$filetypes['swf']['id_regex'] = '^CWS|FWS|ZWS';

$filetypes['blorb']['type'] = 'other';
$filetypes['blorb']['format'] = 'blorb';
$filetypes['blorb']['mime'] = 'application/x-blorb';
$filetypes['blorb']['id_regex'] = '^FORM.{4}IFRSRIdx';

$filetypes['gblorb'] = $filetypes['blorb'];

$filetypes['zblorb'] = $filetypes['blorb'];

$filetypes['glb'] = $filetypes['blorb'];

$filetypes['blb'] = $filetypes['blorb'];

$filetypes['zlb'] = $filetypes['blorb'];

$filetypes['gz']['type'] = 'archive';
$filetypes['gz']['format'] = 'gzip';
$filetypes['gz']['mime'] = 'application/gzip';
$filetypes['gz']['id_regex'] = '^\x1F\x8B\x08';

$filetypes['tgz'] = $filetypes['gz'];

$filetypes['gzip'] = $filetypes['gz'];

$filetypes['bz2']['type'] = 'archive';
$filetypes['bz2']['format'] = 'bzip';
$filetypes['bz2']['mime'] = 'application/gzip';
$filetypes['bz2']['id_regex'] = '^\x42\x5A\x68';

$filetypes['tbz2'] = $filetypes['bz2'];

$filetypes['tb2'] = $filetypes['bz2'];

$filetypes['tar']['type'] = 'archive';
$filetypes['tar']['format'] = 'tar';
$filetypes['tar']['mime'] = 'application/x-tar';
$filetypes['tar']['id_regex'] = '^.{257}ustar';

$filetypes['7z']['type'] = 'archive';
$filetypes['7z']['format'] = '7zip';
$filetypes['7z']['mime'] = 'application/x-7z-compressed';
$filetypes['7z']['id_regex'] = '^\x37\x7A\xBC\xAF\x27\x1C';

$filetypes['hqx']['type'] = 'archive';
$filetypes['hqx']['format'] = 'binhex';
$filetypes['hqx']['mime'] = 'application/binhex';
$filetypes['hqx']['id_regex'] = '^\(This file must be converted with BinHex\)';

$filetypes['lzh']['type'] = 'archive';
$filetypes['lzh']['format'] = 'lzh';
$filetypes['lzh']['mime'] = 'application/x-lzh-compressed';
$filetypes['lzh']['id_regex'] = '^.{2}\x2D\x6C\x68';

$filetypes['lha'] = $filetypes['lzh'];

$filetypes['zip']['type'] = 'archive';
$filetypes['zip']['format'] = 'zip';
$filetypes['zip']['mime'] = 'application/zip';
$filetypes['zip']['id_regex'] = '^PK\x03\x04';

$filetypes['rar']['type'] = 'archive';
$filetypes['rar']['format'] = 'rar';
$filetypes['rar']['mime'] = 'application/x-rar-compressed';
$filetypes['rar']['id_regex'] = '^Rar\x21\x1A\x07\x00';

$filetypes['sit']['type'] = 'archive';
$filetypes['sit']['format'] = 'stuffit';
$filetypes['sit']['mime'] = 'application/x-stuffit';
$filetypes['sit']['id_regex'] = '^StuffIt \(c\)1997-|^SIT\!';

$filetypes['iso']['type'] = 'archive';
$filetypes['iso']['format'] = 'iso';
$filetypes['iso']['mime'] = 'application/x-iso-image';
$filetypes['iso']['id_regex'] = '^(.{32769}|.{34817}|.{36865})CD001';

$filetypes['dmg']['type'] = 'archive';
$filetypes['dmg']['format'] = 'dmg';
$filetypes['dmg']['mime'] = 'application/x-apple-diskimage';
$filetypes['dmg']['id_regex'] = '^(?:x|BZ|PM)';
