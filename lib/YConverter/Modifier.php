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

class Modifier
{
    private const EARLY = -1;
    private const NORMAL = 0;
    private const LATE = 1;

    private $config;
    private $message;
    private $sql;

    private $outdatedCode;
    private $replaces;
    private $tables;

    public function __construct(Config $config, Message $message)
    {
        $this->sql = \rex_sql::factory();
        $this->sql->setDebug(false);

        $this->config = $config;
        $this->message = $message;

        $this->boot();
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function updateTables()
    {
        foreach ($this->tables as $table => $versions) {
            foreach ($versions as $version => $params) {
                if (!$this->isTableForVersion($version)) {
                    continue;
                }

                $r5Table = $this->config->getConverterTable($table);
                $r5TableEscaped = $this->sql->escapeIdentifier($r5Table);

                if (isset($params['renameTable'])) {
                    \rex_sql_table::get($this->config->getConverterTable($params['renameTable']['oldName']))
                        ->setName($r5Table)
                        ->alter();
                    $this->message->addSuccess(sprintf('Tabelle wurde von <code>%s</code> in <code>%s</code> umbenannt', $params['renameTable']['oldName'], $r5Table));
                }

                if (isset($params['renameColumns'])) {
                    $sqlTable = \rex_sql_table::get($r5Table);
                    $messages = [];
                    foreach ($params['renameColumns'] as $column) {
                        $sqlTable->renameColumn($column['oldName'], $column['newName']);
                        $messages[] = sprintf('<code>%s</code> in <code>%s</code>', $column['oldName'], $column['newName']);
                    }
                    $sqlTable->alter();
                    $this->message->addSuccess(sprintf('Folgende Felder der Tabelle <code>%s</code> wurde umbenannt <ul><li>%s</li></ul>', $r5Table, implode('</li><li>', $messages)));
                }

                if (isset($params['ensureColumns'])) {
                    $sqlTable = \rex_sql_table::get($r5Table);
                    $messages = [];
                    foreach ($params['ensureColumns'] as $column) {
                        $sqlTable->ensureColumn(
                            new \rex_sql_column($column['name'], $column['type']),
                            (isset($column['after']) ? $column['after'] : null)
                        );
                        $messages[] = sprintf('<code>%s</code> als <code>%s</code>', $column['name'], $column['type']);
                    }
                    $sqlTable->alter();
                    $this->message->addSuccess(sprintf('Folgende Felder der Tabelle <code>%s</code> wurde neu erstellt <ul><li>%s</li></ul>', $r5Table, implode('</li><li>', $messages)));
                }

                if (isset($params['ensurePrimaryIdColumn']) && $params['ensurePrimaryIdColumn']) {
                    \rex_sql_table::get($r5Table)
                        ->ensurePrimaryIdColumn()
                        ->alter();
                    $this->message->addSuccess(sprintf('Für die Tabelle <code>%s</code> wurde ein Feld <code>id</code> mit <code>auto_incremtent</code> sichergestellt', $r5Table));
                }

                if (isset($params['convertColumns'])) {
                    foreach ($params['convertColumns'] as $column => $convertType) {
                        $columnEscaped = $this->sql->escapeIdentifier($column);
                        switch ($convertType) {
                            case 'replaces':
                                $items = $this->sql->getArray('SELECT `id`, $columnEscaped FROM '.$r5TableEscaped.' WHERE $columnEscaped != ""');
                                if (\count($items)) {
                                    foreach ($items as $item) {
                                        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.$columnEscaped.' = :replacedContent WHERE `id` = :id', ['id' => $item['id'], 'replacedContent' => $this->replaceContent($item[$column])]);
                                    }
                                }
                                $this->message->addSuccess(sprintf('Die Daten des Feldes <code>%s</code> der Tabelle <code>%s</code> wurden konvertiert', $column, $r5Table));
                                $this->checkOutdatetCode($r5Table, $params['fireReplaces']);
                                break;
                            case 'serialize':
                                $items = $this->sql->getArray('SELECT `id`, '.$columnEscaped.' FROM '.$r5TableEscaped.' WHERE '.$columnEscaped.' != ""');
                                if (\count($items)) {
                                    foreach ($items as $item) {
                                        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.$columnEscaped.' = \''.addslashes(json_encode(unserialize($item[$column]))).'\' WHERE `id` = "'.$item['id'].'"');
                                    }
                                }
                                $this->message->addSuccess(sprintf('Die serialisierten Daten des Feldes <code>%s</code> der Tabelle <code>%s</code> wurden konvertiert', $column, $r5Table));
                                break;
                            case 'timestamp':
                                $this->sql->setQuery('ALTER TABLE '.$r5TableEscaped.' CHANGE COLUMN '.$columnEscaped.' '.$columnEscaped.' varchar(20)');
                                $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.$columnEscaped.' = IF('.$columnEscaped.' > 0, FROM_UNIXTIME('.$columnEscaped.', "%Y-%m-%d %H:%i:%s"), NOW())');
                                $this->sql->setQuery('ALTER TABLE '.$r5TableEscaped.' CHANGE COLUMN '.$columnEscaped.' '.$columnEscaped.' datetime');
                                $this->message->addSuccess(sprintf('Die Timestamps des Feldes <code>%s</code> der Tabelle <code>%s</code> wurden konvertiert', $column, $r5Table));
                                break;
                        }
                    }
                }

                if (isset($params['moveContents'])) {
                    foreach ($params['moveContents'] as $column) {
                        $fromEscaped = $this->sql->escapeIdentifier($column['from']);
                        $toEscaped = $this->sql->escapeIdentifier($column['to']);
                        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.$toEscaped.' = IF('.$fromEscaped.' = "", '.$toEscaped.', CONCAT('.$toEscaped.', "\n\n\n", '.$fromEscaped.'))');
                        $this->message->addSuccess(sprintf('Die Daten des Feldes <code>%s</code> der Tabelle <code>%s</code> wurden in das Feld <code>%s</code> übertragen', $column['from'], $r5Table, $column['from']));
                    }
                }

