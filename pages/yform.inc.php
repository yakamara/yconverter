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

use YConverter\Converter;
use YConverter\YFormConverter;

$func = rex_request('func', 'string');
$sort = rex_request('sort', 'string');
$transfer = rex_request('transfer', 'bool', 0);
$transferTables = rex_request('transferTables', 'array', []);

if (OOAddon::isActivated('xform') && OOAddon::getVersion('xform') != '4.14') {
    echo rex_warning('XForm aktualisieren!');
}

if ('convert' == $func) {
    $converter = new YFormConverter();
    $converter->boot();
    $converter->run();
    $messages = $converter->getMessages();
    echo implode('', $messages);

} elseif ('transfer' == $func && $transfer) {
    if (true !== rex_sql::checkDbConnection($REX['DB']['5']['HOST'], $REX['DB']['5']['LOGIN'], $REX['DB']['5']['PSW'], $REX['DB']['5']['NAME'])) {
        $transfer = false;
        echo rex_warning($I18N->msg('setup_021'));
    }

    if ($transfer && count($transferTables) >= 1) {
        $converter = new YFormConverter();
        $converter->boot();
        $converter->transferToR5($transferTables);
        $messages = $converter->getMessages();
        echo implode('', $messages);
    }
}


$converter = new YFormConverter();
$converter->boot();
$r4Tables = $converter->getR4Tables();
$r5Tables = $converter->getR5Tables();
$changeableTables = $converter->getR5ChangeableTables();
$removableActions = $converter->getRemovableActions();
$removableValues = $converter->getRemovableValues();
sort($r4Tables);
sort($r5Tables);
sort($changeableTables);

$selectTransferTables = new rex_select();
$selectTransferTables->setId('rex-form-transfer-tables');
$selectTransferTables->setName('transferTables[]');
$selectTransferTables->setMultiple(1);
$selectTransferTables->setSelected($transferTables);
$selectTransferTables->setSize(count($r5Tables));
$selectTransferTables->addOptions($r5Tables, true);

echo '
<div class="rex-addon-output">
    <h2 class="rex-hl2">1. Phase <small style="font-size: 80%; font-weight: 400;">REDAXO 4 Tabellen kopieren und für REDAXO 5 vorbereiten</small></h2>
    
    <div class="rex-addon-content">
        <p class="rex-tx1">Diese Aktionen bzw. Felder werden aus der Tabelle der Felddefinition (rex_yform_field) gelöscht und müssen, sofern in YForm noch existieren, neu angelegt werden.</p>
        
        <div style="display: inline-block; margin-left: 150px;">
            <h3>Aktionen</h3>
            <code class="rex-code" style="display: inline-block;">' . implode('<br />', $removableActions) . '</code>        
        </div>
        <div style="display: inline-block; margin-left: 150px;">
            <h3>Values</h3>
            <code class="rex-code" style="display: inline-block;">' . implode('<br />', $removableValues) . '</code>        
        </div>
    </div>
    <hr />
    <div class="rex-addon-content">
        <p class="rex-tx1">Die nachfolgenden Tabellen werden  jetzt kopiert und für REDAXO 5 modifiziert.</p>
        <code class="rex-code" style="display: inline-block; margin-left: 150px;">' . implode('<br />', $r4Tables) . '</code>
    </div>
    <div class="rex-form">
        <form action="index.php" method="post">
            <input type="hidden" name="page" value="yconverter" />
            <input type="hidden" name="subpage" value="yform" />
            <input type="hidden" name="func" value="convert" />
            
            <fieldset class="rex-form-col-1">
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-submit"><input class="rex-form-submit" type="submit" value="Nun gut, auf geht\'s!." /></p>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

<div class="rex-addon-output">
    <h2 class="rex-hl2">2. Phase <small style="font-size: 80%; font-weight: 400;">Konvertierte Tabellen zur REDAXO 5 Instanz übertragen</small></h2>
    <div class="rex-addon-content">
        <p class="rex-tx1">Sollte es zu einem Timeout kommen, dann entweder die Anzahl der selektierten Tabellen reduzieren oder die Daten mit dem Adminer übertragen.</p>
    </div>
    <div class="rex-form">
        <form action="index.php" method="post">
            <input type="hidden" name="page" value="yconverter" />
            <input type="hidden" name="subpage" value="yform" />
            <input type="hidden" name="func" value="transfer" />
    
            <fieldset class="rex-form-col-1">
                <legend>Datenbankverbindung zu REDAXO 5</legend>
    
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-text">
                            <label for="rex-form-host">Host</label>
                            <input class="rex-form-text" type="text" id="rex-form-host" name="db[host]" value="' . htmlspecialchars($REX['DB']['5']['HOST']) . '" />
                        </p>
                    </div>
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-text">
                            <label for="rex-form-login">Login</label>
                            <input class="rex-form-text" type="text" id="rex-form-login" name="db[login]" value="' . htmlspecialchars($REX['DB']['5']['LOGIN']) . '" />
                        </p>
                    </div>
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-text">
                            <label for="rex-form-password">Passwort</label>
                            <input class="rex-form-text" type="password" id="rex-form-password" name="db[password]" value="' . htmlspecialchars($REX['DB']['5']['PSW']) . '" />
                        </p>
                    </div>
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-text">
                            <label for="rex-form-name">Name</label>
                            <input class="rex-form-text" type="text" id="rex-form-name" name="db[name]" value="' . htmlspecialchars($REX['DB']['5']['NAME']) . '" />
                        </p>
                    </div>
                </div>
            </fieldset>
    
            <fieldset class="rex-form-col-1">
                <legend>Transfer</legend>
                    
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
                            <br />
                            <input class="rex-form-checkbox" id="rex-form-transfer" type="checkbox" name="transfer" value="1" />
                            <label for="rex-form-transfer">Übertragen</label>
                        </p>
                    </div>
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-select">
                            <label for="rex-form-transfer-tables">Tabellen auswählen</label>
                            ' . $selectTransferTables->get() . '
                        </p>
                    </div>
                    <div class="rex-form-row">
                        <p class="rex-form-submit"><input class="rex-form-submit" type="submit" value="Daten transferieren." /></p>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>

';
