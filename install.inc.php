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

$msg = '';

$minPhpVersion = '5.4.0';
$minRexVersion = '4.6.0';
if (version_compare(PHP_VERSION, $minPhpVersion) < 0) {
    $msg = 'Es wird mindestens PHP '. $minPhpVersion .' benötigt!';
} elseif (version_compare($REX['VERSION'] . '.' . $REX['SUBVERSION'] . '.' . $REX['MINORVERSION'], $minRexVersion) < 0) {
    $msg = 'Es wird mindestens REDAXO '. $minRexVersion .' benötigt!';
}

if ($msg != '') {
    $REX['ADDON']['installmsg']['yconverter'] = $msg;
    $REX['ADDON']['install']['yconverter'] = false;
} else {
    $REX['ADDON']['install']['yconverter'] = true;
}
