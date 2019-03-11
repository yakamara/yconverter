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
$addon = rex_addon::get('yconverter');

$configFile = $addon->getDataPath('config.yml');
$config = rex_file::getConfig($configFile);

if (rex::isBackend() && rex::getUser()) {
    if (isset($config['db'])) {
        $dbconfig = rex::getProperty('db');
        $dbconfig = $dbconfig + $config['db'];
        rex::setProperty('db', $dbconfig);
    }
    //$sql = rex_sql::factory(5);
}
