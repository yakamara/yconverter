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
        case 'missing':
            // check missing columns in version 5
            $converter->getMissingColumns();
            break;
        case 'modify':
            // Modify tables from version 4 to 5.
            $converter->modifyTables();
            break;
        case 'transfer':
            // Update tables to last version before 5.x
            $converter->transferData();
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
            //$converter->getMissingColumns();
            //$converter->transferData();
            break;
    }
    echo $converter->getMessages();
}
echo sprintf('<p><a class="btn btn-save" href="%s">Run.</a></p>', rex_url::currentBackendPage(['func' => 'run'] + $csrfToken->getUrlParams()));

$buttons = [
    'clone' => 'Clone all tables of the outdated version.',
    'update' => 'Update table structures to last version before 5.x',
    'modify' => 'Modify tables from version 4 to 5.',
    'callback' => 'Call callbacks.',
    'missing' => 'Check missing columns.',
    'transfer' => 'Transfer data in REDAXO 5 Instance.',
];

echo '<div class="list-group">';
$counter = 0;
foreach ($buttons as $key => $label) {
    $counter++;
    echo sprintf('<a class="list-group-item" href="%s"><h4 class="list-group-item-heading">%s</h4><p class="list-group-item-text">%s</p></a>', rex_url::currentBackendPage(['func' => $key] + $csrfToken->getUrlParams()), $counter, $label);
}
echo '</div>';
