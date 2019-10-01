<?php

namespace YConcerter\Package;

use YConverter\YConverter;

class YForm extends Package
{
    public function getName(): string
    {
        return 'yform';
    }

    public function getTables(): array
    {
        return [
            // E-Mail Templates
            // - - - - - - - - - - - - - - - - - -
            'yform_email_template' => [
                '2.7.0' => [
                    'convertPlaceholderToRexVar' => [
                        'subject', 'body', 'body_html',
                    ],
                ],
            ],

            // Fields
            // - - - - - - - - - - - - - - - - - -
            'yform_field' => [
                '2.7.0' => [
                    'callbacks' => [
                        [
                        //    'function' => 'callbackModifyGoogleGeocodeInTables',
                        //    'level' => YConverter::NORMAL
                        //], [
                            'function' => 'callbackModifyLangTextareaInTables',
                            'level' => YConverter::NORMAL
                        ], [
                            'function' => 'callbackChangeFields',
                            'level' => YConverter::NORMAL
                        ], [
                            'function' => 'callbackCleanFieldTable',
                            'level' => YConverter::LATE
                        ], [
                            'function' => 'callbackAddColumnsInInstanceTable',
                            'level' => YConverter::LATE
                        ],
                    ],
                    'removeActions' => [
                        'fulltext_value',
                        'wrapper_value',
                    ],
                    'removeValues' => [
                        'submits',
                        'upload',
                    ],
                    'convertValues' => [
                        'be_mediapool' => [
                            'type_name' => [
                                'value' => 'be_media',
                            ],
                        ],
                        'be_medialist' => [
                            'type_name' => [
                                'value' => 'be_media',
                            ],
                            'multiple' => [
                                'value' => '1',
                            ],
                        ],
                        'date' => [
                            'format' => [
                                'value' => 'DD.MM.YYYY',
                            ],
                        ],
                        'datetime' => [
                            'format' => [
                                'value' => 'DD.MM.YYYY HH:ii',
                            ],
                        ],
                        //'google_geocode' => [
                        //    'googleapikey' => [
                        //        'value' => 'DerYconverterKamSahUndVerschwand', // Das Feld wuerde ansonsten beim callbackCleanFieldTable gelöscht werden
                        //    ],
                        //    'position' => [
                        //        'value' => '',
                        //    ],
                        //],
                        'time' => [
                            'format' => [
                                'value' => 'HH:ii',
                            ],
                        ],
                    ],
                ],
            ],

            // Tables
            // - - - - - - - - - - - - - - - - - -
            'yform_table' => [
                '2.7.0' => [
                    'callbacks' => [
                        [
                            'function' => 'callbackCreateDataTables',
                            'level' => YConverter::LATE
                        ],
                    ],

                ],
            ],
        ];
    }

