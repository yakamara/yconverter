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

class Shuttle
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
        $this->tables = array_keys($package->getTables());
    }

    public function getMessage()
    {
        return $this->message;
    }


    public function transfer()
    {
        $tablesTransfered = [];
        $insertSize = 4000;

        foreach ($this->tables as $table) {
            $r5Columns = \rex_sql::showColumns(\rex::getTable($table));
            $convertColumns = \rex_sql::showColumns($this->config->getConverterTable($table));

            $r5ColumnNames = array_column($r5Columns, 'name');
            $convertColumnNames = array_column($convertColumns, 'name');
            $transferColumns = array_intersect($r5ColumnNames, $convertColumnNames);

            foreach ($convertColumns as $index => $convertColumn) {
                if (!in_array($convertColumn['name'], $transferColumns)) {
                    unset($convertColumns[$index]);
                    continue;
                }
            }

            $this->sql->setQuery('TRUNCATE '.$this->sql->escapeIdentifier(\rex::getTable($table)));
            $sql = \rex_sql::factory();
            $start = 0;
            $max = $insertSize;
            $nl = "\n";

            do {
                $array = $sql->getArray('SELECT * FROM '.$sql->escapeIdentifier($this->config->getConverterTable($table)).' LIMIT '.$start.','.$max, [], \PDO::FETCH_NUM);
                $count = $sql->getRows();

                if ($count == 0) {
                    break;
                }

                $start += $max;
                $values = [];

                foreach ($array as $row) {
                    $record = [];

                    foreach ($convertColumns as $index => $convertColumn) {
                        $column = $row[$index];
                        $record[] = $sql->escape($column);
                    }

                    $values[] = $nl.'  ('.implode(',', $record).')';
                }

                if (!empty($values)) {;
                    $this->sql->setQuery('INSERT INTO '.$this->sql->escapeIdentifier(\rex::getTable($table)).' VALUES '.implode(',', $values).';');
                    unset($values);
                }
            } while ($count >= $max);

            if (!$this->sql->hasError()) {
                $tablesTransfered[] = $table;
            }
        }

        if (\count($tablesTransfered)) {
            $this->message->addSuccess(sprintf('Folgende Tabellen und deren Inhalte wurden erfolgreich in die REDAXO 5 Instanz kopiert.<br /><br /><pre class="rex-code">%s</pre>', implode('<br />', $tablesTransfered)));
        }



        if (\rex_addon::get('developer')->isAvailable()) {
            \rex_dir::delete(\rex_developer_manager::getBasePath().'action');
            \rex_dir::delete(\rex_developer_manager::getBasePath().'modules');
            \rex_dir::delete(\rex_developer_manager::getBasePath().'templates');
        }


        rex_delete_cache();
    }
}