                if (isset($params['dropColumns'])) {
                    $sqlTable = \rex_sql_table::get($r5Table);
                    foreach ($params['dropColumns'] as $column) {
                        $sqlTable->removeColumn($column);
                        $messages[] = sprintf('<code>%s</code>', $column);
                    }
                    $sqlTable->alter();
                    $this->message->addSuccess(sprintf('Folgende Felder der Tabelle <code>%s</code> wurde gelöscht <ul><li>%s</li></ul>', $r5Table, implode('</li><li>', $messages)));
                }
            }
        }
    }

    public function callCallbacks()
    {
        // Callbacks erst nach den Anpassungen durchgehen
        $callbacks = [];
        foreach ($this->tables as $table => $versions) {
            foreach ($versions as $fromVersion => $params) {
                if (\rex_string::versionCompare($this->config->getOutdatedCoreVersion(), $fromVersion, '<')) {
                    continue;
                }
                $r5Table = $this->config->getConverterTable($table);

                if (isset($params['callbacks'])) {
                    foreach ($params['callbacks'] as $callback) {
                        $level = isset($callback[1]) ? $callback[1] : self::NORMAL;
                        $params['r5Table'] = $r5Table;
                        $callbacks[$level][] = ['function' => $callback[0], 'params' => $params];
                    }
                }
            }
        }

        foreach ([self::EARLY, self::NORMAL, self::LATE] as $level) {
            if (isset($callbacks[$level]) && \is_array($callbacks[$level])) {
                foreach ($callbacks[$level] as $callback) {
                    $this->{$callback['function']}($callback['params']);
                    //\call_user_func([$this, $callback['function']], $callback['params']);
                    $this->message->addInfo(('Callback '.$callback['function'].' für '.$callback['params']['r5Table'].' aufgerufen'));
                }
            }
        }
    }

    public function checkMissingColumns()
    {
        foreach ($this->tables as $table => $versions) {
            $missingTables = [];

            $originalTable = \rex::getTable($table);
            $convertTable = $this->config->getConverterTable($table);

            $sqlTable = \rex_sql_table::get($originalTable);
            if (!$sqlTable->exists()) {
                $missingTables[] = $sqlTable->getName();
                continue;
            }


            dump($sqlTable->getColumns());

            $originalColumns = \rex_sql::showColumns($originalTable);
            $convertColumns = \rex_sql::showColumns($convertTable);

            $originalNames = array_column($originalColumns, 'name');
            $convertNames = array_column($convertColumns, 'name');

            $missingColumns = array_diff($convertNames, $originalNames);

            if (count($missingColumns)) {
                $this->message->addWarning(sprintf('Folgende Felder sind der REDAXO 5 Tabelle <code>%s</code> nicht bekannt <ul><li>%s</li></ul>', $originalTable, implode('</li><li>', $missingColumns)));
            }
        }
    }

    private function boot()
    {
        $this->outdatedCode = [
            [
                // $REX
                'regex' => '(\$REX\s*\[[\'\"]$$SEARCH$$[\'\"]\])',
                'matches' => [
                    //'MOD_REWRITE',
                    '.*?',
                ],
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
                ],
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
                ],
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
                ],
            ], [
                // OOF
                'regex' => '$$SEARCH$$\s*::\s*([a-zA-Z]+)\((.*?)\)',
                'replaces' => [
                    ['OOArticle' => 'rex_article::$1($2)'],
                    ['OOCategory' => 'rex_category::$1($2)'],
                    ['OOMedia' => 'rex_media::$1($2)'],
                    ['OOMediaCategory' => 'rex_media_category::$1($2)'],
                    ['OOArticleSlice' => 'rex_article_slice::$1($2)'],
                ],
            ], [
                // OO isValid
                'regex' => '$$SEARCH$$\s*::\s*isValid\((.*?)\)',
                'replaces' => [
                    ['OOArticle' => '$1 instanceof rex_article'],
                    ['OOCategory' => '$1 instanceof rex_category'],
                    ['OOMedia' => '$1 instanceof rex_media'],
                    ['OOMediaCategory' => '$1 instanceof rex_media_category'],
                ],
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
                    ['INPUT_PHP' => 'REX_INPUT_VALUE['.$this->config->getNewPhpValueField().']'],
                    ['REX_PHP' => 'REX_VALUE[id='.$this->config->getNewPhpValueField().' output=php]'],
                    ['INPUT_HTML' => 'REX_INPUT_VALUE['.$this->config->getNewHtmlValueField().']'],
                    ['REX_HTML' => 'REX_VALUE[id='.$this->config->getNewHtmlValueField().' output=html]'],
                    ['([^_])VALUE\[(id=)?([1-9]+[0-9]*)\]' => '$1REX_INPUT_VALUE[$3]'],
                ],
            ], [
                // Extension Points
                'replaces' => [
                    ['ALL_GENERATED' => 'CACHE_DELETED'],
                    ['ADDONS_INCLUDED' => 'PACKAGES_INCLUDED'],
                    ['OUTPUT_FILTER_CACHE' => 'RESPONSE_SHUTDOWN'],
                    ['OOMEDIA_IS_IN_USE' => 'MEDIA_IS_IN_USE'],
                ],
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
                ],
            ], [
                // XForm
                'replaces' => [
                    ['db2email' => 'tpl2email'],
                    ['notEmpty' => 'empty'],
                ],
            ], [
                // SEO42
                'replaces' => [
                    ['seo42\s*::\s*getMediaFile\(' => 'rex_url::media('],
                    ['seo42\s*::\s*getLangCode\((.*?)\)' => 'rex_clang::getCurrent()->getCode()'],
                    ['seo42\s*::\s*getBaseUrl\((.*?)\)' => 'rex::getServer()'],
                ],
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
                ],
            ],
        ];

        $this->tables = [
            // MetaInfo
            // - - - - - - - - - - - - - - - - - -
            'metainfo_field' => [
                '4.0.0' => [
                    'renameTable' => [
                        'oldName' => '62_params',
                    ],
                    'renameColumns' => [
                        [
                            'oldName' => 'field_id',
                            'newName' => 'id',
                        ], [
                            'oldName' => 'prior',
                            'newName' => 'priority',
                        ], [
                            'oldName' => 'type',
                            'newName' => 'type_id',
                        ],
                    ],
                    'ensureColumns' => [
                        [
                            'name' => 'callback',
                            'type' => 'text',
                            'after' => 'validate',
                        ],
                    ],
                    'ensurePrimaryIdColumn' => true,
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],
            'metainfo_type' => [
                '4.0.0' => [
                    'renameTable' => [
                        'oldName' => '62_type',
                    ],
                    'ensurePrimaryIdColumn' => true,
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyMetainfoTypes',
                        ],
                    ],
                ],
            ],

            // MediaManager
            // - - - - - - - - - - - - - - - - - -
            'media_manager_type' => [
                '4.0.0' => [
                    'renameTable' => [
                        'oldName' => '679_types',
                    ],
                ],
            ],
            'media_manager_type_effect' => [
                '4.0.0' => [
                    'renameTable' => [
                        'oldName' => '679_type_effects',
                    ],
                    'renameColumns' => [
                        [
                            'oldName' => 'prior',
                            'newName' => 'priority',
                        ],
                    ],
                    'convertColumns' => [
                        'parameters' => 'serialize',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],

            // Action
            // - - - - - - - - - - - - - - - - - -
            'action' => [
                '2.7.0' => [
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                        'preview' => 'replace',
                        'presave' => 'replace',
                        'postsave' => 'replace',
                    ],
                ],
            ],

            // Articles
            // - - - - - - - - - - - - - - - - - -
            'article' => [
                '<4.0.0' => [
                    'convertColumns' => [
                        'art_online_from' => 'timestamp',
                        'art_online_to' => 'timestamp',
                    ],
                ],
                '2.7.0' => [
                    'renameColumns' => [
                        [
                            'oldName' => 're_id',
                            'newName' => 'parent_id',
                        ], [
                            'oldName' => 'catprior',
                            'newName' => 'catpriority',
                        ], [
                            'oldName' => 'startpage',
                            'newName' => 'startarticle',
                        ], [
                            'oldName' => 'prior',
                            'newName' => 'priority',
                        ], [
                            'oldName' => 'clang',
                            'newName' => 'clang_id',
                        ],
                    ],
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'dropColumns' => [
                        'attributes',
                    ],
                    'callbacks' => [
                        ['callbackModifyArticles', self::EARLY],
                    ],
                ],
            ],

            // Article Slices
            // - - - - - - - - - - - - - - - - - -
            'article_slice' => [
                '2.7.0' => [
                    'renameColumns' => [
                        [
                            'oldName' => 'clang',
                            'newName' => 'clang_id',
                        ], [
                            'oldName' => 'ctype',
                            'newName' => 'ctype_id',
                        ], [
                            'oldName' => 're_article_slice_id',
                            'newName' => 'priority',
                        ], [
                            'oldName' => 'modultyp_id',
                            'newName' => 'module_id',
                        ], [
                            'oldName' => 'file1', 'newName' => 'media1', ], [
                            'oldName' => 'file2', 'newName' => 'media2', ], [
                            'oldName' => 'file3', 'newName' => 'media3', ], [
                            'oldName' => 'file4', 'newName' => 'media4', ], [
                            'oldName' => 'file5', 'newName' => 'media5', ], [
                            'oldName' => 'file6', 'newName' => 'media6', ], [
                            'oldName' => 'file7', 'newName' => 'media7', ], [
                            'oldName' => 'file8', 'newName' => 'media8', ], [
                            'oldName' => 'file9', 'newName' => 'media9', ], [
                            'oldName' => 'file10', 'newName' => 'media10', ], [
                            'oldName' => 'filelist1', 'newName' => 'medialist1', ], [
                            'oldName' => 'filelist2', 'newName' => 'medialist2', ], [
                            'oldName' => 'filelist3', 'newName' => 'medialist3', ], [
                            'oldName' => 'filelist4', 'newName' => 'medialist4', ], [
                            'oldName' => 'filelist5', 'newName' => 'medialist5', ], [
                            'oldName' => 'filelist6', 'newName' => 'medialist6', ], [
                            'oldName' => 'filelist7', 'newName' => 'medialist7', ], [
                            'oldName' => 'filelist8', 'newName' => 'medialist8', ], [
                            'oldName' => 'filelist9', 'newName' => 'medialist9', ], [
                            'oldName' => 'filelist10', 'newName' => 'medialist10',
                        ],
                    ],
                    'convertColumns' => [
                        'value1' => 'replace',
                        'value2' => 'replace',
                        'value3' => 'replace',
                        'value4' => 'replace',
                        'value5' => 'replace',
                        'value6' => 'replace',
                        'value7' => 'replace',
                        'value8' => 'replace',
                        'value9' => 'replace',
                        'value10' => 'replace',
                        'value11' => 'replace',
                        'value12' => 'replace',
                        'value13' => 'replace',
                        'value14' => 'replace',
                        'value15' => 'replace',
                        'value16' => 'replace',
                        'value17' => 'replace',
                        'value18' => 'replace',
                        'value19' => 'replace',
                        'value20' => 'replace',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'moveContents' => [
                        [
                            'from' => 'php',
                            'to' => 'value'.$this->config->getNewPhpValueField(),
                        ], [
                            'from' => 'html',
                            'to' => 'value'.$this->config->getNewHtmlValueField(),
                        ],
                    ],
                    'dropColumns' => [
                        'next_article_slice_id',
                        'php',
                        'html',
                    ],
                    'callbacks' => [
                        ['callbackModifyArticleSlices', self::LATE],
                    ],
                ],
            ],

            // Clang
            // - - - - - - - - - - - - - - - - - -
            'clang' => [
                '2.7.0' => [
                    'ensureColumns' => [
                        [
                            'name' => 'code',
                            'type' => 'varchar(255)',
                            'after' => 'id',
                        ], [
                            'name' => 'priority',
                            'type' => 'int(10)',
                            'after' => 'name',
                        ], [
                            'name' => 'status',
                            'type' => 'tinyint(1)',
                            'after' => 'revision',
                        ],
                    ],
                    'ensurePrimaryIdColumn' => false,
                    'callbacks' => [
                        ['callbackModifyLanguages', self::EARLY],
                    ],
                ],
            ],

            // Media
            // - - - - - - - - - - - - - - - - - -
            'media' => [
                '2.7.0' => [
                    'renameTable' => [
                        'oldName' => 'file',
                    ],
                    'renameColumns' => [
                        [
                            'oldName' => 'file_id',
                            'newName' => 'id',
                        ],
                    ],
                    'ensurePrimaryIdColumn' => true,
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'dropColumns' => [
                        're_file_id',
                    ],
                ],
            ],

            'media_category' => [
                '2.7.0' => [
                    'renameTable' => [
                        'oldName' => 'file_category',
                    ],
                    'renameColumns' => [
                        [
                            'oldName' => 're_id',
                            'newName' => 'parent_id',
                        ],
                    ],
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],

            // Module
            // - - - - - - - - - - - - - - - - - -
            'module' => [
                '2.7.0' => [
                    'renameColumns' => [
                        [
                            'oldName' => 'ausgabe',
                            'newName' => 'output',
                        ], [
                            'oldName' => 'eingabe',
                            'newName' => 'input',
                        ],
                    ],
                    'ensureColumns' => [
                        [
                            'name' => 'input',
                            'type' => 'mediumtext',
                        ], [
                            'name' => 'output',
                            'type' => 'mediumtext',
                        ],
                    ],
                    'convertColumns' => [
                        'input' => 'replace',
                        'output' => 'replace',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'dropColumns' => [
                        'category_id',
                    ],
                ],
            ],
            'module_action' => [
                '2.7.0' => [],
            ],

            // Templates
            // - - - - - - - - - - - - - - - - - -
            'template' => [
                '2.7.0' => [
                    'ensureColumns' => [
                        [
                            'name' => 'content',
                            'type' => 'mediumtext',
                        ],
                    ],
                    'convertColumns' => [
                        'attributes' => 'serialize',
                        'content' => 'replace',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'dropColumns' => [
                        'label',
                    ],
                ],
            ],
        ];
    }

    private function isTableForVersion($tableVersion)
    {
        $coomparator = '>';
        $length = strcspn($tableVersion, '0123456789.');
        if ($length > 0) {
            $coomparator = substr($tableVersion, 0, $length);
            $tableVersion = substr($tableVersion, $length);
        }
        if (!\rex_string::versionCompare($this->config->getOutdatedCoreVersion(), $tableVersion, $coomparator)) {
            return false;
        }
        return true;
    }

    private function checkOutdatetCode($table, $column)
    {
        $items = $this->sql->getArray('SELECT `id`, `'.$column.'` FROM `'.$table.'` WHERE `'.$column.'` != ""');
        if (\count($items)) {
            foreach ($items as $item) {
                foreach ($this->outdatedCode as $m) {
                    $search = '';
                    if (isset($m['regex'])) {
                        $search = $m['regex'];
                    }

                    foreach ($m['matches'] as $match) {
                        $expr = $match;
                        if ($search != '') {
                            $expr = str_replace('$$SEARCH$$', $match, $search);
                        }
                        if (preg_match('@'.$expr.'@i', $item[$column])) {
                            preg_match_all('@'.$expr.'@i', $item[$column], $matches);
                            $matches = array_count_values($matches[0]);
                            foreach ($matches as $match => $count) {
                                $this->message->addWarning('
                                    <code>'.$match.'</code> sollte angepasst bzw. nicht mehr verwendet werden.<br /><br />
                                    <dl class="dl-horizontal">
                                        <dt>Tabelle</dt>
                                        <dd><code>'.$table.'</code></dd>
                                        <dt>Id</dt>
                                        <dd><code>'.$item['id'].'</code></dd>
                                        <dt>Feld</dt>
                                        <dd><code>'.$column.'</code></dd>
                                        <dt>Vorkommen</dt>
                                        <dd><code>'.$count.'</code></dd>
                                    </dl>
                                ');
                            }
                        }
                    }
                }
            }
        }
    }

    private function replaceContent($content)
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
                    $content = preg_replace('@'.$expr.'@i', $replace, $content);
                }
            }
        }
        return $content;
    }

    private function callbackModifyArticles($params)
    {
        // rex_article anpassen
        $r5TableEscaped = $this->sql->escapeIdentifier($params['r5Table']);
        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');
    }

    private function callbackModifyArticleSlices($params)
    {
        // Sprachen anpassen
        $r5TableEscaped = $this->sql->escapeIdentifier($params['r5Table']);
        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');

        // Revisionen berücksichtigen
        $revision = $this->sql->getArray('SELECT MAX(`revision`) AS revision_max FROM '.$r5TableEscaped);
        $revision_max = isset($revision[0]['revision_max']) ? $revision[0]['revision_max'] : 0;

        // Prioritäten setzen
        $clangs = $this->sql->getArray('SELECT `id` FROM '.$this->config->getConverterTable('clang'));

        foreach ($clangs as $clang) {
            $clang_id = $clang['id'];
            $articles = $this->sql->getArray('SELECT `id` FROM '.$this->config->getConverterTable('article').' WHERE `clang_id` = :clang_id', ['clang_id' => $clang_id]);

            if ($this->sql->getRows() >= 1) {
                foreach ($articles as $article) {
                    for ($revision = 0; $revision <= $revision_max; ++$revision) {
                        $article_id = $article['id'];
                        //$article_clang_id = $clang_id - 1;
                        $slices = $this->getSortedSlices($article_id, $clang_id, $revision);
                        if (\count($slices)) {
                            $priorities = [];
                            foreach ($slices as $slice) {
                                $priority = isset($priorities[$slice['ctype_id']]) ? $priorities[$slice['ctype_id']] + 1 : 1;
                                $priorities[$slice['ctype_id']] = $priority;
                                $slice_id = $slice['id'];
                                $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `priority` = :priority WHERE `id` = :sliceId', ['priority' => $priority, 'sliceId' => $slice_id]);
                            }
                        }
                    }
                }
            }
        }

        // serialisierte Daten prüfen und umwandeln
        $modulesRexVar = $this->sql->getArray('SELECT `id` FROM '.$this->config->getConverterTable('module').' WHERE `output` LIKE "%rex_var::toArray%"');
        $modulesArray = $this->sql->getArray('SELECT `id` FROM '.$this->config->getConverterTable('module').' WHERE `input` REGEXP ".*VALUE\\\[.*\\\]\s*\\\["');
        $modules = array_merge($modulesArray, $modulesRexVar);
        if (\count($modules)) {
            $module_ids = [];
            foreach ($modules as $module) {
                if (!isset($module_ids[$module['id']])) {
                    $module_ids[$module['id']] = 'module_id = "'.$module['id'].'"';
                }
            }
            $slices = $this->sql->getArray('SELECT `id`, `value1`, `value2`, `value3`, `value4`, `value5`, `value6`, `value7`, `value8`, `value9`, `value10`, `value11`, `value12`, `value13`, `value14`, `value15`, `value16`, `value17`, `value18`, `value19`, `value20` FROM '.$r5TableEscaped.' WHERE '.implode(' OR ', $module_ids));
            foreach ($slices as $slice) {
                $sets = [];
                for ($i = 1; $i <= 20; ++$i) {
                    $column = 'value'.$i;
                    // Notices bei unserialize vermeiden
                    if (preg_match('@^a:\d+:{.*?}$@', $slice[$column])) {
                        $value = \rex_var::toArray($slice[$column]);
                        if (\is_array($value)) {
                            $sets[] = $this->sql->escapeIdentifier($column).'` = \''.addslashes(json_encode($value)).'\'';
                        }
                    }
                }
                if (\count($sets)) {
                    $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.implode(', ', $sets).' WHERE `id` = :sliceId', ['sliceId' => $slice['id']]);
                }
            }
        }
    }

    private function callbackModifyLanguages($params)
    {
        // rex_clang anpassen
        $r5TableEscaped = $this->sql->escapeIdentifier($params['r5Table']);
        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `id` = id +1 ORDER BY id DESC');
        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `priority` = `id`');
        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `status` = 1');
    }

    private function callbackModifyMetainfoTypes($params)
    {
        // rex_metainfo_type anpassen
        $r5TableEscaped = $this->sql->escapeIdentifier($params['r5Table']);
        $this->sql->setQuery('UPDATE '.$r5TableEscaped.' SET `label` = REPLACE(`label`, "_BUTTON", "_WIDGET")');
    }

    private function getSortedSlices($articleId, $clangId, $revision)
    {
        $items = $this->sql->getArray('
            SELECT  `id`, 
                    `priority`, 
                    `ctype_id` 
            FROM    '.$this->sql->escapeIdentifier($this->config->getConverterTable('article_slice')).'
            WHERE   article_id = :articleId
                AND clang_id = :clangId
                AND revision = :revision
            ', ['articleId' => $articleId, 'clangId' => $clangId, 'revision' => $revision]);

        $slices = [];
        if (\count($items)) {
            $sliceMap = [];
            $sliceRefMap = [];
            foreach ($items as $slice) {
                $sliceMap[$slice['id']] = $slice;
                $sliceRefMap[$slice['priority']] = $slice['id'];
            }
            $nextSlice = $sliceMap[$sliceRefMap[0]];
            while ($nextSlice) {
                $slices[] = $nextSlice;
                if (!isset($sliceRefMap[$nextSlice['id']])) {
                    break;
                }
                $nextSlice = $sliceMap[$sliceRefMap[$nextSlice['id']]];
            }
        }
        return $slices;
    }
}
