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

class Cloner
{
    private $config;
    private $message;
    private $sql;

    public function __construct(Config $config, Message $message)
    {
        $this->sql = \rex_sql::factory();
        $this->sql->setDebug(false);

        $this->config = $config;
        $this->message = $message;
    }

    public function fetchTables()
    {
        $this->dropTables();

        $tablesCloned = [];
        $sqlOutdated = \rex_sql::factory($this->config->getOutdatedDatabaseId());
        $insertSize = 4000;

        $tables = [];
        foreach ($sqlOutdated->getTablesAndViews($this->config->getOutdatedTablePrefix()) as $table) {
            $tables[] = $table;
        }

        foreach ($tables as $table) {
            $tableClone = str_replace($this->config->getOutdatedTablePrefix(), $this->config->getConverterTablePrefix(), $table);

            $create = \rex_sql::showCreateTable($table, $this->config->getOutdatedDatabaseId());
            $create = str_replace(
                'CREATE TABLE `'.$this->config->getOutdatedTablePrefix(),
                'CREATE TABLE `'.$this->config->getConverterTablePrefix(),
                $create
            );

            $this->sql->setQuery($create);

            $fields = $sqlOutdated->getArray('SHOW FIELDS FROM '.$sqlOutdated->escapeIdentifier($table));

            foreach ($fields as &$field) {
                if (preg_match('#^(bigint|int|smallint|mediumint|tinyint|timestamp)#i', $field['Type'])) {
                    $field = 'int';
                } elseif (preg_match('#^(float|double|decimal)#', $field['Type'])) {
                    $field = 'double';
                } elseif (preg_match('#^(char|varchar|text|longtext|mediumtext|tinytext)#', $field['Type'])) {
                    $field = 'string';
                }
            }

            $start = 0;
            $max = $insertSize;
            $nl = "\n";

            do {
                $array = $sqlOutdated->getArray('SELECT * FROM '.$sqlOutdated->escapeIdentifier($table).' LIMIT '.$start.','.$max, [], \PDO::FETCH_NUM);
                $count = $sqlOutdated->getRows();

                if ($count == 0) {
                    break;
                }

                $start += $max;
                $values = [];

                foreach ($array as $row) {
                    $record = [];

                    foreach ($fields as $idx => $type) {
                        $column = $row[$idx];

                        switch ($type) {
                            case 'int':
                                $record[] = (int) $column;
                                break;
                            case 'double':
                                $record[] = sprintf('%.10F', (float) $column);
                                break;
                            case 'string':
                            default:
                                $record[] = $sqlOutdated->escape($column);
                                break;
                        }
                    }

                    $values[] = $nl.'  ('.implode(',', $record).')';
                }

                if (!empty($values)) {
                    $this->sql->setQuery('INSERT INTO '.$this->sql->escapeIdentifier($tableClone).' VALUES '.implode(',', $values).';');
                    unset($values);
                }
            } while ($count >= $max);

            if (!$this->sql->hasError()) {
                $tablesCloned[] = $table;
            }
        }

        if (\count($tablesCloned)) {
            $this->message->addSuccess(sprintf('Folgende Tabellen und deren Inhalte wurden erfolgreich in die REDAXO 5 Datenbank geklont.<br /><br /><pre class="rex-code">%s</pre>', implode('<br />', $tablesCloned)));
        }
    }

    public function getMessage()
    {
        return $this->message;
    }

    protected function dropTables()
    {
        $dbConfig = \rex::getProperty('db');
        if (!isset($dbConfig['1']['name'])) {
            return null;
        }
        $result = $this->sql->getArray('
            SELECT CONCAT("DROP TABLE IF EXISTS ", GROUP_CONCAT(table_name) , ";" ) AS drop_query 
            FROM information_schema.tables
            WHERE table_schema = "'.$dbConfig['1']['name'].'" 
            AND table_name LIKE "'.$this->config->getConverterTablePrefix().'%"');

        if (isset($result[0]['drop_query'])) {
            $this->sql->setQuery($result[0]['drop_query']);
            $this->message->addSuccess('Geklonte Tabellen wurden aus der REDAXO 5 Datenbank gelöscht.');
        } else {
            $this->message->addInfo('Es existierten keine geklonten Tabellen in der REDAXO 5 Datenbank.');
        }
    }
}
