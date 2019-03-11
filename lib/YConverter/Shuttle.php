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

    public function transfer()
    {
        foreach ($this->tables as $table => $versions) {
            foreach ($versions as $fromVersion => $params) {
                if (\rex_string::versionCompare($this->config->getOutdatedCoreVersion(), $fromVersion, '<')) {
                    continue;
                }

                $originalTable = \rex::getTable($table);
                $convertTable = $this->config->getConverterTable($table);

                $originalColumns = \rex_sql::showColumns($originalTable);
                $convertColumns = \rex_sql::showColumns($convertTable);

                $originalNames = array_column($originalColumns, 'name');
                $convertNames = array_column($convertColumns, 'name');

                $missingColumns = array_diff($convertNames, $originalNames);

                if (count($missingColumns)) {

                }
            }
        }

    }
}
