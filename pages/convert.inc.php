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

echo '<p class="rex-button"><a class="rex-button" href="index.php?page=yconverter&func=convert">Konvertieren</a></p>';
