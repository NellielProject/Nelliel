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

$filetypes['jpg']['supertype'] = 'graphics';
$filetypes['jpg']['subtype'] = 'jpeg';
$filetypes['jpg']['mime'] = 'image/jpeg';
$filetypes['jpg']['id_regex'] = '^\xFF\xD8\xFF';

$filetypes['jpeg'] = $filetypes['jpg'];

$filetypes['jpe'] = $filetypes['jpg'];

$filetypes['gif']['supertype'] = 'graphics';
$filetypes['gif']['subtype'] = 'gif';
$filetypes['gif']['mime'] = 'image/gif';
$filetypes['gif']['id_regex'] = '^(?:GIF87a|GIF89a)';

$filetypes['png']['supertype'] = 'graphics';
$filetypes['png']['subtype'] = 'png';
$filetypes['png']['mime'] = 'image/png';
$filetypes['png']['id_regex'] = '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A';

$filetypes['jp2']['supertype'] = 'graphics';
$filetypes['jp2']['subtype'] = 'jpeg2000';
$filetypes['jp2']['mime'] = 'image/jp2';
$filetypes['jp2']['id_regex'] = '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A';

$filetypes['tiff']['supertype'] = 'graphics';
$filetypes['tiff']['subtype'] = 'tiff';
$filetypes['tiff']['mime'] = 'image/tiff';
$filetypes['tiff']['id_regex'] = '^I\x20?I\x2A\x00|^MM\x00[\x2A-\x2B]';

$filetypes['tif'] = $filetypes['tiff'];

$filetypes['bmp']['supertype'] = 'graphics';
$filetypes['bmp']['subtype'] = 'bmp';
$filetypes['bmp']['mime'] = 'image/x-bmp';
$filetypes['bmp']['id_regex'] = '^BM';

$filetypes['ico']['supertype'] = 'graphics';
$filetypes['ico']['subtype'] = 'ico';
$filetypes['ico']['mime'] = 'image/x-icon';
$filetypes['ico']['id_regex'] = '^\x00\x00\x01\x00';

$filetypes['psd']['supertype'] = 'graphics';
$filetypes['psd']['subtype'] = 'psd';
$filetypes['psd']['mime'] = 'image/vnd.adobe.photoshop';
$filetypes['psd']['id_regex'] = '^8BPS\x00\x01';

$filetypes['tga']['supertype'] = 'graphics';
$filetypes['tga']['subtype'] = 'tga';
$filetypes['tga']['mime'] = 'image/x-targa';
$filetypes['tga']['id_regex'] = '^.{1}\x00';

$filetypes['pict']['supertype'] = 'graphics';
$filetypes['pict']['subtype'] = 'pict';
$filetypes['pict']['mime'] = 'image/x-pict';
$filetypes['pict']['id_regex'] = '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00)';

$filetypes['pct'] = $filetypes['pict'];

$filetypes['art']['supertype'] = 'graphics';
$filetypes['art']['subtype'] = 'aol-art';
$filetypes['art']['mime'] = 'image/x-jg';
$filetypes['art']['id_regex'] = '^JG[\x03-\x04]\x0E';

$filetypes['cel']['supertype'] = 'graphics';
$filetypes['cel']['subtype'] = 'kiss-cel';
$filetypes['cel']['mime'] = 'application/octet-stream';
$filetypes['cel']['id_regex'] = '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)';

$filetypes['kcf']['supertype'] = 'graphics';
$filetypes['kcf']['subtype'] = 'kiss-palette';
$filetypes['kcf']['mime'] = 'application/octet-stream';
$filetypes['kcf']['id_regex'] = '^KiSS\x10)';

$filetypes['wav']['supertype'] = 'audio';
$filetypes['wav']['subtype'] = 'wave';
$filetypes['wav']['mime'] = 'audio/x-wave';
$filetypes['wav']['id_regex'] = '^RIFF.{4}WAVEfmt';

$filetypes['aiff']['supertype'] = 'audio';
$filetypes['aiff']['subtype'] = 'aiff';
$filetypes['aiff']['mime'] = 'audio/aiff';
$filetypes['aiff']['id_regex'] = '^FORM.{4}AIFF';

$filetypes['aif'] = $filetypes['aiff'];

$filetypes['mp3']['supertype'] = 'audio';
$filetypes['mp3']['subtype'] = 'mp3';
$filetypes['mp3']['mime'] = 'audio/mpeg';
$filetypes['mp3']['id_regex'] = '^ID3|\xFF[\xE0-\xFF]{1}';

$filetypes['m4a']['supertype'] = 'audio';
$filetypes['m4a']['subtype'] = 'm4a';
$filetypes['m4a']['mime'] = 'audio/x-m4a';
$filetypes['m4a']['id_regex'] = '^.{4}ftypM4A';

$filetypes['flac']['supertype'] = 'audio';
$filetypes['flac']['subtype'] = 'flac';
$filetypes['flac']['mime'] = 'audio/x-flac';
$filetypes['flac']['id_regex'] = '^fLaC\x00\x00\x00\x22';

