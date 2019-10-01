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

use YConcerter\Package\Core;
use YConcerter\Package\Cronjob;
use YConcerter\Package\Sprog;
use YConcerter\Package\YForm;
use YConverter\YConverter;

$func = rex_request('func', 'string');
$pack = rex_request('package', 'string');
$csrfToken = rex_csrf_token::factory('yconverter');


$panels = [
    'yconverter' => [
        'heading' => rex_i18n::msg('yconverter_clone_tables'),
        'steps' => [
            'clone' => rex_i18n::msg('yconverter_execute'),
        ],
    ],
    'core' => [
        'heading' => 'Core Tabellen',
        'steps' => [
            'update' => rex_i18n::msg('yconverter_update_table_structures_to_last_version'),
            'modify' => rex_i18n::msg('yconverter_modify_table_contents'),
            'compare' => rex_i18n::msg('yconverter_compare_tables_and_columns'),
            'transfer' => rex_i18n::msg('yconverter_transfer_data_to_instance'),
        ]
    ],
    'cronjob' => [
        'heading' => 'Cronjob Tabellen',
        'info' => \rex_i18n::msg('yconverter_cronjob_info'),
        'steps' => [
            'update' => rex_i18n::msg('yconverter_update_table_structures_to_last_version'),
            'modify' => rex_i18n::msg('yconverter_modify_table_contents'),
            'compare' => rex_i18n::msg('yconverter_compare_tables_and_columns'),
            'transfer' => rex_i18n::msg('yconverter_transfer_data_to_instance'),
        ]
    ],
    'sprog' => [
        'heading' => 'Sprog Tabellen',
        'steps' => [
            'update' => rex_i18n::msg('yconverter_update_table_structures_to_last_version'),
            'modify' => rex_i18n::msg('yconverter_modify_table_contents'),
            'compare' => rex_i18n::msg('yconverter_compare_tables_and_columns'),
            'transfer' => rex_i18n::msg('yconverter_transfer_data_to_instance'),
        ]
    ],
    'yform' => [
        'heading' => 'YForm Tabellen',
        'steps' => [
            'update' => rex_i18n::msg('yconverter_update_table_structures_to_last_version'),
            'modify' => rex_i18n::msg('yconverter_modify_table_contents'),
            'compare' => rex_i18n::msg('yconverter_compare_tables_and_columns'),
            'transfer' => rex_i18n::msg('yconverter_transfer_data_to_instance'),
        ]
    ],
];

if ($func && !$csrfToken->isValid()) {
    $error[] = rex_i18n::msg('csrf_token_invalid');
} elseif ('' !== $func) {

    if ($func == 'reset') {
        foreach ($panels as $package => $panel) {
            \rex_config::remove('yconverter', $package);
        }
    } else {
        switch ($pack) {
            case 'core':
            case 'yconverter':
                $package = new Core();
                break;
            case 'cronjob':
                $package = new Cronjob();
                break;
            case 'sprog':
                $package = new Sprog();
                break;
            case 'yform':
                $package = new YForm();
                break;
            default:
                $package = null;
                break;
        }

        $converter = new YConverter($package);
        switch ($func) {
            case 'clone':
                // Clone all tables of the outdated version.
                $converter->cloneTables();
                break;

            case 'compare':
                // check missing tables and columns in version 5
                $converter->compareTables();
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
                $converter->compareTables();
                $converter->updateTables();
                $converter->modifyTables();
                $converter->transferData();
                break;
        }
        echo $converter->getMessages();
    }
}



echo sprintf(
    '<p>
        <a class="btn btn-default" href="%s">'.rex_i18n::msg('yconverter_reset_and_start_again').'</a>
    </p>', rex_url::currentBackendPage(['func' => 'reset'] + $csrfToken->getUrlParams()));


$echo = '';

$echo .= '<div class="row">';
foreach ($panels as $package => $panel) {
    $status = rex_config::get('yconverter', $package, []);
    $listItems = [];
    $counter = 0;
    foreach ($panel['steps'] as $key => $label) {
        $counter++;
        if (in_array($key, $status)) {
            $listItems[] = sprintf(
                '<div class="list-group-item disabled">
                    <h4 class="list-group-item-heading">%s</h4>
                    <p class="list-group-item-text">%s</p>
                </div>', $counter, $label);
        } else {
            $listItems[] = sprintf(
                '<a class="list-group-item" href="%s">
                    <h4 class="list-group-item-heading">%s</h4>
                    <p class="list-group-item-text">%s</p>
                </a>',rex_url::currentBackendPage(['func' => $key, 'package' => $package] + $csrfToken->getUrlParams()), $counter, $label);
        }
    }

    $footer = '';
    if (isset($panel['info']) && $panel['info'] != '') {
        $footer = sprintf('<div class="panel-footer">%s</div>', $panel['info']);
    }

    $echo .=
        '<div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">'.$panel['heading'].'</div>
                <div class="list-group">
                    '.implode('', $listItems).'
                </div>
                '.$footer.'
            </div>
        </div>';
}
$echo .= '</div>';

echo $echo;

//
//if (in_array($key, $status)) {
//    echo sprintf(
//        '<div class="list-group-item disabled">
//            <h4 class="list-group-item-heading">%s</h4>
//            <p class="list-group-item-text">%s</p>
//        </div>', $counter, $label);
//} else {
//echo sprintf(
//    '<a class="list-group-item" href="%s">
//        <h4 class="list-group-item-heading">%s</h4>
//        <p class="list-group-item-text">%s</p>
//    </a>',rex_url::currentBackendPage(['func' => $key, 'package' => 'core'] + $csrfToken->getUrlParams()), $counter, $label);
//}
//
//
//echo
//    '<div class="panel panel-default">
//        <div class="panel-heading">Komplette Datenbank der alten Instanz klonen.</div>
//        <div class="list-group">
//
//        </div>
//    </div>';
//
//
//$buttons = [
//    'clone' => 'Clone all tables of the outdated version.',
//    'update' => 'Update table structures to last version 5.x',
//    'modify' => 'Modify table contents from version 4 to 5.',
//    'missing' => 'Check missing columns.',
//    'transfer' => 'Transfer data in REDAXO 5 Instance.',
//];
//
//echo
//    '<div class="panel panel-default">
//        <div class="panel-heading">Run all steps separately</div>
//        <div class="list-group">';
//
//$counter = 0;
//foreach ($buttons as $key => $label) {
//    $counter++;
//    if (in_array($key, $status)) {
//        echo sprintf(
//            '<div class="list-group-item disabled">
//                <h4 class="list-group-item-heading">%s</h4>
//                <p class="list-group-item-text">%s</p>
//            </div>', $counter, $label);
//    } else {
//        echo sprintf(
//            '<a class="list-group-item" href="%s">
//                <h4 class="list-group-item-heading">%s</h4>
//                <p class="list-group-item-text">%s</p>
//            </a>',rex_url::currentBackendPage(['func' => $key, 'package' => 'core'] + $csrfToken->getUrlParams()), $counter, $label);
//    }
//}
//echo '</div></div>';
//
//echo sprintf(
//    '<p>
//        <a class="btn btn-save" href="%s">Sprog Run all steps at once.</a>
//        <a class="btn btn-default" href="%s">Reset and start again.</a>
//    </p>', rex_url::currentBackendPage(['func' => 'run', 'package' => 'sprog'] + $csrfToken->getUrlParams()), rex_url::currentBackendPage(['func' => 'reset'] + $csrfToken->getUrlParams()));
//
//
