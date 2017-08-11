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

$myaddon = 'yconverter';
if ($REX['REDAXO'] && is_object($REX['USER'])) {
    $I18N->appendFile(__DIR__ . '/lang/');

    $REX['ADDON']['name'][$myaddon] = 'YConverter';
    $REX['ADDON']['perm'][$myaddon] = 'admin[]';
    $REX['ADDON']['author'][$myaddon] = 'Yakamara Media GmbH & Co. KG';
    $REX['ADDON']['version'][$myaddon] = '0.1';


    $REX['ADDON']['pages'][$myaddon] = array();
    $REX['ADDON']['pages'][$myaddon][] = array('', 'Core konvertieren');
    $REX['ADDON']['pages'][$myaddon][] = array('yform', 'XForm konvertieren');
    $REX['ADDON']['pages'][$myaddon][] = array('adminer', 'Adminer');

    if ($REX['USER']->isAdmin() && !isset($_REQUEST['page']) && isset($_GET['username']) && isset($_GET['db'])) {
        $_REQUEST['page'] = 'yconverter';
        $_REQUEST['subpage'] = 'adminer';
    }
}