$filetypes['aac']['supertype'] = 'audio';
$filetypes['aac']['subtype'] = 'aac';
$filetypes['aac']['mime'] = 'audio/aac';
$filetypes['aac']['id_regex'] = '^ADIF|^\xFF(?:\xF1|\xF9)';

$filetypes['ogg']['supertype'] = 'audio';
$filetypes['ogg']['subtype'] = 'ogg';
$filetypes['ogg']['mime'] = 'audio/ogg';
$filetypes['ogg']['id_regex'] = '^OggS';

$filetypes['oga'] = $filetypes['ogg'];

$filetypes['au']['supertype'] = 'audio';
$filetypes['au']['subtype'] = 'au';
$filetypes['au']['mime'] = 'audio/basic';
$filetypes['au']['id_regex'] = '^\.snd';

$filetypes['ac3']['supertype'] = 'audio';
$filetypes['ac3']['subtype'] = 'ac3';
$filetypes['ac3']['mime'] = 'audio/ac3';
$filetypes['ac3']['id_regex'] = '^\x0B\x77';

$filetypes['wma']['supertype'] = 'audio';
$filetypes['wma']['subtype'] = 'wma';
$filetypes['wma']['mime'] = 'audio/x-ms-wma';
$filetypes['wma']['id_regex'] = '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C';

$filetypes['mid']['supertype'] = 'audio';
$filetypes['mid']['subtype'] = 'midi';
$filetypes['mid']['mime'] = 'audio/midi';
$filetypes['mid']['id_regex'] = '^MThd';

$filetypes['midi'] = $filetypes['mid'];

$filetypes['mpg']['supertype'] = 'video';
$filetypes['mpg']['subtype'] = 'mpeg';
$filetypes['mpg']['mime'] = 'video/mpeg';
$filetypes['mpg']['id_regex'] = '^\x00\x00\x01[\xB0-\xBF]';

$filetypes['mpeg'] = $filetypes['mpg'];

$filetypes['mpe'] = $filetypes['mpg'];

$filetypes['mov']['supertype'] = 'video';
$filetypes['mov']['subtype'] = 'mov';
$filetypes['mov']['mime'] = 'video/quicktime';
$filetypes['mov']['id_regex'] = '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)';

$filetypes['avi']['supertype'] = 'video';
$filetypes['avi']['subtype'] = 'avi';
$filetypes['avi']['mime'] = 'video/x-msvideo';
$filetypes['avi']['id_regex'] = '^RIFF.{4}AVI\x20LIST';

$filetypes['wmv']['supertype'] = 'video';
$filetypes['wmv']['subtype'] = 'wmv';
$filetypes['wmv']['mime'] = 'video/x-ms-wmv';
$filetypes['wmv']['id_regex'] = '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C';

$filetypes['mp4']['supertype'] = 'video';
$filetypes['mp4']['subtype'] = 'mp4';
$filetypes['mp4']['mime'] = 'video/mp4';
$filetypes['mp4']['id_regex'] = '^.{4}(?:ftypiso2|ftypisom)';

$filetypes['m4v']['supertype'] = 'video';
$filetypes['m4v']['subtype'] = 'm4v';
$filetypes['m4v']['mime'] = 'video/x-m4v';
$filetypes['m4v']['id_regex'] = '^.{4}ftypmp(?:41|42|71)';

$filetypes['mkv']['supertype'] = 'video';
$filetypes['mkv']['subtype'] = 'mkv';
$filetypes['mkv']['mime'] = 'video/x-matroska';
$filetypes['mkv']['id_regex'] = '^\x1A\x45\xDF\xA3';

$filetypes['flv']['supertype'] = 'video';
$filetypes['flv']['subtype'] = 'flv';
$filetypes['flv']['mime'] = 'video/x-flv';
$filetypes['flv']['id_regex'] = '^FLV\x01';

$filetypes['webm']['supertype'] = 'video';
$filetypes['webm']['subtype'] = 'webm';
$filetypes['webm']['mime'] = 'video/webm';
$filetypes['webm']['id_regex'] = '^\x1A\x45\xDF\xA3';

$filetypes['3gp']['supertype'] = 'video';
$filetypes['3gp']['subtype'] = '3gp';
$filetypes['3gp']['mime'] = 'video/3gpp';
$filetypes['3gp']['id_regex'] = '^.{4}ftyp3gp';

$filetypes['ogv']['supertype'] = 'video';
$filetypes['ogv']['subtype'] = 'ogv';
$filetypes['ogv']['mime'] = 'video/ogg';
$filetypes['ogv']['id_regex'] = '^OggS';

$filetypes['rtf']['supertype'] = 'document';
$filetypes['rtf']['subtype'] = 'rtf';
$filetypes['rtf']['mime'] = 'application/rtf';
$filetypes['rtf']['id_regex'] = '^\x7B\x5C\x72\x74\x66\x31';

$filetypes['pdf']['supertype'] = 'document';
$filetypes['pdf']['subtype'] = 'pdf';
$filetypes['pdf']['mime'] = 'application/pdf';
$filetypes['pdf']['id_regex'] = '^\x25PDF';

