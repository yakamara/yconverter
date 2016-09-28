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

class rex_adminer extends Adminer
{
    function credentials()
    {
        global $REX;
        $db = $REX['DB']['1'];
        return [$db['HOST'], $db['LOGIN'], $db['PSW']];
    }

    function database()
    {
        global $REX;
        return $REX['DB']['1']['NAME'];
    }

    function databases($flush = true)
    {
        return [];
    }

    function databasesPrint($missing)
    {
    }
}
