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
$config = array_merge([
    'db' => [
        '5' => [
            'host' => null,
            'login' => null,
            'password' => null,
            'name' => null,
            'persistent' => null,
        ],
    ],
    'core_version' => null,
    'table_prefix' => 'rex_',
], rex_file::getConfig($configFile));

$csrfToken = rex_csrf_token::factory('system');

if (!$csrfToken->isValid()) {
    $error[] = rex_i18n::msg('csrf_token_invalid');
} else {
    $newConfig = rex_post('settings', [
        ['host', 'string'],
        ['login', 'string'],
        ['password', 'string'],
        ['name', 'string'],
        ['persistent', 'bool'],
        ['core_version', 'string'],
        ['table_prefix', 'string', 'rex_'],
    ], null);

    if (is_array($newConfig)) {
        if ($newConfig['login'] != '' && $newConfig['password'] == '' && $config['db']['5']['password'] != '') {
            $newConfig['password'] = $config['db']['5']['password'];
        }
        $config = $newConfig;
        foreach (['host', 'login', 'password', 'name', 'persistent'] as $key) {
            $config['db']['5'][$key] = $config[$key];
            unset($config[$key]);
        }
        if (rex_file::putConfig($configFile, $config)) {
            echo rex_view::success($addon->i18n('settings_saved'));
        } else {
            echo rex_view::error($addon->i18n('settings_error', $configFile));
        }
    }
}

$coreVersion = new rex_select();
$coreVersion->setStyle('class="form-control"');
$coreVersion->setName('settings[core_version]');
$coreVersion->setAttribute('class', 'form-control selectpicker');
$coreVersion->setSize(1);
$coreVersion->setSelected($config['core_version']);

$coreVersion->addArrayOptions([
    '2.7.4',
    '3.0.0',
    '3.1.0',
    '3.2.0',
    '4.0.0',
    '4.0.1',
    '4.1.0',
    '4.2.0',
    '4.2.1',
    '4.3.0',
    '4.3.1',
    '4.3.2',
    '4.3.3',
    '4.4.0',
    '4.4.1',
    '4.5.0',
    '4.5.1',
    '4.6.0',
    '4.6.1',
    '4.6.2',
    '4.7.0',
    '4.7.1',
    '4.7.2',
    '4.7.3',
], false);

$formElements = [];

$n = [];
$n['label'] = '<label>'.$addon->i18n('core_version').'</label>';
$n['field'] = $coreVersion->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label>'.$addon->i18n('table_prefix').'</label>';
$n['field'] = '<input class="form-control" type="text" name="settings[table_prefix]" value="'.rex_escape($config['table_prefix']).'" />';
$formElements[] = $n;

$n = [];
$n['header'] = '<h3>'.$addon->i18n('database_connection').'</h3>';
$n['label'] = '<label>'.$addon->i18n('database_host').'</label>';
$n['field'] = '<input class="form-control" type="text" name="settings[host]" value="'.rex_escape($config['db']['5']['host']).'" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label>'.$addon->i18n('database_user').'</label>';
$n['field'] = '<input class="form-control" type="text" name="settings[login]" value="'.rex_escape($config['db']['5']['login']).'" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label>'.$addon->i18n('database_password').'</label>';
$n['field'] = '<input class="form-control" type="password" name="settings[password]" value="" placeholder="'.rex_escape(($config['db']['5']['password'] ? $addon->i18n('database_password_exists') : '')).'" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label>'.$addon->i18n('database_name').'</label>';
$n['field'] = '<input class="form-control" type="text" name="settings[name]" value="'.rex_escape($config['db']['5']['name']).'" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content = $fragment->parse('core/form/form.php');

$formElements = [];
$n = [];
$n['reverse'] = true;
$n['label'] = '<label>'.$addon->i18n('database_persistent').'</label>';
$n['field'] = '<input type="checkbox"  name="settings[persistent]" value="1" '.($config['db']['5']['persistent'] ? 'checked="checked" ' : '').'/>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/checkbox.php');

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="sendit"'.rex::getAccesskey(rex_i18n::msg('system_update'), 'save').'>'.rex_i18n::msg('system_update').'</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('system_settings'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
<form id="rex-form-system-setup" action="'.rex_url::currentBackendPage().'" method="post">
    <input type="hidden" name="func" value="updateinfos" />
    '.$csrfToken->getHiddenField().'
    '.$content.'
</form>';

echo $content;
