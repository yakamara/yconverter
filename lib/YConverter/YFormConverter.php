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

class YFormConverter extends Converter
{
    public static $tablePrefix = 'yconverter_';
    public static $phpValueField = 19;
    public static $htmlValueField = 20;
    public $tables = [];
    public $regex = [];
    public $messages = [];
    public $tableStructure = [];
    public $transferType = '';
    public $transferTypes = ['all', 'changeable'];

    const EARLY = -1;
    const NORMAL = 0;
    const LATE = 1;

    public function __construct()
    {
        global $REX;

        $this->rex = $REX;
        $this->transferType = '';

        $this->db = \rex_sql::factory();
        $this->db->debugsql = 0;
    }

    public function boot()
    {
        $this->matches = [
        ];
        $this->replaces = [
        ];

        $this->tables = [
            // E-Mail Templates
            // - - - - - - - - - - - - - - - - - -
            'xform_email_template' => [
                'r5Table' => 'yform_email_template',
                'isChangeable' => 0,
                'convertPlaceholderToRexVar' => [
                    'subject', 'body', 'body_html',
                ]
            ],

            // Fields
            // - - - - - - - - - - - - - - - - - -
            'xform_field' => [
                'r5Table' => 'yform_field',
                'isChangeable' => 0,
                'addColumns' => [
                    ['address' => 'TEXT NOT NULL'],
                    ['attributes' => 'TEXT NOT NULL'],
                    ['category' => 'TEXT NOT NULL'],
                    ['check_perms' => 'TEXT NOT NULL'],
                    ['clang' => 'TEXT NOT NULL'],
                    ['columns' => 'TEXT NOT NULL'],
                    ['compare_type' => 'TEXT NOT NULL'],
                    ['compare_value' => 'TEXT NOT NULL'],
                    ['css_classes' => 'TEXT NOT NULL'],
                    ['current_date' => 'TEXT NOT NULL'],
                    ['default' => 'TEXT NOT NULL'],
                    ['empty_option' => 'TEXT NOT NULL'],
                    ['empty_value' => 'TEXT NOT NULL'],
                    ['field' => 'TEXT NOT NULL'],
                    ['fields' => 'TEXT NOT NULL'],
                    ['filter' => 'TEXT NOT NULL'],
                    ['format' => 'TEXT NOT NULL'],
                    ['from' => 'TEXT NOT NULL'],
                    ['function' => 'TEXT NOT NULL'],
                    ['googleapikey' => 'TEXT NOT NULL'],
                    ['height' => 'TEXT NOT NULL'],
                    ['homepage' => 'TEXT NOT NULL'],
                    ['hours' => 'TEXT NOT NULL'],
                    ['html' => 'TEXT NOT NULL'],
                    ['ignore_offlines' => 'TEXT NOT NULL'],
                    ['label' => 'TEXT NOT NULL'],
                    ['labels' => 'TEXT NOT NULL'],
                    ['max' => 'TEXT NOT NULL'],
                    ['max_size' => 'TEXT NOT NULL'],
                    ['messages' => 'TEXT NOT NULL'],
                    ['min' => 'TEXT NOT NULL'],
                    ['minutes' => 'TEXT NOT NULL'],
                    ['multiple' => 'TEXT NOT NULL'],
                    ['name' => 'TEXT NOT NULL'],
                    ['name2' => 'TEXT NOT NULL'],
                    ['names' => 'TEXT NOT NULL'],
                    ['no_db' => 'TEXT NOT NULL'],
                    ['notice' => 'TEXT NOT NULL'],
                    ['only_empty' => 'TEXT NOT NULL'],
                    ['options' => 'TEXT NOT NULL'],
                    ['params' => 'TEXT NOT NULL'],
                    ['pattern' => 'TEXT NOT NULL'],
                    ['php' => 'TEXT NOT NULL'],
                    ['preview' => 'TEXT NOT NULL'],
                    ['query' => 'TEXT NOT NULL'],
                    ['relation_table' => 'TEXT NOT NULL'],
                    ['required' => 'TEXT NOT NULL'],
                    ['salt' => 'TEXT NOT NULL'],
                    ['scale' => 'TEXT NOT NULL'],
                    ['scope' => 'TEXT NOT NULL'],
                    ['show_value' => 'TEXT NOT NULL'],
                    ['size' => 'TEXT NOT NULL'],
                    ['sizes' => 'TEXT NOT NULL'],
                    ['table' => 'TEXT NOT NULL'],
                    ['to' => 'TEXT NOT NULL'],
                    ['type' => 'TEXT NOT NULL'],
                    ['types' => 'TEXT NOT NULL'],
                    ['user' => 'TEXT NOT NULL'],
                    ['values' => 'TEXT NOT NULL'],
                    ['widget' => 'TEXT NOT NULL'],
                    ['width' => 'TEXT NOT NULL'],
                    ['year_end' => 'TEXT NOT NULL'],
                    ['year_start' => 'TEXT NOT NULL'],
                ],
                'moveContents' => [
                    ['css_class' => 'css_classes'],
                    ['message' => 'messages'],
                    ['hashname' => 'salt'],
                ],
                'dropColumns' => [
                    'css_class',
                    'message',
                    'hashname',
                ],
                'doNotDropColumns' => [
                    'id',
                    'table_name',
                    'prio',
                    'type_id',
                    'type_name',
                    'list_hidden',
                    'search',
                    'name',
                    'label',
                    'not_required',
                ],
                'removeActions' => [
                    'fulltext_value',
                    'wrapper_value',
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
                            'value' => '1'
                        ]
                    ],
                    'date' => [
                        'format' => [
                            'replaces' => [
                                '###D###' => 'DD',
                                '###M###' => 'MM',
                                '###Y###' => 'YYYY',
                            ]
                        ]
                    ],
                    'datetime' => [
                        'format' => [
                            'replaces' => [
                                '###D###' => 'DD',
                                '###M###' => 'MM',
                                '###Y###' => 'YYYY',
                                '###H###' => 'HH',
                                '###I###' => 'ii',
                            ]
                        ]
                    ],
                    'google_geocode' => [
                        'googleapikey' => [
                            'value' => 'DerYconverterKamSahUndVerschwand' // Das Feld wuerde ansonsten beim callbackCleanFieldTable gelöscht werden
                        ],
                        'position' => [
                            'value' => ''
                        ]
                    ],
                    'time' => [
                        'format' => [
                            'replaces' => [
                                '###H###' => 'HH',
                                '###I###' => 'ii',
                            ]
                        ]
                    ]
                ],
                'removeValues' => [
                    'submits',
                    'upload'
                ],
                'callbacks' => [
                    ['YConverter\YFormConverter::callbackModifyGoogleGeocodeInTables'],
                    ['YConverter\YFormConverter::callbackModifyLangTextareaInTables'],
                    ['YConverter\YFormConverter::callbackChangeFields'],
                    ['YConverter\YFormConverter::callbackCleanFieldTable'],
                ],

            ],

