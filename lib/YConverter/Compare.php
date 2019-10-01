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

class Compare
{
    private $config;
    private $message;
    private $sql;

    private $tables;
    private $package;

    public function __construct(Config $config, Message $message, Package $package)
    {
        $this->sql = \rex_sql::factory();
        $this->sql->setDebug(false);

        $this->config = $config;
        $this->message = $message;

        $this->package = $package;
        $this->tables = $package->getTables();
    }

    public function getMessage()
    {
        if ('' === $this->message->getAll()) {
            $this->message->addSuccess('Es scheint alles gut zu sein.');
        }
        return $this->message;
    }

    public function run()
    {
        $missingTables = [];
        foreach ($this->tables as $table => $versions) {
            $originalTable = \rex::getTable($table);
            $convertTable = $this->config->getConverterTable($table);

            $sqlTable = \rex_sql_table::get($originalTable);
            if (!$sqlTable->exists()) {
                $missingTables[] = $sqlTable->getName();
                continue;
            }

            $originalColumns = \rex_sql::showColumns($originalTable);
            $convertColumns = \rex_sql::showColumns($convertTable);

            $originalNames = array_column($originalColumns, 'name');
            $convertNames = array_column($convertColumns, 'name');

            $missingColumns = array_diff($convertNames, $originalNames);

            if (count($missingColumns)) {
                $this->message->addWarning(sprintf('Folgende Felder sind der REDAXO 5 Tabelle <code>%s</code> nicht bekannt <ul><li>%s</li></ul>', $originalTable, implode('</li><li>', $missingColumns)));
            }
        }
        if (count($missingTables)) {
            $this->message->addWarning(
                sprintf('Folgende Tabellen sind der REDAXO 5 Instanz nicht bekannt <ul><li>%s</li></ul><p><br />AddOn <code>%s</code> installieren oder aktualisieren</p>',
                    implode('</li><li>', $missingTables),
                    ucfirst($this->package->getName())
                )
            );
        }
    }

}
