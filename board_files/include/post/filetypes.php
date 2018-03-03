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
$filetypes['jpg']['label'] = 'JPEG - .jpg, .jpeg, .jpe';

$filetypes['jpeg'] = $filetypes['jpg'];

$filetypes['jpe'] = $filetypes['jpg'];

$filetypes['gif']['type'] = 'graphics';
$filetypes['gif']['format'] = 'gif';
$filetypes['gif']['mime'] = 'image/gif';
$filetypes['gif']['id_regex'] = '^(?:GIF87a|GIF89a)';
$filetypes['gif']['label'] = 'GIF - .gif';

$filetypes['png']['type'] = 'graphics';
$filetypes['png']['format'] = 'png';
$filetypes['png']['mime'] = 'image/png';
$filetypes['png']['id_regex'] = '^\x89\x50\x4E\x47\x0D\x0A\x1A\x0A';
$filetypes['png']['label'] = 'PNG - .png';

$filetypes['jp2']['type'] = 'graphics';
$filetypes['jp2']['format'] = 'jpeg2000';
$filetypes['jp2']['mime'] = 'image/jp2';
$filetypes['jp2']['id_regex'] = '^\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A';
$filetypes['jp2']['label'] = 'JPEG2000 - .jp2, .j2k';

$filetypes['j2k'] = $filetypes['jp2'];

$filetypes['tiff']['type'] = 'graphics';
$filetypes['tiff']['format'] = 'tiff';
$filetypes['tiff']['mime'] = 'image/tiff';
$filetypes['tiff']['id_regex'] = '^I\x20?I\x2A\x00|^MM\x00[\x2A-\x2B]';
$filetypes['tiff']['label'] = 'TIFF - .tiff, .tif';

$filetypes['tif'] = $filetypes['tiff'];

$filetypes['bmp']['type'] = 'graphics';
$filetypes['bmp']['format'] = 'bmp';
$filetypes['bmp']['mime'] = 'image/x-bmp';
$filetypes['bmp']['id_regex'] = '^BM';
$filetypes['bmp']['label'] = 'BMP - .bmp';

$filetypes['ico']['type'] = 'graphics';
$filetypes['ico']['format'] = 'ico';
$filetypes['ico']['mime'] = 'image/x-icon';
$filetypes['ico']['id_regex'] = '^\x00\x00\x01\x00';
$filetypes['ico']['label'] = 'ICO - .ico';

$filetypes['psd']['type'] = 'graphics';
$filetypes['psd']['format'] = 'psd';
$filetypes['psd']['mime'] = 'image/vnd.adobe.photoshop';
$filetypes['psd']['id_regex'] = '^8BPS\x00\x01';
$filetypes['psd']['label'] = 'PSD (Photoshop) - .psd';

$filetypes['tga']['type'] = 'graphics';
$filetypes['tga']['format'] = 'tga';
$filetypes['tga']['mime'] = 'image/x-targa';
$filetypes['tga']['id_regex'] = '^.{1}\x00';
$filetypes['tga']['label'] = 'Truevision TGA - .tga';

$filetypes['pict']['type'] = 'graphics';
$filetypes['pict']['format'] = 'pict';
$filetypes['pict']['mime'] = 'image/x-pict';
$filetypes['pict']['id_regex'] = '^.{522}(?:\x11\x01|\x00\x11\x02\xFF\x0C\x00)';
$filetypes['pict']['label'] = 'PICT - .pict, .pct';

$filetypes['pct'] = $filetypes['pict'];

$filetypes['art']['type'] = 'graphics';
$filetypes['art']['format'] = 'art';
$filetypes['art']['mime'] = 'image/x-jg';
$filetypes['art']['id_regex'] = '^JG[\x03-\x04]\x0E';
$filetypes['art']['label'] = 'AOL ART - .art';

$filetypes['cel']['type'] = 'graphics';
$filetypes['cel']['format'] = 'cel';
$filetypes['cel']['mime'] = 'application/octet-stream';
$filetypes['cel']['id_regex'] = '^KiSS(?:\x20\x04|\x20\x08|\x21\x20|\x20\x20)';
$filetypes['cel']['label'] = 'Kisekae CEL - .cel';

$filetypes['kcf']['type'] = 'graphics';
$filetypes['kcf']['format'] = 'kcf';
$filetypes['kcf']['mime'] = 'application/octet-stream';
$filetypes['kcf']['id_regex'] = '^KiSS\x10)';
$filetypes['kcf']['label'] = 'Kisekae Palette - .kcf';

$filetypes['wav']['type'] = 'audio';
$filetypes['wav']['format'] = 'wave';
$filetypes['wav']['mime'] = 'audio/x-wave';
$filetypes['wav']['id_regex'] = '^RIFF.{4}WAVEfmt';
$filetypes['wav']['label'] = 'WAVE - .wav';

