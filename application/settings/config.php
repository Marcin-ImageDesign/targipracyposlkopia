<?php

$configPath = ROOT_PATH . '/config.php';
$config = file_exists($configPath) ? include $configPath : null;

define('CHAT_URL', $config['chat_url'] ?? '');
define('CDN_DOMAIN', $config['cdn_domain'] ?? '');

$domain = str_replace('www.', '', @$_SERVER['HTTP_HOST']);
if (defined('SSL_URL_PATH')) {
    $domain = str_replace(['http://', 'https://', '/'], '', SSL_URL_PATH);
}

define('DOMAIN', $domain);
define('VCMS_DOMAIN', $domain);
define('CHARSET', 'UTF-8');
define('DS', DIRECTORY_SEPARATOR);

define('DIR_PRIVILIGES', 0777);
define('FILE_PRIVILIGES', 0666);

$maxFileSize = 25 * 1024 * 1024;
$uploadMaxFilesize = convertToBytes(ini_get('upload_max_filesize'));
$postMaxSize = convertToBytes(ini_get('post_max_size'));

define('MAX_FILE_SIZE', min($maxFileSize, $uploadMaxFilesize, $postMaxSize));
define('ALLOWED_FILE_EXTENSIONS', 'pdf, doc, docx, ppt, pptx, odt, zip, jpg, png, gif, webp, svg');
define('ALLOWED_IMAGE_EXTENSIONS', 'jpg, png, gif, webp, svg');

function convertToBytes($val): int
{
    if (empty($val)) {
        return 0;
    }

    preg_match('#([0-9]+)[\s]*([a-z]+)#i', trim($val), $matches);

    $last = '';
    if (isset($matches[2])) {
        $last = $matches[2];
    }

    if (isset($matches[1])) {
        $val = (int)$matches[1];
    }

    switch (strtolower($last)) {
        case 'g':
        case 'gb':
            $val *= 1024;
        case 'm':
        case 'mb':
            $val *= 1024;
        case 'k':
        case 'kb':
            $val *= 1024;
    }

    return (int)$val;
}