            // Tables
            // - - - - - - - - - - - - - - - - - -
            'xform_table' => [
                'r5Table' => 'yform_table',
                'isChangeable' => 0,
                'addColumns' => [
                    ['mass_deletion' => 'tinyint(1)'],
                    ['mass_edit' => 'tinyint(1)'],
                    ['schema_overwrite' => 'tinyint(1)'],
                    ['history' => 'tinyint(1) '],
                ],
                'changeColumns' => [
                    ['list_amount' => 'list_amount int(11)'],
                ],
            ],
        ];

        // Added all XForm Tables
        // - - - - - - - - - - - - - - - - - -
        $xformTables = $this->db->getArray('SELECT `table_name` FROM ' . $this->getR4Table('xform_table'));
        if (count($xformTables)) {
            foreach ($xformTables as $xformTable) {
                $tableName = $this->removeR4TablePrefix($xformTable['table_name']);
                $this->tables[$tableName] = [
                    'r5Table' => $tableName,
                    'isChangeable' => 0,
                ];
            }
        }
    }

    public function transferToR5($tables)
    {
        global $REX;
        if (isset($REX['DB']['5'])) {
            $r5Tables = $this->getR5Tables();

            $tables = array_intersect($tables, $r5Tables);

            if (count($tables)) {
                foreach ($tables as $table) {
                    $r5Table = str_replace($this->getTablePrefix(), '', $table);
                    $sql5 = \rex_sql::factory(5);
                    //$sql5->debugsql = 1;
                    $sql5->setQuery('CREATE TABLE IF NOT EXISTS `' . $r5Table . '` ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

                }
            }
        }
        parent::transferToR5($tables);
    }

    public static function callbackModifyGoogleGeocodeInTables($params)
    {
        // alle Tabellen mit google_geocode anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);

        $query = sprintf('SELECT `table_name`, `name`, `position`  FROM %s WHERE `type_name` = "google_geocode" AND `type_id` = "value"', $r5Table);
        $tables = $converter->db->getArray($query);
        if (count($tables)) {
            foreach ($tables as $table) {
                $positionFields = explode(',', $table['position']);
                $latField = trim($positionFields[0]);
                $lngField = trim($positionFields[1]);
                $tableName = $converter->getR5Table($converter->removeR4TablePrefix($table['table_name']));
                $converter->db->setQuery('UPDATE `' . $tableName . '` SET `' . $table['name'] . '` = CONCAT(`' . $latField . '`, ",", `' . $lngField . '`)');
                $converter->dropTableColumns($tableName, [$latField, $lngField]);
            }
        }
    }

    public static function callbackModifyLangTextareaInTables($params)
    {
        // alle Tabellen mit google_geocode anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);

        $query = sprintf('SELECT `id`, `table_name`, `list_hidden`, `search`, `name`, `label`  FROM %s WHERE `type_name` = "lang_textarea" AND `type_id` = "value"', $r5Table);
        $tables = $converter->db->getArray($query);
        if (count($tables)) {
            foreach ($tables as $table) {
                $clangs = $converter->db->getArray('SELECT `id`, `name` FROM ' . $converter->getR4Table('clang'));
                $tableName = $converter->getR5Table($converter->removeR4TablePrefix($table['table_name']));
                $column = $table['name'];
                $modifyResults = $converter->db->getArray('SELECT `id`, `'.$column.'` FROM ' . $tableName);

                $valueParts = [];
                if (count($modifyResults)) {
                    foreach ($modifyResults as $modifyResult) {
                        $valueParts[$modifyResult['id']] = explode('^^^^°°°°', $modifyResult[$column]);
                    }
                }

                parent::pr($valueParts);

                foreach ($clangs as $clang) {
                    $clang_id = (int)$clang['id'] + 1;
                    $converter->db->setQuery('ALTER TABLE `' . $tableName .'` ADD COLUMN `' . $column.'_'.$clang_id . '` text');

                    $sqlInsert = \rex_sql::factory();
                    $sqlInsert->debugsql = 0;
                    $sqlInsert->setTable($r5Table);
                    $sqlInsert->setValue('table_name', $table['table_name']);
                    $sqlInsert->setValue('type_id', 'value');
                    $sqlInsert->setValue('type_name', 'textarea');
                    $sqlInsert->setValue('list_hidden', $table['list_hidden']);
                    $sqlInsert->setValue('search', $table['search']);
                    $sqlInsert->setValue('name', $column.'_'.$clang_id);
                    $sqlInsert->setValue('label', $table['label'].' ['.$clang['name'].']');
                    $sqlInsert->insert();
                }

                foreach ($valueParts as $id => $valuePart) {
                    foreach ($valuePart as $oldClangId => $input) {
                        $clang_id = (int)$oldClangId + 1;
                        $converter->db->setQuery('UPDATE `' . $tableName . '` SET `' . $column.'_'.$clang_id . '` = \'' . $converter->db->escape($input) . '\' WHERE id = "'.$id.'"');
                    }
                }
                $converter->dropTableColumns($tableName, [$column]);
                $converter->db->setQuery('DELETE FROM `' . $r5Table . '` WHERE `id` = "'.$table['id'].'"');
            }
        }
    }

    public static function callbackChangeFields($params)
    {
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);

        // Actions aus der rex_yform_field löschen
        // - - - - - - - - - - - - - - - - - - - -
        $values = $params['removeActions'];
        if (count($values)) {
            foreach ($values as $value) {
                $converter->db->setQuery('DELETE FROM `' . $r5Table . '` WHERE `type_id` = "action" AND `type_name` = "' . $value . '"');
            }
        }

        // Values aus der rex_yform_field löschen
        // - - - - - - - - - - - - - - - - - - - -
        $values = $params['removeValues'];
        if (count($values)) {
            foreach ($values as $value) {
                $converter->db->setQuery('DELETE FROM `' . $r5Table . '` WHERE `type_id` = "value" AND `type_name` = "' . $value . '"');
            }
        }

        // Values in der rex_yform_field anpassen
        // - - - - - - - - - - - - - - - - - - - -
        $values = $params['convertValues'];
        if (count($values)) {
            foreach ($values as $valueName => $columns) {
                $sets = [];
                foreach ($columns as $columnName => $data) {
                    if (isset($data['value'])) {
                        $sets[] = '`' . $columnName . '` = "' . $data['value'] . '"';
                    }
                    if (isset($data['replaces'])) {
                        foreach ($data['replaces'] as $search => $replace) {
                            $sets[] = '`' . $columnName . '` = REPLACE(`' . $columnName . '`, "' . $search . '", "' . $replace . '")';
                        }
                    }
                }
                if (count($sets)) {
                    $converter->db->setQuery('UPDATE `' . $r5Table . '` SET ' . implode(',', $sets) . ' WHERE `type_id` = "value" AND `type_name` = "' . $valueName . '"');
                }
            }
        }
    }


    public static function callbackCleanFieldTable($params)
    {
        // rex_yform_field anpassen
        // leere Spalten löschen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);
        $checkColumnNames = [];
        foreach ($params['addColumns'] as $column) {
            foreach ($column as $columnName => $type) {
                if (!in_array($columnName, $params['doNotDropColumns'])) {
                    $checkColumnNames[] = $columnName;
                }
            }
        }
        if (count($checkColumnNames)) {
            $dropColumnNames = [];
            foreach ($checkColumnNames as $checkColumnName) {
                $query = sprintf('SELECT * FROM %s WHERE `%s` != ""', $r5Table, $checkColumnName, $checkColumnName);
                $converter->db->setQuery($query);
                if ($converter->db->getRows() < 1) {
                    $dropColumnNames[] = $checkColumnName;
                }
            }
            if (count($checkColumnNames)) {
                $converter->dropTableColumns($r5Table, $dropColumnNames);
            }
        }
    }

    public function getRemovableActions()
    {
        return $this->tables['xform_field']['removeActions'];
    }

    public function getRemovableValues()
    {
        return $this->tables['xform_field']['removeValues'];
    }
}