$filetypes['aiff']['type'] = 'audio';
$filetypes['aiff']['format'] = 'aiff';
$filetypes['aiff']['mime'] = 'audio/aiff';
$filetypes['aiff']['id_regex'] = '^FORM.{4}AIFF';
$filetypes['aiff']['label'] = 'AIFF - .aiff, .aif';

$filetypes['aif'] = $filetypes['aiff'];

$filetypes['mp3']['type'] = 'audio';
$filetypes['mp3']['format'] = 'mp3';
$filetypes['mp3']['mime'] = 'audio/mpeg';
$filetypes['mp3']['id_regex'] = '^ID3|\xFF[\xE0-\xFF]{1}';
$filetypes['mp3']['label'] = 'MP3 - .mp3';

$filetypes['m4a']['type'] = 'audio';
$filetypes['m4a']['format'] = 'm4a';
$filetypes['m4a']['mime'] = 'audio/x-m4a';
$filetypes['m4a']['id_regex'] = '^.{4}ftypM4A';
$filetypes['m4a']['label'] = 'MPEG-4 Audio - .m4a';

$filetypes['flac']['type'] = 'audio';
$filetypes['flac']['format'] = 'flac';
$filetypes['flac']['mime'] = 'audio/x-flac';
$filetypes['flac']['id_regex'] = '^fLaC\x00\x00\x00\x22';
$filetypes['flac']['label'] = 'FLAC - .flac';

$filetypes['aac']['type'] = 'audio';
$filetypes['aac']['format'] = 'aac';
$filetypes['aac']['mime'] = 'audio/aac';
$filetypes['aac']['id_regex'] = '^ADIF|^\xFF(?:\xF1|\xF9)';
$filetypes['aac']['label'] = 'AAC - .aac';

$filetypes['ogg']['type'] = 'audio';
$filetypes['ogg']['format'] = 'ogg';
$filetypes['ogg']['mime'] = 'audio/ogg';
$filetypes['ogg']['id_regex'] = '^OggS';
$filetypes['ogg']['label'] = 'OGG Audio - .ogg, .oga';

$filetypes['oga'] = $filetypes['ogg'];

$filetypes['au']['type'] = 'audio';
$filetypes['au']['format'] = 'au';
$filetypes['au']['mime'] = 'audio/basic';
$filetypes['au']['id_regex'] = '^\.snd';
$filetypes['au']['label'] = 'AU - .au, .snd';

$filetypes['snd'] = $filetypes['au'];

$filetypes['ac3']['type'] = 'audio';
$filetypes['ac3']['format'] = 'ac3';
$filetypes['ac3']['mime'] = 'audio/ac3';
$filetypes['ac3']['id_regex'] = '^\x0B\x77';
$filetypes['ac3']['label'] = 'AC3 - .ac3';

$filetypes['wma']['type'] = 'audio';
$filetypes['wma']['format'] = 'wma';
$filetypes['wma']['mime'] = 'audio/x-ms-wma';
$filetypes['wma']['id_regex'] = '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C';
$filetypes['wma']['label'] = 'Windows Media Audio - .wma';

$filetypes['mid']['type'] = 'audio';
$filetypes['mid']['format'] = 'midi';
$filetypes['mid']['mime'] = 'audio/midi';
$filetypes['mid']['id_regex'] = '^MThd';
$filetypes['mid']['label'] = 'MIDI - .mid, .midi';

$filetypes['midi'] = $filetypes['mid'];

$filetypes['mpg']['type'] = 'video';
$filetypes['mpg']['format'] = 'mpeg';
$filetypes['mpg']['mime'] = 'video/mpeg';
$filetypes['mpg']['id_regex'] = '^\x00\x00\x01[\xB0-\xBF]';
$filetypes['mpg']['label'] = 'MPEG-1/MPEG-2 - .mpg, .mpeg, .mpe';

$filetypes['mpeg'] = $filetypes['mpg'];

$filetypes['mpe'] = $filetypes['mpg'];

$filetypes['mov']['type'] = 'video';
$filetypes['mov']['format'] = 'mov';
$filetypes['mov']['mime'] = 'video/quicktime';
$filetypes['mov']['id_regex'] = '^.{4}(?:cmov|free|ftypqt|mdat|moov|pnot|skip|wide)';
$filetypes['mov']['label'] = 'Quicktime Movie - .mov';

$filetypes['avi']['type'] = 'video';
$filetypes['avi']['format'] = 'avi';
$filetypes['avi']['mime'] = 'video/x-msvideo';
$filetypes['avi']['id_regex'] = '^RIFF.{4}AVI\x20LIST';
$filetypes['avi']['label'] = 'AVI - .avi';