    public function updateTableStructure()
    {
        $config = $this->getConfig();

        \rex_sql_table::get($config->getConverterTable('xform_email_template'))
            ->ensureColumn(new \rex_sql_column('mail_reply_to', 'varchar(191)'))
            ->ensureColumn(new \rex_sql_column('mail_reply_to_name', 'varchar(191)'))
            ->setName($config->getConverterTable('yform_email_template'))
            ->alter();


        \rex_sql_table::get($config->getConverterTable('xform_field'))
            ->ensureColumn(new \rex_sql_column('db_type', 'varchar(191)'), 'type_name')
            ->ensureColumn(new \rex_sql_column('not_required', 'mediumtext'), 'label')

            // alte Felder,
            // sicherstellen das diese existieren, werden später umbenannt
            ->ensureColumn(new \rex_sql_column('css_class', 'text'))
            ->ensureColumn(new \rex_sql_column('hashname', 'text'))
            ->ensureColumn(new \rex_sql_column('message', 'text'))

            ->ensureColumn(new \rex_sql_column('address', 'text'))
            ->ensureColumn(new \rex_sql_column('attributes', 'text'))
            ->ensureColumn(new \rex_sql_column('category', 'text'))
            ->ensureColumn(new \rex_sql_column('check_perms', 'text'))
            ->ensureColumn(new \rex_sql_column('clang', 'text'))
            ->ensureColumn(new \rex_sql_column('columns', 'text'))
            ->ensureColumn(new \rex_sql_column('compare_type', 'text'))
            ->ensureColumn(new \rex_sql_column('compare_value', 'text'))
            //->ensureColumn(new \rex_sql_column('css_classes', 'text'))
            ->ensureColumn(new \rex_sql_column('current_date', 'text'))
            ->ensureColumn(new \rex_sql_column('default', 'text'))
            ->ensureColumn(new \rex_sql_column('empty_option', 'text'))
            ->ensureColumn(new \rex_sql_column('empty_value', 'text'))
            ->ensureColumn(new \rex_sql_column('field', 'text'))
            ->ensureColumn(new \rex_sql_column('fields', 'text'))
            ->ensureColumn(new \rex_sql_column('filter', 'text'))
            ->ensureColumn(new \rex_sql_column('format', 'text'))
            ->ensureColumn(new \rex_sql_column('from', 'text'))
            ->ensureColumn(new \rex_sql_column('function', 'text'))
            ->ensureColumn(new \rex_sql_column('googleapikey', 'text'))
            ->ensureColumn(new \rex_sql_column('height', 'text'))
            ->ensureColumn(new \rex_sql_column('homepage', 'text'))
            ->ensureColumn(new \rex_sql_column('hours', 'text'))
            ->ensureColumn(new \rex_sql_column('html', 'text'))
            ->ensureColumn(new \rex_sql_column('ignore_offlines', 'text'))
            ->ensureColumn(new \rex_sql_column('label', 'text'))
            ->ensureColumn(new \rex_sql_column('labels', 'text'))
            ->ensureColumn(new \rex_sql_column('max', 'text'))
            ->ensureColumn(new \rex_sql_column('max_size', 'text'))
            //->ensureColumn(new \rex_sql_column('messages', 'text'))
            ->ensureColumn(new \rex_sql_column('min', 'text'))
            ->ensureColumn(new \rex_sql_column('minutes', 'text'))
            ->ensureColumn(new \rex_sql_column('multiple', 'text'))
            ->ensureColumn(new \rex_sql_column('name', 'text'))
            ->ensureColumn(new \rex_sql_column('name2', 'text'))
            ->ensureColumn(new \rex_sql_column('names', 'text'))
            ->ensureColumn(new \rex_sql_column('no_db', 'text'))
            ->ensureColumn(new \rex_sql_column('notice', 'text'))
            ->ensureColumn(new \rex_sql_column('only_empty', 'text'))
            ->ensureColumn(new \rex_sql_column('options', 'text'))
            ->ensureColumn(new \rex_sql_column('params', 'text'))
            ->ensureColumn(new \rex_sql_column('pattern', 'text'))
            ->ensureColumn(new \rex_sql_column('php', 'text'))
            ->ensureColumn(new \rex_sql_column('position', 'text'))
            ->ensureColumn(new \rex_sql_column('preview', 'text'))
            ->ensureColumn(new \rex_sql_column('query', 'text'))
            ->ensureColumn(new \rex_sql_column('relation_table', 'text'))
            ->ensureColumn(new \rex_sql_column('required', 'text'))
            //->ensureColumn(new \rex_sql_column('salt', 'text'))
            ->ensureColumn(new \rex_sql_column('scale', 'text'))
            ->ensureColumn(new \rex_sql_column('scope', 'text'))
            ->ensureColumn(new \rex_sql_column('show_value', 'text'))
            ->ensureColumn(new \rex_sql_column('size', 'text'))
            ->ensureColumn(new \rex_sql_column('sizes', 'text'))
            ->ensureColumn(new \rex_sql_column('table', 'text'))
            ->ensureColumn(new \rex_sql_column('to', 'text'))
            ->ensureColumn(new \rex_sql_column('type', 'text'))
            ->ensureColumn(new \rex_sql_column('types', 'text'))
            ->ensureColumn(new \rex_sql_column('user', 'text'))
            ->ensureColumn(new \rex_sql_column('values', 'text'))
            ->ensureColumn(new \rex_sql_column('widget', 'text'))
            ->ensureColumn(new \rex_sql_column('width', 'text'))
            ->ensureColumn(new \rex_sql_column('year_end', 'text'))
            ->ensureColumn(new \rex_sql_column('year_start', 'text'))
            ->setName($config->getConverterTable('yform_field'))
            ->alter();

        // Alte Felder umbenennen
        \rex_sql_table::get($config->getConverterTable('yform_field'))
            ->renameColumn('css_class', 'css_classes')
            ->renameColumn('message', 'messages')
            ->renameColumn('hashname', 'salt')
            ->alter();

        \rex_sql_table::get($config->getConverterTable('xform_table'))
            ->ensureColumn(new \rex_sql_column('mass_deletion', 'tinyint(1)'))
            ->ensureColumn(new \rex_sql_column('mass_edit', 'tinyint(1)'))
            ->ensureColumn(new \rex_sql_column('schema_overwrite', 'tinyint(1)'))
            ->ensureColumn(new \rex_sql_column('history', 'tinyint(1)'))
            ->ensureColumn(new \rex_sql_column('list_amount', 'int(11)'))
            ->setName($config->getConverterTable('yform_table'))
            ->alter();
    }


