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

$func = rex_request('func', 'string');
$sort = rex_request('sort', 'string');
$id = rex_request('id', 'int');
$db = rex_request('db', 'array', []);
$transfer = rex_request('transfer', 'bool', 0);
$transferTables = rex_request('transferTables', 'array', []);

$REX['DB']['5']['HOST'] = isset($db['host']) ? $db['host'] : $REX['DB']['1']['HOST'];
$REX['DB']['5']['LOGIN'] = isset($db['login']) ? $db['login'] : '';
$REX['DB']['5']['PSW'] = isset($db['password']) ? $db['password'] : '';
$REX['DB']['5']['NAME'] = isset($db['name']) ? $db['name'] : '';
$REX['DB']['5']['PERSISTENT'] = false;

if ('convert' == $func) {
    $converter = new Converter();
    $converter->run();
    $messages = $converter->getMessages();
    echo implode('', $messages);

} elseif ('transfer' == $func && $transfer) {

    if (true !== rex_sql::checkDbConnection($REX['DB']['5']['HOST'], $REX['DB']['5']['LOGIN'], $REX['DB']['5']['PSW'], $REX['DB']['5']['NAME'])) {
        $transfer = false;
        echo rex_warning($I18N->msg('setup_021'));
    }

    if ($transfer && count($transferTables) >= 1) {
        $converter = new Converter();
        $converter->transferToR5($transferTables);
        $messages = $converter->getMessages();
        echo implode('', $messages);
    }
}


$converter = new Converter();
$r4Tables = $converter->getR4Tables();
$r5Tables = $converter->getR5Tables();
$changeableTables = $converter->getR5ChangeableTables();
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
    <h2 class="rex-hl2">Tabellen und Daten für REDAXO 5 konvertieren</h2>

    <div class="rex-addon-content">
        <h3>Was wird passieren?</h3>
        <p class="rex-tx1">Die nachfolgenden Tabellen werden in ihrer Struktur und Daten kopiert und für REDAXO 5 modifiziert. Die Tabellenspalten werden angepasst, nicht mehr genutzte Spalten gelöscht, Inhalte teilweise verschoben bzw. konvertiert.<code class="rex-code">' . implode(', ', $r4Tables) . '</code></p>
        
        <h3>Vorgehen</h3>
        <ol>
            <li><b>REDAXO 5</b> und das AddOn <b>Adminer</b> via <b>Installer</b> installieren.</li>
            <li>Unten den Button klicken und REDAXO 4 Tabellen konvertieren lassen.</li>
            <li>Den <b>Adminer hier im REDAXO 4</b> in neuem Tab aufrufen.</li>
            <li>Im <b>Adminer von REDAXO 4</b> oben links auf <b>Exportieren</b> klicken.</li>
            <li>Tabellen und Daten alle wegklicken (im Tabellenkopf).</li>
            <li>Nur die Daten auswählen, bei den die Tabelle mit <b>' . $converter->getTablePrefix() . '</b> beginnen.</li>
            <li>Button <b>Exportieren</b> klicken.</li>
            <li>Daten kopieren.</li>
            <li>Im <b>Admin von REDAXO 5</b> oben links <b>SQL-Kommando</b> klicken und das Kopierte in das Textfeld einfügen.</li>
            <li>Nach <b>' . $converter->getTablePrefix() . '</b> im Textfeld suchen und löschen.</li>
            <li>Den Button <b>Ausführen</b> klicken.</li>
        </ol>
    </div>
</div>
    
<div class="rex-addon-output">
    <h2 class="rex-hl2">1. Phase <small style="font-size: 80%; font-weight: 400;">REDAXO 4 Tabellen kopieren und für REDAXO 5 vorbereiten</small></h2>
    
    <div class="rex-addon-content">
        <p class="rex-tx1">Die nachfolgenden Tabellen werden in ihrer Struktur und Daten kopiert und für REDAXO 5 modifiziert. Die Tabellenspalten werden angepasst, nicht mehr genutzte Spalten gelöscht, Inhalte teilweise verschoben bzw. konvertiert.</p>
        <code class="rex-code" style="display: inline-block; margin-left: 150px;">' . implode('<br />', $r4Tables) . '</code>
    </div>
    <div class="rex-form">
        <form action="index.php" method="post">
            <input type="hidden" name="page" value="yconverter" />
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

/*
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-radio rex-form-label-right">
                            <br />
                            <input class="rex-form-radio" id="rex-form-transfer-all" type="radio" name="transferType" value="all" />
                            <label for="rex-form-transfer-all">Alle Tabellen</label>
                            <br class="rex-clearer" />
                            <br />
                            <code class="rex-code" style="display: inline-block; margin-left: 175px;">' . str_replace($converter->getTablePrefix(), '', implode(', <br />', $r5Tables)) . '</code>
                            <br />
                            <br />
                        </p>
                    </div>
                    <div class="rex-form-row">
                        <p class="rex-form-col-a rex-form-radio rex-form-label-right">
                            <br />
                            <input class="rex-form-radio" id="rex-form-transfer-changeable" type="radio" name="transferType" value="changeable" />
                            <label for="rex-form-transfer-changeable">Nur veränderbare Tabellen</label>
                            <br class="rex-clearer" />
                            <br />
                            <code class="rex-code" style="display: inline-block; margin-left: 175px;">' . str_replace($converter->getTablePrefix(), '', implode(', <br />', $changeableTables)) . '</code>
                            <br />
                            <br />
                        </p>
                    </div>*/