$filetypes['wmv']['type'] = 'video';
$filetypes['wmv']['format'] = 'wmv';
$filetypes['wmv']['mime'] = 'video/x-ms-wmv';
$filetypes['wmv']['id_regex'] = '^\x30\x26\xB2\x75\x8E\x66\xCF\x11\xA6\xD9\x00\xAA\x00\x62\xCE\x6C';
$filetypes['wmv']['label'] = 'Windows Media Video - .wmv';

$filetypes['mp4']['type'] = 'video';
$filetypes['mp4']['format'] = 'mp4';
$filetypes['mp4']['mime'] = 'video/mp4';
$filetypes['mp4']['id_regex'] = '^.{4}(?:ftypiso2|ftypisom)';
$filetypes['mp4']['label'] = 'MPEG-4 Media - .mp4';

$filetypes['m4v']['type'] = 'video';
$filetypes['m4v']['format'] = 'm4v';
$filetypes['m4v']['mime'] = 'video/x-m4v';
$filetypes['m4v']['id_regex'] = '^.{4}ftypmp(?:41|42|71)';
$filetypes['m4v']['label'] = 'MPEG-4 Video - .m4v';

$filetypes['mkv']['type'] = 'video';
$filetypes['mkv']['format'] = 'mkv';
$filetypes['mkv']['mime'] = 'video/x-matroska';
$filetypes['mkv']['id_regex'] = '^\x1A\x45\xDF\xA3';
$filetypes['mkv']['label'] = 'MKV - .mkv';

$filetypes['flv']['type'] = 'video';
$filetypes['flv']['format'] = 'flv';
$filetypes['flv']['mime'] = 'video/x-flv';
$filetypes['flv']['id_regex'] = '^FLV\x01';
$filetypes['flv']['label'] = 'Flash Video - .flv';

$filetypes['webm']['type'] = 'video';
$filetypes['webm']['format'] = 'webm';
$filetypes['webm']['mime'] = 'video/webm';
$filetypes['webm']['id_regex'] = '^\x1A\x45\xDF\xA3';
$filetypes['webm']['label'] = 'WebM - .webm';

$filetypes['3gp']['type'] = 'video';
$filetypes['3gp']['format'] = '3gp';
$filetypes['3gp']['mime'] = 'video/3gpp';
$filetypes['3gp']['id_regex'] = '^.{4}ftyp3gp';
$filetypes['3gp']['label'] = '3GP - .3gp';

$filetypes['ogv']['type'] = 'video';
$filetypes['ogv']['format'] = 'ogv';
$filetypes['ogv']['mime'] = 'video/ogg';
$filetypes['ogv']['id_regex'] = '^OggS';
$filetypes['ogv']['label'] = 'Ogg Video - .ogv';

$filetypes['rtf']['type'] = 'document';
$filetypes['rtf']['format'] = 'rtf';
$filetypes['rtf']['mime'] = 'application/rtf';
$filetypes['rtf']['id_regex'] = '^\x7B\x5C\x72\x74\x66\x31';
$filetypes['rtf']['label'] = 'Rich Text - .rtf';

$filetypes['pdf']['type'] = 'document';
$filetypes['pdf']['format'] = 'pdf';
$filetypes['pdf']['mime'] = 'application/pdf';
$filetypes['pdf']['id_regex'] = '^\x25PDF';
$filetypes['pdf']['label'] = 'PDF - .pdf';

$filetypes['doc']['type'] = 'document';
$filetypes['doc']['format'] = 'doc';
$filetypes['doc']['mime'] = 'application/msword';
$filetypes['doc']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1|\xDB\xA5\x2D\x00';
$filetypes['doc']['label'] = 'MS Word - .doc';

$filetypes['ppt']['type'] = 'document';
$filetypes['ppt']['format'] = 'ppt';
$filetypes['ppt']['mime'] = 'application/ms-powerpoint';
$filetypes['ppt']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1';
$filetypes['ppt']['label'] = 'PowerPoint - .ppt';

$filetypes['xls']['type'] = 'document';
$filetypes['xls']['format'] = 'xls';
$filetypes['xls']['mime'] = 'application/ms-excel';
$filetypes['xls']['id_regex'] = '^\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1';
$filetypes['xls']['label'] = 'Excel - .xls';

$filetypes['txt']['type'] = 'document';
$filetypes['txt']['format'] = 'text';
$filetypes['txt']['mime'] = 'text/plain';
$filetypes['txt']['id_regex'] = '';
$filetypes['txt']['label'] = 'Plain Text - .txt';

$filetypes['swf']['type'] = 'other';
$filetypes['swf']['format'] = 'swf';
$filetypes['swf']['mime'] = 'application/x-shockwave-flash';
$filetypes['swf']['id_regex'] = '^CWS|FWS|ZWS';
$filetypes['swf']['label'] = 'Flash/Shockwave - .swf';