$filetypes['doc']['supertype'] = 'document';
$filetypes['doc']['subtype'] = 'doc';
$filetypes['doc']['mime'] = 'application/msword';
$filetypes['doc']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|\xDB\xA5\x2D\x00';

$filetypes['ppt']['supertype'] = 'document';
$filetypes['ppt']['subtype'] = 'ppt';
$filetypes['ppt']['mime'] = 'application/ms-powerpoint';
$filetypes['ppt']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1';

$filetypes['xls']['supertype'] = 'document';
$filetypes['xls']['subtype'] = 'xls';
$filetypes['xls']['mime'] = 'application/ms-excel';
$filetypes['xls']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1';

$filetypes['txt']['supertype'] = 'document';
$filetypes['txt']['subtype'] = 'text';
$filetypes['txt']['mime'] = 'text/plain';
$filetypes['txt']['id_regex'] = '';

$filetypes['swf']['supertype'] = 'other';
$filetypes['swf']['subtype'] = 'swf';
$filetypes['swf']['mime'] = 'application/x-shockwave-flash';
$filetypes['swf']['id_regex'] = '^CWS|FWS|ZWS';

$filetypes['blorb']['supertype'] = 'other';
$filetypes['blorb']['subtype'] = 'blorb';
$filetypes['blorb']['mime'] = 'application/x-blorb';
$filetypes['blorb']['id_regex'] = '^FORM.{4}IFRSRIdx';

$filetypes['gblorb'] = $filetypes['blorb'];

$filetypes['zblorb'] = $filetypes['blorb'];

$filetypes['glb'] = $filetypes['blorb'];

$filetypes['blb'] = $filetypes['blorb'];

$filetypes['zlb'] = $filetypes['blorb'];

$filetypes['gz']['supertype'] = 'archive';
$filetypes['gz']['subtype'] = 'gzip';
$filetypes['gz']['mime'] = 'application/gzip';
$filetypes['gz']['id_regex'] = '^\x1F\x8B\x08';

$filetypes['tgz'] = $filetypes['gz'];

$filetypes['gzip'] = $filetypes['gz'];

$filetypes['bz2']['supertype'] = 'archive';
$filetypes['bz2']['subtype'] = 'bzip';
$filetypes['bz2']['mime'] = 'application/gzip';
$filetypes['bz2']['id_regex'] = '^\x42\x5A\x68';

$filetypes['tbz2'] = $filetypes['bz2'];

$filetypes['tb2'] = $filetypes['bz2'];

$filetypes['tar']['supertype'] = 'archive';
$filetypes['tar']['subtype'] = 'tar';
$filetypes['tar']['mime'] = 'application/x-tar';
$filetypes['tar']['id_regex'] = '^.{257}ustar';

$filetypes['7z']['supertype'] = 'archive';
$filetypes['7z']['subtype'] = '7zip';
$filetypes['7z']['mime'] = 'application/x-7z-compressed';
$filetypes['7z']['id_regex'] = '^\x37\x7A\xBC\xAF\x27\x1C';

$filetypes['hqx']['supertype'] = 'archive';
$filetypes['hqx']['subtype'] = 'binhex';
$filetypes['hqx']['mime'] = 'application/binhex';
$filetypes['hqx']['id_regex'] = '^\(This file must be converted with BinHex\)';

$filetypes['lzh']['supertype'] = 'archive';
$filetypes['lzh']['subtype'] = 'lzh';
$filetypes['lzh']['mime'] = 'application/x-lzh-compressed';
$filetypes['lzh']['id_regex'] = '^.{2}\x2D\x6C\x68';

$filetypes['lha'] = $filetypes['lzh'];

$filetypes['zip']['supertype'] = 'archive';
$filetypes['zip']['subtype'] = 'zip';
$filetypes['zip']['mime'] = 'application/zip';
$filetypes['zip']['id_regex'] = '^PK\x03\x04';

$filetypes['rar']['supertype'] = 'archive';
$filetypes['rar']['subtype'] = 'rar';
$filetypes['rar']['mime'] = 'application/x-rar-compressed';
$filetypes['rar']['id_regex'] = '^Rar\x21\x1A\x07\x00';

$filetypes['sit']['supertype'] = 'archive';
$filetypes['sit']['subtype'] = 'stuffit';
$filetypes['sit']['mime'] = 'application/x-stuffit';
$filetypes['sit']['id_regex'] = '^StuffIt \(c\)1997-|^SIT\!';

$filetypes['iso']['supertype'] = 'archive';
$filetypes['iso']['subtype'] = 'iso';
$filetypes['iso']['mime'] = 'application/x-iso-image';
$filetypes['iso']['id_regex'] = '^(.{32769}|.{34817}|.{36865})CD001';

$filetypes['dmg']['supertype'] = 'archive';
$filetypes['dmg']['subtype'] = 'dmg';
$filetypes['dmg']['mime'] = 'application/x-apple-diskimage';
$filetypes['dmg']['id_regex'] = '^(?:x|BZ|PM)';
