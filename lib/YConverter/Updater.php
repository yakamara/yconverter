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

namespace YConverter;

use YConcerter\Package\Package;

class Updater
{
    private $config;
    private $message;
    private $sql;
    private $package;

    public function __construct(Config $config, Message $message, Package $package)
    {
        $this->sql = \rex_sql::factory();
        $this->sql->setDebug(false);

        $this->config = $config;
        $this->message = $message;
        $this->package = $package;
    }

    public function run()
    {
        $this->package->updateTableStructure();
        $this->message->addSuccess(sprintf('Die Tabellenstrukturen wurden erfolgreich angepasst.'));
    }

    public function getMessage()
    {
        return $this->message;
    }

    public static function convertTablesToUtf8($tables)
    {
        foreach ($tables as $table) {
            self::convertTableToUtf8($table);
        }
    }

    private static function convertTableToUtf8($table)
    {
        $sql = \rex_sql::factory();
        $sql->setQuery('ALTER TABLE `'.$table.'` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
    }
}
