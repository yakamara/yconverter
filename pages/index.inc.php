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

$subpage = rex_request('subpage', 'string');

$dir = $REX['INCLUDE_PATH'] . '/addons/yconverter/lib/';
require_once $dir . 'YConverter/Converter.php';
require_once $dir . 'YConverter/YFormConverter.php';
require_once $dir . 'helper.php';


$id = rex_request('id', 'int');
$db = rex_request('db', 'array', []);

$REX['DB']['5']['HOST'] = isset($db['host']) ? $db['host'] : $REX['DB']['1']['HOST'];
$REX['DB']['5']['LOGIN'] = isset($db['login']) ? $db['login'] : '';
$REX['DB']['5']['PSW'] = isset($db['password']) ? $db['password'] : '';
$REX['DB']['5']['NAME'] = isset($db['name']) ? $db['name'] : '';
$REX['DB']['5']['PERSISTENT'] = false;


include rex_path::core('layout/top.php');

rex_title('YConverter', $REX['ADDON']['pages']['yconverter']);

if ($subpage == 'adminer' && $REX['USER']->isAdmin()) {
    require $REX['INCLUDE_PATH'] . '/addons/yconverter/pages/adminer.inc.php';
} elseif ($subpage == 'yform' && $REX['USER']->isAdmin()) {
    require $REX['INCLUDE_PATH'] . '/addons/yconverter/pages/yform.inc.php';
} else {
    require $REX['INCLUDE_PATH'] . '/addons/yconverter/pages/convert.inc.php';
}

include rex_path::core('layout/bottom.php');
