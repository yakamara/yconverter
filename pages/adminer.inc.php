<?php

/**
 * This file is part of the YConverter package.
 *
 * @author (c) Yakamara Media GmbH & Co. KG
 * @author Thomas Blum <thomas.blum@yakamara.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


// Adminer extension, the function is called automatically by adminer
function adminer_object()
{
    include __DIR__ .'/../lib/Adminer.php';

    // Avoid warning "A non-numeric value encountered"
    error_reporting(error_reporting() & ~E_WARNING);

    return new rex_adminer();
}

// auto login and db selection
$_GET['username'] = '';
$_GET['db'] = $REX['DB']['1']['NAME'];

// becaause adminer is not included in global scope this var must be made global
$GLOBALS['rg'] = &$_SESSION['translations'];

/**
 * Cleans all output buffers.
 */
while (ob_get_length()) {
    ob_end_clean();
}

// add page param to all adminer urls
ob_start(function ($output) {
    return preg_replace('#(?<==(?:"|\'))index\.php\?(?=username=&amp;db=|file=[^&]*&amp;version=)#', 'index.php?page=yconverter&amp;subpage=adminer&amp;', $output);
});
include __DIR__ .'/../vendor/adminer.php';


// make sure the output buffer callback is called
while (ob_get_level()) {
    ob_end_flush();
}

exit;
