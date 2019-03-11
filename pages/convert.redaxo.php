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

use YConverter\YConverter;

$func = rex_request('func', 'string');
$csrfToken = rex_csrf_token::factory('yconverter');

if ($func && !$csrfToken->isValid()) {
    $error[] = rex_i18n::msg('csrf_token_invalid');
} elseif ('' !== $func) {
    $converter = new YConverter();
    switch ($func) {
        case 'callback':
            // Modify tables from version 4 to 5.
            $converter->callCallbacks();
            break;
        case 'clone':
            // Clone all tables of the outdated version.
            $converter->cloneTables();
            break;
        case 'modify':
            // Modify tables from version 4 to 5.
            $converter->modifyTables();
            break;
        case 'update':
            // Update tables to last version before 5.x
            $converter->updateTables();
            break;
        case 'run':
            //$converter->cloneTables();
            //$converter->updateTables();
            //$converter->modifyTables();
            //$converter->callCallbacks();
            $converter->getMissinColumns();
            //$converter->transferData();
            break;
    }
    echo $converter->getMessages();
}
echo sprintf('<p><a class="btn btn-save" href="%s">Run.</a></p>', rex_url::currentBackendPage(['func' => 'run'] + $csrfToken->getUrlParams()));

echo sprintf('<p><a class="btn btn-primary" href="%s">Clone all tables of the outdated version.</a></p>', rex_url::currentBackendPage(['func' => 'clone'] + $csrfToken->getUrlParams()));
echo sprintf('<p><a class="btn btn-primary" href="%s">Update tables to last version before 5.x</a></p>', rex_url::currentBackendPage(['func' => 'update'] + $csrfToken->getUrlParams()));
echo sprintf('<p><a class="btn btn-primary" href="%s">Modify tables from version 4 to 5.</a></p>', rex_url::currentBackendPage(['func' => 'modify'] + $csrfToken->getUrlParams()));
echo sprintf('<p><a class="btn btn-primary" href="%s">Call callbacks.</a></p>', rex_url::currentBackendPage(['func' => 'callback'] + $csrfToken->getUrlParams()));
