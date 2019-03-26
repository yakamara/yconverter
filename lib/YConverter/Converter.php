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

class Converter
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
            [
                // $REX
                'regex' => '(\$REX\s*\[[\'\"]$$SEARCH$$[\'\"]\])',
                'matches' => [
                    //'MOD_REWRITE',
                    '.*?',
                ]
            ], [
                'matches' => [
                    'getCategoryByName\(',
                    'rex_addslashes',
                    'rex_call_func',
                    'rex_check_callable',
                    'rex_create_lang',
                    'rex_getAttributes',
                    'rex_lang_is_utf8',
                    'rex_replace_dynamic_contents',
                    'rex_setAttributes',
                    'rex_tabindex',
                ]
            ],
        ];
        $this->replaces = [
            [
                // $REX
                'regex' => '\$REX\s*\[[\'\"]$$SEARCH$$[\'\"]\]([^\[])',
                'replaces' => [
                    ['ARTICLE_ID' => 'rex_article::getCurrentId()$1'],
                    ['CLANG' => 'rex_clang::getAll()$1'],
                    ['CUR_CLANG' => 'rex_clang::getCurrentId()$1'],
                    ['ERROR_EMAIL' => 'rex::getErrorEmail()$1'],
                    ['FRONTEND_FILE' => 'rex_path::frontendController()$1'],
                    ['FRONTEND_PATH' => 'rex_path::frontend()$1'],
                    ['HTDOCS_PATH' => 'rex_path::frontend()$1'],
                    ['INCLUDE_PATH' => 'rex_path::src() . \'/addons/\'$1'],
                    ['MEDIAFOLDER' => 'rex_path::media()$1'],
                    ['NOTFOUND_ARTICLE_ID' => 'rex_article::getNotFoundArticleId()$1'],
                    ['REDAXO' => 'rex::isBackend()$1'],
                    ['SERVER' => 'rex::getServer()$1'],
                    ['SERVERNAME' => 'rex::getServerName()$1'],
                    ['START_ARTICLE_ID' => 'rex_article::getSiteStartArticleId()$1'],
                    ['TABLE_PREFIX' => 'rex::getTablePrefix()$1'],
                    ['USER' => 'rex::getUser()$1'],
                ]
            ], [
                // OOF Spezial
                'replaces' => [
                    ['new\s*rex_article' => 'new rex_article_content'],
                    ['new\s*article' => 'new rex_article_content'],
                    ['OOArticle\s*::\s*getArticleById\(' => 'rex_article::get('],
                    ['OOCategory\s*::\s*getCategoryById\(' => 'rex_category::get('],
                    ['OOCategory\s*::\s*getChildrenById\((.*?),\s*(.*?)\)' => 'rex_category::get($1)->getChildren($2)'],
                    ['OOMedia\s*::\s*getMediaByFilename\(' => 'rex_media::get('],
                    ['OOMedia\s*::\s*getMediaByName\(' => 'rex_media::get('],
                    ['OOMediaCategory\s*::\s*getCategoryById\(' => 'rex_media_category::get('],
                    ['OOAddon\s*::\s*isActivated\((.*?)\)' => 'rex_addon::get($1)->isActivated()'],
                    ['OOAddon\s*::\s*isAvailable\((.*?)\)' => 'rex_addon::get($1)->isAvailable()'],
                    ['OOAddon\s*::\s*isInstalled\((.*?)\)' => 'rex_addon::get($1)->isInstalled()'],
                    ['OOAddon\s*::\s*getProperty\((.*?),\s*(.*?)\)' => 'rex_addon::get($1)->getProperty($2)'],
                    ['OOPlugin\s*::\s*isActivated\((.*?),\s*(.*?)\)' => 'rex_plugin::get($1, $2)->isActivated()'],
                    ['OOPlugin\s*::\s*isAvailable\((.*?),\s*(.*?)\)' => 'rex_plugin::get($1, $2)->isAvailable()'],
                    ['OOPlugin\s*::\s*isInstalled\((.*?),\s*(.*?)\)' => 'rex_plugin::get($1, $2)->isInstalled()'],
                    ['OOPlugin\s*::\s*getProperty\((.*?),\s*(.*?),\s*(.*?)\)' => 'rex_plugin::get($1, $2)->getProperty($3)'],

                ]
            ], [
                // OOF
                'regex' => '$$SEARCH$$\s*::\s*([a-zA-Z]+)\((.*?)\)',
                'replaces' => [
                    ['OOArticle' => 'rex_article::$1($2)'],
                    ['OOCategory' => 'rex_category::$1($2)'],
                    ['OOMedia' => 'rex_media::$1($2)'],
                    ['OOMediaCategory' => 'rex_media_category::$1($2)'],
                    ['OOArticleSlice' => 'rex_article_slice::$1($2)'],
                ]
            ], [
                // OO isValid
                'regex' => '$$SEARCH$$\s*::\s*isValid\((.*?)\)',
                'replaces' => [
                    ['OOArticle' => '$1 instanceof rex_article'],
                    ['OOCategory' => '$1 instanceof rex_category'],
                    ['OOMedia' => '$1 instanceof rex_media'],
                    ['OOMediaCategory' => '$1 instanceof rex_media_category'],
                ]
            ], [
                // REX_
                'replaces' => [
                    ['REX_EXTENSION_EARLY' => 'rex_extension::EARLY'],
                    ['REX_EXTENSION_LATE' => 'rex_extension::LATE'],
                    ['REX_FILE\[([1-9]+)\]' => 'REX_MEDIA[id=$1]'],
                    ['REX_HTML_VALUE\[([0-9]+)\]' => 'REX_VALUE[id=$1 output=html]'],
                    ['REX_HTML_VALUE\[id=[\"\']([0-9]+)(.*?)\]' => 'REX_VALUE[id=$1 output=html $2]'],
                    ['REX_IS_VALUE\[([1-9]+)\]' => 'REX_VALUE[id=$1 isset=1]'],
                    ['REX_LINK_BUTTON\[(id=)?([1-9]|10)(.*?)\]' => 'REX_LINK[id=$2 widget=1$3]'],
                    ['REX_LINK_ID\[(id=)?([1-9]|10)(.*?)\]' => 'REX_LINK[id=$2 output=id$3]'],
                    ['REX_LINK\[(id=)?([1-9]|10)(.*?)\]' => 'REX_LINK[id=$2 output=url$3]'],
                    ['REX_LINKLIST_BUTTON\[(id=)?([1-9]|10)(.*?)\]' => 'REX_LINKLIST[id=$2 widget=1$3]'],
                    ['REX_MEDIA_BUTTON\[(id=)?([1-9]|10)(.*?)\]' => 'REX_MEDIA[id=$2 widget=1$3]'],
                    ['REX_MEDIALIST_BUTTON\[(id=)?([1-9]|10)(.*?)\]' => 'REX_MEDIALIST[id=$2 widget=1$3]'],
                    // muss hier stehen
                    ['INPUT_PHP' => 'REX_INPUT_VALUE[' . self::$phpValueField . ']'],
                    ['REX_PHP' => 'REX_VALUE[id=' . self::$phpValueField . ' output=php]'],
                    ['INPUT_HTML' => 'REX_INPUT_VALUE[' . self::$htmlValueField . ']'],
                    ['REX_HTML' => 'REX_VALUE[id=' . self::$htmlValueField . ' output=html]'],
                    ['([^_])VALUE\[(id=)?([1-9]+[0-9]*)\]' => '$1REX_INPUT_VALUE[$3]'],
                ]
            ], [
                // Extension Points
                'replaces' => [
                    ['ALL_GENERATED' => 'CACHE_DELETED'],
                    ['ADDONS_INCLUDED' => 'PACKAGES_INCLUDED'],
                    ['OUTPUT_FILTER_CACHE' => 'RESPONSE_SHUTDOWN'],
                    ['OOMEDIA_IS_IN_USE' => 'MEDIA_IS_IN_USE'],
                ]
            ], [
                // Core Rest
                'replaces' => [
                    ['\$I18N\-\>msg\(' => 'rex_i18n::msg('],
                    ['(\/?)files\/' => '$1media/'],
                    ['\-\>countFiles\(' => '->countMedia('],
                    ['getDescription\(' => 'getValue(\'description\''],
                    ['\-\>getFiles\(' => '->getMedia('],
                    ['\-\>hasFiles\(' => '->hasMedia('],
                    ['isStartPage\(' => 'isStartArticle('],
                    ['rex_absPath\(' => 'rex_path::absolute('],
                    ['rex_copyDir\(' => 'rex_dir::copy('],
                    ['rex_createDir\(' => 'rex_dir::create('],
                    ['rex_deleteAll\(' => 'rex_deleteCache('],
                    ['rex_deleteDir\(' => 'rex_dir::delete('],
                    ['rex_deleteFiles\(' => 'rex_dir::deleteFiles('],
                    ['rex_generateAll\(' => 'rex_deleteCache('],
                    ['rex_hasBackendSession\(' => 'rex_backend_login::hasSession('],
                    ['rex_highlight_string\(' => 'rex_string::highlight('],
                    ['rex_highlight_file\(' => 'rex_string::highlight('],
                    ['rex_img_type' => 'rex_media_type'],
                    ['rex_img_file' => 'rex_media_file'],
                    ['rex_info\(' => 'rex_view::info('],
                    ['rex_install_dump\(' => 'rex_sql_util::importDump('],
                    ['rex_organize_priorities\(' => 'rex_sql_util::organizePriorities('],
                    ['rex_parse_article_name\(' => 'rex_string::normalize('],
                    ['rex_register_extension\(' => 'rex_extension::register('],
                    ['rex_register_extension_point\(' => 'rex_extension::registerPoint('],
                    ['rex_send_article\(' => 'rex_response::sendArticle('],
                    ['rex_send_content\(' => 'rex_response::sendContent('],
                    ['rex_send_file\(' => 'rex_response::sendFile('],
                    ['rex_send_resource\(' => 'rex_response::sendResource('],
                    ['rex_split_string' => 'rex_string::split()'],
                    ['rex_title\(' => 'rex_view::title('],
                    ['rex_translate\(' => 'rex_i18n::translate('],
                    ['rex_warning\(' => 'rex_view::error('],
                    ['instanceof\s*OOArticle' => 'instanceof rex_article'],
                    ['instanceof\s*OOCategory' => 'instanceof rex_category'],
                    ['instanceof\s*OOMedia' => 'instanceof rex_media'],
                    ['instanceof\s*OOMediaCategory' => 'instanceof rex_media_category'],
                ]
            ], [
                // XForm
                'replaces' => [
                    ['db2email' => 'tpl2email'],
                    ['notEmpty' => 'empty'],
                ]
            ], [
                // SEO42
                'replaces' => [
                    ['seo42\s*::\s*getMediaFile\(' => 'rex_url::media('],
                    ['seo42\s*::\s*getLangCode\((.*?)\)' => 'rex_clang::getCurrent()->getCode()'],
                    ['seo42\s*::\s*getBaseUrl\((.*?)\)' => 'rex::getServer()'],
                ]
            ], [
                // Community Addon -> YCom
                'replaces' => [
                    ['checkperm\(' => 'checkPerm('],
                    ['rex_addon::get\((["\']{1})community$1\)->isActivated\(\)' => 'rex_addon::get(\'ycom\')->isActivated()'],
                    ['rex_addon::get\((["\']{1})community$1\)->isAvailable\(\)' => 'rex_addon::get(\'ycom\')->isAvailable()'],
                    ['rex_addon::get\((["\']{1})community$1\)->isInstalled\(\)' => 'rex_addon::get(\'ycom\')->isInstalled()'],
                    ['rex_com_auth\s*::' => 'rex_ycom_auth::'],
                    ['com_auth_db' => 'ycom_auth_db'],
                    ['com_auth_form_' => 'ycom_auth_form_'],
                    ['com_auth_load_user' => 'ycom_auth_load_user'],
                    ['com_auth_password_hash\|.*' => ''],
                    ['password\|.*?\|' => 'ycom_auth_password|$1|'],
                ]
            ],
        ];

        $this->tables = [
            // Metainfo
            // - - - - - - - - - - - - - - - - - -
            '62_params' => [
                'r5Table' => 'metainfo_field',
                'autoIncrementColumn' => 'id',
                'isChangeable' => 0,
                'addColumns' => [
                    ['callback' => 'text AFTER validate'],
                ],
                'changeColumns' => [
                    ['field_id' => 'id'],
                    ['prior' => 'priority'],
                    ['type' => 'type_id'],
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'callbacks' => [
                    ['YConverter\Converter::callbackSetAutoIncrement'],
                ],
            ],

            '62_type' => [
                'r5Table' => 'metainfo_type',
                'autoIncrementColumn' => 'id',
                'isChangeable' => 0,
                'callbacks' => [
                    ['YConverter\Converter::callbackModifyMetainfoTypes'],
                    ['YConverter\Converter::callbackSetAutoIncrement'],
                ],

            ],

            // Image Manager
            // - - - - - - - - - - - - - - - - - -
            '679_types' => [
                'r5Table' => 'media_manager_type',
                'isChangeable' => 0,
            ],

            '679_type_effects' => [
                'r5Table' => 'media_manager_type_effect',
                'isChangeable' => 0,
                'changeColumns' => [
                    ['prior' => 'priority'],
                ],
                'convertSerialize' => [
                    'parameters',
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ]
            ],

            // Action
            // - - - - - - - - - - - - - - - - - -
            'action' => [
                'r5Table' => 'action',
                'isChangeable' => 0,
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'fireReplaces' => [
                    'preview', 'presave', 'postsave',
                ],
            ],

            // Articles
            // - - - - - - - - - - - - - - - - - -
            'article' => [
                'r5Table' => 'article',
                'isChangeable' => 1,
                'changeColumns' => [
                    ['re_id' => 'parent_id'],
                    ['catprior' => 'catpriority'],
                    ['startpage' => 'startarticle'],
                    ['prior' => 'priority'],
                    ['clang' => 'clang_id'],
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'dropColumns' => [
                    'attributes',
                ],
                'callbacks' => [
                    ['YConverter\Converter::callbackModifyArticles', self::EARLY],
                ]
            ],

            // Article Slices
            // - - - - - - - - - - - - - - - - - -
            'article_slice' => [
                'r5Table' => 'article_slice',
                'isChangeable' => 1,
                'changeColumns' => [
                    ['clang' => 'clang_id'],
                    ['ctype' => 'ctype_id'],
                    ['re_article_slice_id' => 'priority'],
                    ['file1' => 'media1'],
                    ['file2' => 'media2'],
                    ['file3' => 'media3'],
                    ['file4' => 'media4'],
                    ['file5' => 'media5'],
                    ['file6' => 'media6'],
                    ['file7' => 'media7'],
                    ['file8' => 'media8'],
                    ['file9' => 'media9'],
                    ['file10' => 'media10'],
                    ['filelist1' => 'medialist1'],
                    ['filelist2' => 'medialist2'],
                    ['filelist3' => 'medialist3'],
                    ['filelist4' => 'medialist4'],
                    ['filelist5' => 'medialist5'],
                    ['filelist6' => 'medialist6'],
                    ['filelist7' => 'medialist7'],
                    ['filelist8' => 'medialist8'],
                    ['filelist9' => 'medialist9'],
                    ['filelist10' => 'medialist10'],
                    ['modultyp_id' => 'module_id'],
                ],
                'fireReplaces' => [
                    'value1', 'value2', 'value3', 'value4', 'value5', 'value6', 'value7', 'value8', 'value9', 'value10',
                    'value11', 'value12', 'value13', 'value14', 'value15', 'value16', 'value17', 'value18', 'value19', 'value20',
                ],
                'moveContents' => [
                    ['php' => 'value' . self::$phpValueField],
                    ['html' => 'value' . self::$htmlValueField],
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'dropColumns' => [
                    'next_article_slice_id', 'php', 'html'
                ],
                'callbacks' => [
                    ['YConverter\Converter::callbackModifyArticleSlices', self::LATE],
                ],
            ],

            // Clang
            // - - - - - - - - - - - - - - - - - -
            'clang' => [
                'r5Table' => 'clang',
                'autoIncrementColumn' => 'id',
                'isChangeable' => 0,
                'addColumns' => [
                    ['code' => 'varchar(255) AFTER id'],
                    ['priority' => 'int(10) AFTER name'],
                    ['status' => 'tinyint(1) AFTER revision'],
                ],
                'callbacks' => [
                    ['YConverter\Converter::callbackModifyLanguages', self::EARLY],
                    ['YConverter\Converter::callbackSetAutoIncrement'],
                ]
            ],

            // Media
            // - - - - - - - - - - - - - - - - - -
            'file' => [
                'r5Table' => 'media',
                'autoIncrementColumn' => 'id',
                'isChangeable' => 1,
                'changeColumns' => [
                    ['file_id' => 'id'],
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'dropColumns' => [
                    're_file_id',
                ],
                'callbacks' => [
                    ['YConverter\Converter::callbackSetAutoIncrement'],
                ]
            ],

            'file_category' => [
                'r5Table' => 'media_category',
                'isChangeable' => 1,
                'changeColumns' => [
                    ['re_id' => 'parent_id'],
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ]
            ],

            // Module
            // - - - - - - - - - - - - - - - - - -
            'module' => [
                'r5Table' => 'module',
                'isChangeable' => 0,
                'changeColumns' => [
                    ['ausgabe' => 'output mediumtext'],
                    ['eingabe' => 'input mediumtext'],
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'fireReplaces' => [
                    'input', 'output',
                ],
                'dropColumns' => [
                    'category_id',
                ],
            ],
            'module_action' => [
                'r5Table' => 'module_action',
            ],

            // Templates
            // - - - - - - - - - - - - - - - - - -
            'template' => [
                'r5Table' => 'template',
                'isChangeable' => 0,
                'changeColumns' => [
                    ['content' => 'content mediumtext'],
                ],

                'convertSerialize' => [
                    'attributes',
                ],
                'convertTimestamp' => [
                    'createdate', 'updatedate',
                ],
                'fireReplaces' => [
                    'content',
                ],
                'dropColumns' => [
                    'label',
                ],
            ],

        ];
    }

    public function run()
    {
        $this->createDestinationTables();
        $this->loadDestinationTableStructure();
        $this->modifyDestinationTables();
        $this->callCallbacks();
    }

    public function removeR4TablePrefix($table)
    {
        global $REX;
        if (substr($table, 0, strlen($REX['TABLE_PREFIX'])) == $REX['TABLE_PREFIX']) {
            $table = substr($table, strlen($REX['TABLE_PREFIX']));
        }
        return $table;
    }


    public function getR4Table($table)
    {
        global $REX;
        return $REX['TABLE_PREFIX'] . $table;
    }

    public function getR5Table($table)
    {
        return self::$tablePrefix . $this->getR4Table($table);
    }

    public function getR4Tables()
    {
        $tables = array_keys($this->tables);
        foreach($tables as $index => $table) {
            $tables[$index] = $this->getR4Table($table);
        }
        return $tables;
    }

    public function getR5Tables()
    {
        $tables = [];
        foreach ($this->tables as $r4Table => $params) {
            $tables[] = $this->getR5Table($params['r5Table']);
        }
        return $tables;
    }

    public function getR5ChangeableTables()
    {
        $tables = [];
        foreach ($this->tables as $r4Table => $params) {
            if (isset($params['isChangeable']) && $params['isChangeable']) {
                $tables[] = $this->getR5Table($params['r5Table']);
            }
        }
        return $tables;
    }



    public function getTablePrefix()
    {
        return self::$tablePrefix;
    }

    protected function addMessage($string)
    {
        $this->messages[] = rex_info($string);
    }

    protected function addErrorMessage($string)
    {
        $this->messages[] = rex_warning($string);
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function setTransferType($type)
    {
        if (in_array($type, $this->transferTypes)) {
            $this->transferType = $type;
        }
    }

    public function transferToR5($tables)
    {
        global $REX;
        if (isset($REX['DB']['5'])) {
            $r5Tables = $this->getR5Tables();

            $tables = array_intersect($tables, $r5Tables);

            if (count($tables)) {
                $this->loadDestinationTableStructure();

                foreach ($tables as $table) {
                    $r5Table = str_replace($this->getTablePrefix(), '', $table);
                    $r4ConvertTable = $table;

                    $sql4 = \rex_sql::factory();
                    $items = $sql4->getArray('SELECT * FROM ' . $r4ConvertTable);

                    $sql5 = \rex_sql::factory(5);
                    $sql5->debugsql = 0;
                    //$sql5->setQuery('CREATE TABLE IF NOT EXISTS `' . $r5Table . '`;');
                    $sql5->setQuery('TRUNCATE TABLE `' . $r5Table . '`;');
                    $columns = $sql5->getArray('SHOW COLUMNS FROM `' . $r5Table . '`;');

                    if (count($columns)) {
                        $r4ConvertColumns = $this->tableStructure[$r4ConvertTable];
                        $r5Columns = [];
                        foreach ($columns as $column) {
                            $r5Columns[$column['Field']] = $column;
                        }
                        if (count($r4ConvertColumns) != count($r5Columns)) {
                            foreach ($r4ConvertColumns as $missingColumnName => $missingColumn) {
                                if (!isset($r5Columns[$missingColumnName])) {
                                    $sql5->setQuery('ALTER TABLE `' . $r5Table .'` ADD COLUMN `' . $missingColumnName . '` ' . $missingColumn['Type']);
                                }
                            }
                        }
                    }

                    if (count($items)) {
                        foreach ($items as $item) {
                            $sql5->setTable($r5Table);
                            foreach ($item as $field => $value) {
                                if ($value === null) {
                                    // NULL Werte ueberspringen und nicht via rex_sql setzen
                                    // NULL würde als string in die DB gespeichert werden
                                    // $sql5->setValue($field, 'NULL');
                                } else {
                                    $sql5->setValue($field, $sql5->escape($value));
                                }
                            }
                            $sql5->insert();
                        }
                    }
                }
            }
        }
    }

    protected function createDestinationTables()
    {
        foreach ($this->tables as $r4Table => $params) {
            $r4Table = $this->getR4Table($r4Table);
            $r5Table = $this->getR5Table($params['r5Table']);

            // R5 Tabelle löschen
            $this->db->setQuery('DROP TABLE IF EXISTS `' . $r5Table . '`;');

            // R5 Tabelle erstellen, inkl. der Struktur der R4 Tabelle
            $this->db->setQuery('CREATE TABLE `' . $r5Table . '` LIKE `' . $r4Table . '`;');

            // Daten in R5 Tablle kopieren
            $this->db->setQuery('INSERT ' . $r5Table . ' SELECT * FROM `' . $r4Table . '`;');
        }
        $this->addMessage('Tabellen angelegt und Daten kopiert.');
    }


    protected function loadDestinationTableStructure()
    {
        foreach ($this->tables as $r4Table => $params) {
            $r5Table = $this->getR5Table($params['r5Table']);

            $sql = \rex_sql::factory();
            $columns = $sql->getArray('SHOW COLUMNS FROM `' . $r5Table . '`;');
            foreach ($columns as $column) {
                $this->tableStructure[$r5Table][$column['Field']] = $column;
            }
        }
    }

    protected function getTableColumnType($table, $column)
    {
        return $this->tableStructure[$table][$column]['Type'];
    }

    protected function getFieldsForMessage($fields)
    {
        $return = [];
        foreach ($fields as $field) {
            if (is_array($field)) {
                $return = array_merge($return, array_keys($field));
            } else {
                $return[] = $field;
            }
        }
        return $return;
    }

    protected function modifyDestinationTables()
    {
        foreach ($this->tables as $r4Table => $params) {
            $r5Table = $this->getR5Table($params['r5Table']);

            $messages = [];
            if (isset($params['addColumns'])) {
                $this->addTableColumns($r5Table, $params['addColumns']);
                $messages[] = 'Angelegte Felder:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['addColumns'])) . '</b>';
            }
            if (isset($params['changeColumns'])) {
                $this->changeTableColumns($r5Table, $params['changeColumns']);
                $messages[] = 'Angepasste Felder:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['changeColumns'])) . '</b>';
            }
            if (isset($params['moveContents'])) {
                $this->moveTableContents($r5Table, $params['moveContents']);
                $messages[] = 'Inhalte übertragen:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['moveContents'])) . '</b>';
            }
            if (isset($params['convertSerialize'])) {
                $this->convertTableContents('serializeToJson', $r5Table, $params['convertSerialize']);
                $messages[] = 'Serialisierte Daten konvertiert:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['convertSerialize'])) . '</b>';
            }
            if (isset($params['convertTimestamp'])) {
                $this->convertTableContents('timestampToDatetime', $r5Table, $params['convertTimestamp']);
                $messages[] = 'Timestamps konvertiert:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['convertTimestamp'])) . '</b>';
            }
            if (isset($params['fireReplaces'])) {
                $this->convertTableContents('fireReplaces', $r5Table, $params['fireReplaces']);
                $messages[] = 'Inhalte konvertiert:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['fireReplaces'])) . '</b>';
                $this->checkMatches($r5Table, $params['fireReplaces']);
            }
            if (isset($params['dropColumns'])) {
                $this->dropTableColumns($r5Table, $params['dropColumns']);
                $messages[] = 'Gelöschte Felder:<br /><b style="color: #000; font-weight: 400;">' . implode(', ', $this->getFieldsForMessage($params['dropColumns'])) . '</b>';
            }
            $this->addMessage('Tabelle:<br /><b style="color: #000;">' . $r5Table . '</b><br /><br />' . implode('<br /><br />', $messages));
        }
    }

    protected function callCallbacks()
    {
        $callbacks = [];
        foreach ($this->tables as $r4Table => $params) {
            $r5Table = $this->getR5Table($params['r5Table']);

            if (isset($params['callbacks'])) {
                foreach ($params['callbacks'] as $callback) {
                    $level = isset($callback[1]) ? $callback[1] : 0;
                    $callbacks[$level][] = ['function' => $callback[0], 'table' => $r5Table, 'params' => $params];
                }
            }
        }
        foreach ([self::EARLY, self::NORMAL, self::LATE] as $level) {
            if (isset($callbacks[$level]) && is_array($callbacks[$level])) {
                foreach ($callbacks[$level] as $callback) {
                    if(is_callable($callback['function'])) {
                        call_user_func($callback['function'], $callback['params']);
                        $this->addMessage('Callback ' . $callback['function'] . ' für ' . $callback['table'] . ' aufgerufen');
                    }
                }
            }
        }
    }

    protected function addTableColumns($table, array $columns)
    {
        foreach ($columns as $column) {
            foreach ($column as $name => $type) {
                if (!isset($this->tableStructure[$table][$name])) {
                    $this->db->setQuery('ALTER TABLE `' . $table .'` ADD COLUMN `' . $name . '` ' . $type);
                }
            }
        }
    }

    protected function changeTableColumns($table, array $columns)
    {
        foreach ($columns as $column) {
            foreach ($column as $oldName => $newName) {
                $type = '';
                if (strpos($newName, ' ') === false) {
                    $type = $this->getTableColumnType($table, $oldName);
                }
                $this->db->setQuery('ALTER TABLE `' . $table .'` CHANGE COLUMN `' . $oldName . '` ' . $newName . ' ' . $type);
            }
        }
    }

    protected function dropTableColumns($table, array $columns)
    {
        foreach ($columns as $column) {
            $this->db->setQuery('ALTER TABLE `' . $table . '` DROP COLUMN `' . $column . '`;');
        }
    }

    protected function moveTableContents($table, array $columns)
    {
        foreach ($columns as $column) {
            foreach ($column as $from => $to) {
                $this->db->setQuery('UPDATE `' . $table . '` SET `' . $to . '` = IF(`' . $from . '` = "", `' . $to . '`, CONCAT(`' . $to . '`, "\n\n\n", `' . $from . '`))');
            }
        }
    }

    protected function convertTableContents($function, $table, array $columns)
    {
        switch ($function) {
            case 'fireReplaces':
                foreach ($columns as $column) {
                    $items = $this->db->getArray('SELECT `id`, `' . $column . '` FROM `' . $table . '` WHERE `' . $column . '` != ""');
                    if (count($items)) {
                        foreach ($items as $item) {
                            $this->db->setQuery('UPDATE `' . $table . '` SET `' . $column . '` = \'' .  $this->db->escape($this->fireReplaces($item[$column])) . '\' WHERE `id` = "' . $item['id'] . '"');
                        }
                    }
                }
                break;
            case 'serializeToJson':
                foreach ($columns as $column) {
                    $items = $this->db->getArray('SELECT `id`, `' . $column . '` FROM `' . $table . '` WHERE `' . $column . '` != ""');
                    if (count($items)) {
                        foreach ($items as $item) {
                            $this->db->setQuery('UPDATE `' . $table . '` SET `' . $column . '` = \'' . addslashes(json_encode(unserialize($item[$column]))) . '\' WHERE `id` = "' . $item['id'] . '"');
                        }
                    }
                }
                break;
            case 'timestampToDatetime':
                foreach ($columns as $column) {
                    $this->db->setQuery('ALTER TABLE `' . $table . '` CHANGE COLUMN `' . $column . '` `' . $column . '` varchar(20)');
                    $this->db->setQuery('UPDATE `' . $table . '` SET `' . $column . '` = IF(`' . $column . '` > 0, FROM_UNIXTIME(`' . $column . '`, "%Y-%m-%d %H:%i:%s"), NOW())');
                    $this->db->setQuery('ALTER TABLE `' . $table . '` CHANGE COLUMN `' . $column . '` `' . $column . '` datetime');
                }
                break;
        }
    }

    protected function checkMatches($table, array $columns)
    {
        foreach ($columns as $column) {
            $items = $this->db->getArray('SELECT `id`, `' . $column . '` FROM `' . $table . '` WHERE `' . $column . '` != ""');
            if (count($items)) {
                foreach ($items as $item) {

                    foreach ($this->matches as $m) {
                        $search = '';
                        if (isset($m['regex'])) {
                            $search = $m['regex'];
                        }

                        foreach ($m['matches'] as $match) {
                            $expr = $match;
                            if ($search != '') {
                                $expr = str_replace('$$SEARCH$$', $match, $search);
                            }
                            if (preg_match('@' . $expr . '@i', $item[$column])) {
                                preg_match_all('@' . $expr . '@i', $item[$column], $matches);
                                $matches = array_count_values($matches[0]);
                                foreach ($matches as $match => $count) {
                                    $this->addErrorMessage('
                                        <span style="font-weight: 400;"><code>' . $match . '</code> sollte angepasst bzw. nicht mehr verwendet werden.<br /><br />
                                            Tabelle: <b style="color: #000;">' . $table . '</b>' . str_repeat('&nbsp;', 10) . '
                                            Id: <b style="color: #000;">' . $item['id'] . '</b>' . str_repeat('&nbsp;', 10) . '
                                            Spalte: <b style="color: #000;">' . $column . '</b>' . str_repeat('&nbsp;', 10) . '
                                            Vorkommen: <b style="color: #000;">' . $count . '</b>
                                        </span>');
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    public function fireReplaces($content)
    {
        foreach ($this->replaces as $r) {
            $search = '';
            if (isset($r['regex'])) {
                $search = $r['regex'];
            }

            foreach ($r['replaces'] as $pair) {
                foreach ($pair as $expr => $replace) {
                    if ($search != '') {
                        $expr = str_replace('$$SEARCH$$', $expr, $search);
                    }
                    $content = preg_replace('@' . $expr . '@i', $replace, $content);
                }
            }
        }
        return $content;
    }


    public static function callbackModifyArticles($params)
    {
        // rex_article anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);
        $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');
    }


    public static function callbackModifyArticleSlices($params)
    {
        // Sprachen anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);
        $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');

        // Revisionen berücksichtigen
        $revision = $converter->db->getArray('SELECT MAX(`revision`) AS revision_max FROM `' . $r5Table . '`');
        $revision_max = isset($revision[0]['revision_max']) ? $revision[0]['revision_max'] : 0;

        // Prioritäten setzen
        $clangs = $converter->db->getArray('SELECT `id` FROM ' . $converter->getR5Table('clang'));
        foreach ($clangs as $clang) {
            $clang_id = $clang['id'];
            $articles = $converter->db->getArray('SELECT `id` FROM ' . $converter->getR5Table('article') . ' WHERE `clang_id` = "' . $clang_id . '"');

            if ($converter->db->getRows() >= 1) {
                foreach ($articles as $article) {

                    for ($revision = 0; $revision <= $revision_max; $revision++) {
                        $article_id = $article['id'];
                        $article_clang_id = $clang_id - 1;
                        $slices = yconverterGetSortedSlices($article_id, $article_clang_id, 0, $revision);
                        if (count($slices)) {
                            $priorities = [];
                            /* @var $slice \OOArticleSlice */
                            foreach ($slices as $slice) {
                                $priority = isset($priorities[$slice->getCtype()]) ? $priorities[$slice->getCtype()] + 1 : 1;
                                $priorities[$slice->getCtype()] = $priority;
                                $slice_id = $slice->getId();
                                $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `priority` = "' . $priority . '" WHERE `id` = "' . $slice_id . '"');
                            }
                        }
                    }
                }
            }
        }

        // serialisierte Daten prüfen und umwandeln
        $modulesRexVar = $converter->db->getArray('SELECT `id` FROM ' . $converter->getR5Table('module') . ' WHERE `output` LIKE "%rex_var::toArray%"');
        $modulesArray = $converter->db->getArray('SELECT `id` FROM ' . $converter->getR5Table('module') . ' WHERE `input` REGEXP ".*VALUE\\\[.*\\\]\s*\\\["');
        $modules = array_merge($modulesArray, $modulesRexVar);
        if (count($modules)) {
            $module_ids = [];
            foreach ($modules as $module) {
                if (!isset($module_ids[$module['id']])) {
                    $module_ids[$module['id']] = 'module_id = "' . $module['id']. '"';
                }
            }
            $slices = $converter->db->getArray('SELECT `id`, `value1`, `value2`, `value3`, `value4`, `value5`, `value6`, `value7`, `value8`, `value9`, `value10`, `value11`, `value12`, `value13`, `value14`, `value15`, `value16`, `value17`, `value18`, `value19`, `value20` FROM    ' . $r5Table . ' WHERE ' . implode(' OR ', $module_ids));
            foreach ($slices as $slice) {
                $sets = [];
                for ($i = 1; $i <= 20; $i++) {
                    $column = 'value' . $i;
                    // Notices bei unserialize vermeiden
                    if (preg_match('@^a:\d+:{.*?}$@s', $slice[$column])) {
                        $value = \rex_var::toArray($slice[$column]);
                        if (is_array($value)) {
                            $sets[] = '`' . $column . '` = \'' . addslashes(json_encode($value)) . '\'';
                        }
                    }
                }
                if (count($sets)) {
                    $converter->db->setQuery('UPDATE `' . $r5Table . '` SET ' . implode(', ', $sets) . ' WHERE `id` = "' . $slice['id'] . '"');
                }
            }
        }
    }

    public static function callbackModifyLanguages($params)
    {
        // rex_clang anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);
        $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `id` = id +1 ORDER BY id DESC');
        $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `priority` = `id`');
        $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `status` = 1');
    }

    public static function callbackModifyMetainfoTypes($params)
    {
        // rex_metainfo_type anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);
        $converter->db->setQuery('UPDATE `' . $r5Table . '` SET `label` = REPLACE(`label`, "_BUTTON", "_WIDGET")');
    }

    public static function callbackSetAutoIncrement($params)
    {
        // auto_increment anpassen
        $converter = new self();
        $r5Table = $converter->getR5Table($params['r5Table']);
        $converter->db->setQuery('ALTER TABLE `' . $r5Table . '` CHANGE `'.$params['autoIncrementColumn'].'` `'.$params['autoIncrementColumn'].'` INT(11) NOT NULL AUTO_INCREMENT');
    }

    public static function pr($array, $exit = false)
    {
        echo '<pre>'; print_r($array); echo '</pre>';
        if ($exit) {
            exit();
        }
    }

}