    public function callbackModifyGoogleGeocodeInTables($params)
    {
        $config = $this->getConfig();

        // alle Tabellen mit google_geocode anpassen
        $sql = \rex_sql::factory();
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);

        $query = sprintf('SELECT * FROM %s WHERE `type_name` = "google_geocode" AND `type_id` = "value"', $r5TableEscaped);
        $tables = $sql->getArray($query);
        if (count($tables)) {
            foreach ($tables as $table) {
                if (!isset($table['position'])) {
                    continue;
                }
                $positionFields = explode(',', $table['position']);
                $latField = trim($positionFields[0]);
                $lngField = trim($positionFields[1]);
                $tableName = $config->getConverterTable($table['table_name']);
                $sql->setQuery('UPDATE `'.$tableName.'` SET `'.$table['name'].'` = CONCAT(`'.$latField.'`, ",", `'.$lngField.'`)');
                $sql->setQuery('ALTER TABLE `'.$tableName.'` DROP COLUMN `'.$latField.'`;');
                $sql->setQuery('ALTER TABLE `'.$tableName.'` DROP COLUMN `'.$lngField.'`;');
            }
        }
    }

    public function callbackModifyLangTextareaInTables($params)
    {
        //$config = $this->getConfig();
        //$sql = \rex_sql::factory();
        //$r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        //
        //$query = sprintf('SELECT `id`, `table_name`, `list_hidden`, `search`, `name`, `label`  FROM %s WHERE `type_name` = "lang_textarea" AND `type_id` = "value"', $r5Table);
        //$tables = $sql->getArray($query);
        //if (\count($tables)) {
        //    foreach ($tables as $table) {
        //        $clangs = $sql->getArray('SELECT `id`, `name` FROM '.$converter->getR4Table('clang'));
        //        $tableName = $converter->getR5Table($converter->removeR4TablePrefix($table['table_name']));
        //        $column = $table['name'];
        //        $modifyResults = $sql->getArray('SELECT `id`, `'.$column.'` FROM '.$tableName);
        //
        //        $valueParts = [];
        //        if (\count($modifyResults)) {
        //            foreach ($modifyResults as $modifyResult) {
        //                $valueParts[$modifyResult['id']] = explode('^^^^°°°°', $modifyResult[$column]);
        //            }
        //        }
        //
        //        parent::pr($valueParts);
        //
        //        foreach ($clangs as $clang) {
        //            $clang_id = (int) $clang['id'] + 1;
        //            $converter->db->setQuery('ALTER TABLE `'.$tableName.'` ADD COLUMN `'.$column.'_'.$clang_id.'` text');
        //
        //            $sqlInsert = \rex_sql::factory();
        //            $sqlInsert->debugsql = 0;
        //            $sqlInsert->setTable($r5Table);
        //            $sqlInsert->setValue('table_name', $table['table_name']);
        //            $sqlInsert->setValue('type_id', 'value');
        //            $sqlInsert->setValue('type_name', 'textarea');
        //            $sqlInsert->setValue('list_hidden', $table['list_hidden']);
        //            $sqlInsert->setValue('search', $table['search']);
        //            $sqlInsert->setValue('name', $column.'_'.$clang_id);
        //            $sqlInsert->setValue('label', $table['label'].' ['.$clang['name'].']');
        //            $sqlInsert->insert();
        //        }
        //
        //        foreach ($valueParts as $id => $valuePart) {
        //            foreach ($valuePart as $oldClangId => $input) {
        //                $clang_id = (int) $oldClangId + 1;
        //                $converter->db->setQuery('UPDATE `'.$tableName.'` SET `'.$column.'_'.$clang_id.'` = \''.$converter->db->escape($input).'\' WHERE id = "'.$id.'"');
        //            }
        //        }
        //        $converter->dropTableColumns($tableName, [$column]);
        //        $converter->db->setQuery('DELETE FROM `'.$r5Table.'` WHERE `id` = "'.$table['id'].'"');
        //    }
        //}
    }

    public function callbackChangeFields($params)
    {
        $sql = \rex_sql::factory();
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);

        // Actions aus der rex_yform_field löschen
        // - - - - - - - - - - - - - - - - - - - -
        $values = $params['removeActions'];
        if (\count($values)) {
            foreach ($values as $value) {
                $sql->setQuery('DELETE FROM '.$r5TableEscaped.' WHERE `type_id` = "action" AND `type_name` = "'.$value.'"');
            }
        }

        // Values aus der rex_yform_field löschen
        // - - - - - - - - - - - - - - - - - - - -
        $values = $params['removeValues'];
        if (\count($values)) {
            foreach ($values as $value) {
                $sql->setQuery('DELETE FROM '.$r5TableEscaped.' WHERE `type_id` = "value" AND `type_name` = "'.$value.'"');
            }
        }

        // Values in der rex_yform_field anpassen
        // - - - - - - - - - - - - - - - - - - - -
        $values = $params['convertValues'];
        if (\count($values)) {
            foreach ($values as $valueName => $columns) {
                $sets = [];
                foreach ($columns as $columnName => $data) {
                    if (isset($data['value'])) {
                        $sets[] = '`'.$columnName.'` = "'.$data['value'].'"';
                    }
                    if (isset($data['replaces'])) {
                        foreach ($data['replaces'] as $search => $replace) {
                            $sets[] = '`'.$columnName.'` = REPLACE(`'.$columnName.'`, "'.$search.'", "'.$replace.'")';
                        }
                    }
                }
                if (\count($sets)) {
                    $sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.implode(',', $sets).' WHERE `type_id` = "value" AND `type_name` = "'.$valueName.'"');
                }
            }
        }
    }

    public function callbackCleanFieldTable($params)
    {
        // rex_yform_field anpassen
        // leere Spalten löschen
        $sql = \rex_sql::factory();
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);

        $doNotDropColumns = [
            'id',
            'table_name',
            'prio',
            'type_id',
            'type_name',
            'db_type',
            'list_hidden',
            'search',
            'name',
            'label',
            'not_required',
        ];


        $checkColumnNames = [];
        $sql->setQuery('SELECT * FROM '.$r5TableEscaped.' LIMIT 0');
        foreach ($sql->getFieldnames() as $columnName) {
            if (!in_array($columnName, $doNotDropColumns)) {
                $checkColumnNames[] = $columnName;
            }
        }

        if (count($checkColumnNames)) {
            $dropColumnNames = [];
            foreach ($checkColumnNames as $checkColumnName) {
                $query = sprintf('SELECT * FROM %s WHERE `%s` != ""', $r5TableEscaped, $checkColumnName);
                $sql->setQuery($query);
                if ($sql->getRows() < 1) {
                    $dropColumnNames[] = $checkColumnName;
                }
            }
            if (count($dropColumnNames)) {
                foreach ($dropColumnNames as $dropColumn) {
                    $sql->setQuery('ALTER TABLE '.$r5TableEscaped.' DROP COLUMN `'.$dropColumn.'`;');
                }
            }
        }
    }

    public function callbackAddColumnsInInstanceTable($params)
    {
        $config = $this->getConfig();

        // rex_yform_field anpassen
        // Felder hinzufügen

        $table = $params['table'];
        $instanceTable = \rex::getTable($table);
        $converterTable = $config->getConverterTable($table);

        $instanceColumns = \rex_sql::showColumns($instanceTable);
        $converterColumns = \rex_sql::showColumns($converterTable);

        $instanceColumnNames = array_column($instanceColumns, 'name');
        $converterColumnNames = array_column($converterColumns, 'name');

        $missingColumnNames = array_diff($converterColumnNames, $instanceColumnNames);

        if (!count($missingColumnNames)) {
            return;
        }

        foreach ($missingColumnNames as $missingColumnName) {
            foreach ($converterColumns as $converterColumn) {
                if ($missingColumnName != $converterColumn['name']) {
                    continue;
                }

                \rex_sql_table::get($instanceTable)
                    ->ensureColumn(new \rex_sql_column($missingColumnName, $converterColumn['type']))
                    ->alter();
            }
        }
    }

    public function callbackCreateDataTables($params)
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();
        $table = $params['table'];
        $tables = $sql->getArray('SELECT `table_name` FROM '.$sql->escapeIdentifier($config->getConverterTable($table)));
        if (count($tables)) {
            foreach ($tables as $table) {
                $target = $table['table_name'];
                $source = str_replace($config->getOutdatedTablePrefix(), $config->getConverterTablePrefix(), $table['table_name']);
                $sql->setQuery('DROP TABLE IF EXISTS '.$target);
                $sql->setQuery('CREATE TABLE '.$target.' LIKE '.$source);
                $sql->setQuery('INSERT '.$target.' SELECT * FROM '.$source);
            }
        }
    }
}
