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

if ('convert' == $func) {
    $converter = new Converter();
    $converter->run();
    $messages = $converter->getMessages();
    echo implode('', $messages);
}


$converter = new Converter();
$tables = $converter->getTables();

echo '
<div class="rex-addon-output">
    <h2 class="rex-hl2">Tabellen und Daten für REDAXO 5 konvertieren</h2>

    <div class="rex-addon-content">
        <h3>Was wird passieren?</h3>
        <p class="rex-tx1">Die nachfolgenden Tabellen werden in ihrer Struktur und Daten kopiert und für REDAXO 5 modifiziert. Die Tabellenspalten werden angepasst, nicht mehr genutzte Spalten gelöscht, Inhalte teilweise verschoben bzw. konvertiert.<code class="rex-code">' . implode(', ', $tables) . '</code></p>
        
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
        
        <h3>Probier ich das mal?</h3>
        <p class="rex-button"><a class="rex-button" href="index.php?page=yconverter&func=convert">Na klar.</a></p>
    </div>
</div>';

