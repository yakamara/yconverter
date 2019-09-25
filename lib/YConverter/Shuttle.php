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

class Shuttle
{
    private $config;
    private $message;
    private $sql;

    private $tables;

    public function __construct(Config $config, Message $message)
    {
        $this->sql = \rex_sql::factory();
        $this->sql->setDebug(false);

        $this->config = $config;
        $this->message = $message;

        $this->boot();
    }

    private function boot()
    {
        $this->tables = [
            // MetaInfo
            // - - - - - - - - - - - - - - - - - -
            'metainfo_field' => [
                '4.0.0' => [
                ],
            ],
            'metainfo_type' => [
                '4.0.0' => [
                ],
            ],

            // MediaManager
            // - - - - - - - - - - - - - - - - - -
            'media_manager_type' => [
                '4.0.0' => [
                ],
            ],
            'media_manager_type_effect' => [
                '4.0.0' => [
                ],
            ],

            // Action
            // - - - - - - - - - - - - - - - - - -
            'action' => [
                '2.7.0' => [
                ],
            ],

            // Articles
            // - - - - - - - - - - - - - - - - - -
            'article' => [
                '2.7.0' => [
                ],
            ],

            // Article Slices
            // - - - - - - - - - - - - - - - - - -
            'article_slice' => [
                '2.7.0' => [
                ],
            ],

            // Clang
            // - - - - - - - - - - - - - - - - - -
            'clang' => [
                '2.7.0' => [
                ],
            ],

            // Media
            // - - - - - - - - - - - - - - - - - -
            'media' => [
                '2.7.0' => [
                ],
            ],

            'media_category' => [
                '2.7.0' => [
                ],
            ],

            // Module
            // - - - - - - - - - - - - - - - - - -
            'module' => [
                '2.7.0' => [
                ],
            ],
            'module_action' => [
                '2.7.0' => [],
            ],

            // Templates
            // - - - - - - - - - - - - - - - - - -
            'template' => [
                '2.7.0' => [
                ],
            ],
        ];
    }

    public function getMessage()
    {
        return $this->message;
    }

    //public function transfer()
    //{
    //    foreach ($this->tables as $table => $versions) {
    //        foreach ($versions as $fromVersion => $params) {
    //            if (\rex_string::versionCompare($this->config->getOutdatedCoreVersion(), $fromVersion, '<')) {
    //                continue;
    //            }
    //
    //            $originalTable = \rex::getTable($table);
    //            $convertTable = $this->config->getConverterTable($table);
    //
    //            $originalColumns = \rex_sql::showColumns($originalTable);
    //            $convertColumns = \rex_sql::showColumns($convertTable);
    //
    //            $originalNames = array_column($originalColumns, 'name');
    //            $convertNames = array_column($convertColumns, 'name');
    //
    //            $missingColumns = array_diff($convertNames, $originalNames);
    //
    //            if (count($missingColumns)) {
    //
    //            }
    //        }
    //    }
    //
    //}


    public function transfer()
    {
        $tablesTransfered = [];
        $insertSize = 4000;

        $tables = array_keys($this->tables);

        foreach ($tables as $table) {
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

                if (!empty($values)) {
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
    }
}