$filetypes['blorb']['type'] = 'other';
$filetypes['blorb']['format'] = 'blorb';
$filetypes['blorb']['mime'] = 'application/x-blorb';
$filetypes['blorb']['id_regex'] = '^FORM.{4}IFRSRIdx';
$filetypes['blorb']['label'] = 'Blorb - .blorb, .blb, .gblorb, .glb, .zblorb, .zlb';

$filetypes['blb'] = $filetypes['blorb'];

$filetypes['gblorb'] = $filetypes['blorb'];

$filetypes['glb'] = $filetypes['blorb'];

$filetypes['zblorb'] = $filetypes['blorb'];

$filetypes['zlb'] = $filetypes['blorb'];

$filetypes['gz']['type'] = 'archive';
$filetypes['gz']['format'] = 'gzip';
$filetypes['gz']['mime'] = 'application/gzip';
$filetypes['gz']['id_regex'] = '^\x1F\x8B\x08';
$filetypes['gz']['label'] = 'GZip - .gz, .gzip, .tgz';

$filetypes['tgz'] = $filetypes['gz'];

$filetypes['gzip'] = $filetypes['gz'];

$filetypes['bz2']['type'] = 'archive';
$filetypes['bz2']['format'] = 'bzip2';
$filetypes['bz2']['mime'] = 'application/x-bzip2';
$filetypes['bz2']['id_regex'] = '^BZh.{1}\x31\x41\x59\x26\x53\x59';
$filetypes['bz2']['label'] = 'bzip2 - .bz2, .tbz2, .tb2';

$filetypes['tbz2'] = $filetypes['bz2'];

$filetypes['tb2'] = $filetypes['bz2'];

$filetypes['tar']['type'] = 'archive';
$filetypes['tar']['format'] = 'tar';
$filetypes['tar']['mime'] = 'application/x-tar';
$filetypes['tar']['id_regex'] = '^.{257}ustar';
$filetypes['tar']['label'] = 'TAR - .tar';

$filetypes['7z']['type'] = 'archive';
$filetypes['7z']['format'] = '7z';
$filetypes['7z']['mime'] = 'application/x-7z-compressed';
$filetypes['7z']['id_regex'] = '^\x37\x7A\xBC\xAF\x27\x1C';
$filetypes['7z']['label'] = '7z - .7z';

$filetypes['hqx']['type'] = 'archive';
$filetypes['hqx']['format'] = 'binhex';
$filetypes['hqx']['mime'] = 'application/binhex';
$filetypes['hqx']['id_regex'] = '^\(This file must be converted with BinHex\)';
$filetypes['hqx']['label'] = 'Binhex - .hqx';

$filetypes['hex'] = $filetypes['hqx'];

$filetypes['lzh']['type'] = 'archive';
$filetypes['lzh']['format'] = 'lzh';
$filetypes['lzh']['mime'] = 'application/x-lzh-compressed';
$filetypes['lzh']['id_regex'] = '^.{2}\x2D\x6C\x68';
$filetypes['lzh']['label'] = 'LZH - .lzh, .lba';

$filetypes['lha'] = $filetypes['lzh'];

$filetypes['zip']['type'] = 'archive';
$filetypes['zip']['format'] = 'zip';
$filetypes['zip']['mime'] = 'application/zip';
$filetypes['zip']['id_regex'] = '^PK\x03\x04';
$filetypes['zip']['label'] = 'Zip - .zip';

$filetypes['rar']['type'] = 'archive';
$filetypes['rar']['format'] = 'rar';
$filetypes['rar']['mime'] = 'application/x-rar-compressed';
$filetypes['rar']['id_regex'] = '^Rar\x21\x1A\x07\x00';
$filetypes['rar']['label'] = 'RAR - .rar';

$filetypes['sit']['type'] = 'archive';
$filetypes['sit']['format'] = 'stuffit';
$filetypes['sit']['mime'] = 'application/x-stuffit';
$filetypes['sit']['id_regex'] = '^StuffIt \(c\)1997-|StuffIt\!|^SIT\!';
$filetypes['sit']['label'] = 'StuffIt - .sit, .sitx';

$filetypes['sitx'] = $filetypes['sit'];

$filetypes['iso']['type'] = 'archive';
$filetypes['iso']['format'] = 'iso';
$filetypes['iso']['mime'] = 'application/x-iso-image';
$filetypes['iso']['id_regex'] = '^(.{32769}|.{34817}|.{36865})CD001';
$filetypes['iso']['label'] = 'ISO Disk Image - .iso';

$filetypes['dmg']['type'] = 'archive';
$filetypes['dmg']['format'] = 'dmg';
$filetypes['dmg']['mime'] = 'application/x-apple-diskimage';
$filetypes['dmg']['id_regex'] = 'koly.{508}$';
$filetypes['dmg']['label'] = 'Apple Disk Image - .dmg';
