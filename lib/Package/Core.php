<?php

namespace YConcerter\Package;

use YConverter\Config;
use YConverter\Updater;
use YConverter\YConverter;

class Core extends Package
{

    public function getName(): string
    {
        return 'core';
    }

    public function getTables(): array
    {
        return [
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
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyArticles',
                            'level' => YConverter::EARLY]
                        ,
                    ],
                ],
            ],

            // Article Slices
            // - - - - - - - - - - - - - - - - - -
            'article_slice' => [
                '2.7.0' => [
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
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyArticleSlices',
                            'level' => YConverter::LATE
                        ],
                    ],
                ],
            ],

            // Clang
            // - - - - - - - - - - - - - - - - - -
            'clang' => [
                '2.7.0' => [
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyLanguages',
                            'level' => YConverter::EARLY
                        ],
                    ],
                ],
            ],

            // Media
            // - - - - - - - - - - - - - - - - - -
            'media' => [
                '2.7.0' => [
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],

            'media_category' => [
                '2.7.0' => [
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],

            // MediaManager
            // - - - - - - - - - - - - - - - - - -
            'media_manager_type' => [
                // eigentlich 4.0.0 - Tabellen werden vom Updater auch für <4.0.0 angelegt
                '2.7.0' => [
                ],
            ],
            'media_manager_type_effect' => [
                // eigentlich 4.0.0 - Tabellen werden vom Updater auch für <4.0.0 angelegt
                '2.7.0' => [
                    'convertColumns' => [
                        'parameters' => 'serialize',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],

            // MetaInfo
            // - - - - - - - - - - - - - - - - - -
            'metainfo_field' => [
                // eigentlich 4.0.0 - Tabellen werden vom Updater auch für <4.0.0 angelegt
                '2.7.0' => [
                    'convertColumns' => [
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],
            'metainfo_type' => [
                // eigentlich 4.0.0 - Tabellen werden vom Updater auch für <4.0.0 angelegt
                '2.7.0' => [
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyMetainfoTypes',
                        ],
                    ],
                ],
            ],

            // Module
            // - - - - - - - - - - - - - - - - - -
            'module' => [
                '2.7.0' => [
                    'convertColumns' => [
                        'input' => 'replace',
                        'output' => 'replace',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
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
                    'convertColumns' => [
                        'attributes' => 'serialize',
                        'content' => 'replace',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                ],
            ],
        ];
    }

    public function updateTableStructure()
    {
        $config = $this->getConfig();

        if (\rex_string::versionCompare($config->getOutdatedCoreVersion(), '4.0.0', '<')) {
            $this->to400();
        }
        if (\rex_string::versionCompare($config->getOutdatedCoreVersion(), '4.0.1', '<')) {
            $this->to401();
        }
        if (\rex_string::versionCompare($config->getOutdatedCoreVersion(), '4.1.0', '<')) {
            $this->to410();
        }
        if (\rex_string::versionCompare($config->getOutdatedCoreVersion(), '4.2.0', '<')) {
            $this->to420();
        }
        if (\rex_string::versionCompare($config->getOutdatedCoreVersion(), '4.3.0', '<')) {
            $this->to430();
        }
        if (\rex_string::versionCompare($config->getOutdatedCoreVersion(), '4.5.0', '<')) {
            $this->to450();
        }

        $this->to5xx();

    }

    private function to400()
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('action').'` ADD `createuser` VARCHAR(255) NOT NULL, ADD `createdate` INT NOT NULL, ADD `updateuser` VARCHAR(255) NOT NULL, ADD `updatedate` INT NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('action').'` ADD `preview` TEXT NOT NULL, ADD `presave` TEXT NOT NULL, ADD `postsave` TEXT NOT NULL, ADD `previewmode` TINYINT NOT NULL, ADD `presavemode` TINYINT NOT NULL, ADD `postsavemode` TINYINT NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('action').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('action').'` SET `presave` = `action` WHERE `prepost` = "0";');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('action').'` SET `postsave` = `action` WHERE `prepost` = "1";');
        $sql->setQuery('UPDATE `'.$config->getConverterTablePrefix().'action` SET `presavemode` = `sadd` + 2 * `sedit` + 4 * `sdelete` WHERE `prepost` = "0";');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('action').'` SET `postsavemode` = `sadd` + 2 * `sedit` + 4 * `sdelete` WHERE `prepost` = "1";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('action').'` DROP `action`, DROP `prepost`, DROP `sadd`, DROP `sedit`, DROP `sdelete`;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_description` TEXT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_file` VARCHAR(255) NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_keywords` TEXT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_online_from` TEXT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_online_to` TEXT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_teaser` VARCHAR(255) NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `art_type_id` VARCHAR(255) NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `label` VARCHAR(255) NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD `url` TEXT NOT NULL;');

        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_description` = `description`;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_file` = `file`;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_keywords` = `keywords`;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_online_from` = `online_from`;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_online_to` = `online_to`;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_teaser` = "|true|" WHERE `teaser` = "1";');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `art_type_id` = "Standard" WHERE `type_id` = "1";');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `alias`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `cattype`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `description`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `fe_ext`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `fe_group`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `fe_user`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `file`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `keywords`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `online_from`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `online_to`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `teaser`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `type_id`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` CHANGE `attribute` `attributes` TEXT NOT NULL;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` ADD `next_article_slice_id` INT(11);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` CHANGE `clang` `clang` INT(11) NOT NULL, CHANGE `ctype` `ctype` INT(11) NOT NULL, CHANGE `re_article_slice_id` `re_article_slice_id` INT(11) NOT NULL, CHANGE `article_id` `article_id` INT(11) NOT NULL, CHANGE `createdate` `createdate` INT(11) NOT NULL, CHANGE `updatedate` `updatedate` INT(11) NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` CHANGE `link1` `link1` VARCHAR(10) NOT NULL, CHANGE `link2` `link2` VARCHAR(10) NOT NULL, CHANGE `link3` `link3` VARCHAR(10) NOT NULL, CHANGE `link4` `link4` VARCHAR(10) NOT NULL, CHANGE `link5` `link5` VARCHAR(10) NOT NULL, CHANGE `link6` `link6` VARCHAR(10) NOT NULL, CHANGE `link7` `link7` VARCHAR(10) NOT NULL, CHANGE `link8` `link8` VARCHAR(10) NOT NULL, CHANGE `link9` `link9` VARCHAR(10) NOT NULL, CHANGE `link10` `link10` VARCHAR(10) NOT NULL;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article_slice').'` SET `ctype`=`ctype`+1;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('clang').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` ADD `attributes` TEXT NOT NULL AFTER `category_id`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` ADD `med_description` TEXT NULL');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` ADD `med_copyright` TEXT NULL');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('file').'` SET `med_description` = `description`;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('file').'` SET `med_copyright` = `copyright`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` DROP `copyright`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` DROP `description`;');
        // Zeile in keinem update.sql - aus Vergleich beider install.sql 3.x > 4.0.0
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` CHANGE `filetype` `filetype` VARCHAR(255) NULL, CHANGE `filename` `filename` VARCHAR(255) NULL, CHANGE `originalname` `originalname` VARCHAR(255) NULL, CHANGE `filesize` `filesize` VARCHAR(255) NULL, CHANGE `title` `title` VARCHAR(255) NULL, CHANGE `width` `width` INT(11) NULL, CHANGE `height` `height` INT(11) NULL;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file_category').'` ADD `attributes` TEXT NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file_category').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file_category').'` DROP `hide`;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` ADD `attributes` TEXT NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP `bausgabe`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP `func`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP `html_enable`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP `label`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP `perm_category`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('modultyp').'` DROP `php_enable`;');
        $sql->setQuery('RENAME TABLE `'.$config->getConverterTable('modultyp').'` TO `'.$config->getConverterTable('module').'`;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('module_action').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('user').'` ADD `cookiekey` varchar(255);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('user').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('user').'` SET `status`=1;');

        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('template').'` ADD `attributes` TEXT NOT NULL;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('template').'` ADD `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('template').'` DROP `bcontent`;');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('template').'` DROP `date`;');
        // Zeile in keinem update.sql - aus Vergleich beider install.sql 3.x > 4.0.0
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('template').'` CHANGE `label` `label` VARCHAR(255) NULL, CHANGE `name` `name` VARCHAR(255) NULL, CHANGE `content` `content` TEXT NULL, CHANGE `active` `active` TINYINT(1) NULL;');


        // Metainfo installieren
        // ----------------------------------------
        //CREATE TABLE `%TABLE_PREFIX%62_params` (
        //    `field_id` int(10) unsigned NOT NULL auto_increment,
        //    `title` varchar(255) default NULL,
        //    `name` varchar(255) default NULL,
        //    `prior` int(10) unsigned NOT NULL,
        //    `attributes` varchar(255) NOT NULL,
        //    `type` int(10) unsigned default NULL,
        //    `default` varchar(255) NOT NULL,
        //    `params` varchar(255) default NULL,
        //    `validate` varchar(255) default NULL,
        //    `createuser` varchar(255) NOT NULL,
        //    `createdate` int(11) NOT NULL,
        //    `updateuser` varchar(255) NOT NULL,
        //    `updatedate` int(11) NOT NULL,
        //    PRIMARY KEY  (`field_id`),
        //    UNIQUE KEY `name` (`name`)
        //    );


        \rex_sql_table::get($config->getConverterTable('62_params'))
            ->ensureColumn(new \rex_sql_column('field_id', 'int(10) unsigned', false, null, 'auto_increment'), \rex_sql_table::FIRST)
            ->ensureColumn(new \rex_sql_column('title', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('name', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('prior', 'int(10)', false))
            ->ensureColumn(new \rex_sql_column('attributes', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('type', 'int(10)', false))
            ->ensureColumn(new \rex_sql_column('default', 'varchar(255)', false))
            ->ensureColumn(new \rex_sql_column('params', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('validate', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('createuser', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('createdate', 'int(10)', false))
            ->ensureColumn(new \rex_sql_column('updateuser', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('updatedate', 'int(10)', false))
            ->setPrimaryKey('field_id')
            ->ensure();

        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('1','translate:pool_file_description','med_description','1','','2','','','','admin','1189343866','admin','1189344596');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('2','translate:pool_file_copyright','med_copyright','2','','1','','','','admin','1189343877','admin','1189344617');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('3','translate:online_from','art_online_from','1','','10','','','','admin','1189344934','admin','1189344934');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('4','translate:online_to','art_online_to','2','','10','','','','admin','1189344947','admin','1189344947');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('5','translate:description','art_description','3','','2','','','','admin','1189345025','admin','1189345025');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('6','translate:keywords','art_keywords','4','','2','','','','admin','1189345068','admin','1189345068');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('7','translate:metadata_image','art_file','5','','6','','','','admin','1189345109','admin','1189345109');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('8','translate:teaser','art_teaser','6','','5','','','','admin','1189345182','admin','1189345182');
        //INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('9','translate:header_article_type','art_type_id','7','size=1','3','','Standard|Zugriff f�r alle','','admin','1191963797','admin','1191964038');
        $arrayValues = [
            ['field_id' => 1, 'title' => 'translate:pool_file_description', 'name' => 'med_description',    'prior' => '1', 'attributes' => '', 'type' => '2', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189343866', 'updateuser' => 'admin', 'updatedate' => '1189344596'],
            ['field_id' => 2, 'title' => 'translate:pool_file_copyright',   'name' => 'med_copyright',      'prior' => '2', 'attributes' => '', 'type' => '1', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189343877', 'updateuser' => 'admin', 'updatedate' => '1189344617'],
            ['field_id' => 3, 'title' => 'translate:online_from',           'name' => 'art_online_from',    'prior' => '1', 'attributes' => '', 'type' => '10', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189344934', 'updateuser' => 'admin', 'updatedate' => '1189344934'],
            ['field_id' => 4, 'title' => 'translate:online_to',             'name' => 'art_online_to',      'prior' => '2', 'attributes' => '', 'type' => '10', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189344947', 'updateuser' => 'admin', 'updatedate' => '1189344947'],
            ['field_id' => 5, 'title' => 'translate:description',           'name' => 'art_description',    'prior' => '3', 'attributes' => '', 'type' => '2', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189345025', 'updateuser' => 'admin', 'updatedate' => '1189345025'],
            ['field_id' => 6, 'title' => 'translate:keywords',              'name' => 'art_keywords',       'prior' => '4', 'attributes' => '', 'type' => '2', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189345068', 'updateuser' => 'admin', 'updatedate' => '1189345068'],
            ['field_id' => 7, 'title' => 'translate:metadata_image',        'name' => 'art_file',           'prior' => '5', 'attributes' => '', 'type' => '6', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189345109', 'updateuser' => 'admin', 'updatedate' => '1189345109'],
            ['field_id' => 8, 'title' => 'translate:teaser',                'name' => 'art_teaser',         'prior' => '6', 'attributes' => '', 'type' => '5', 'default' => '', 'params' => '', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1189345182', 'updateuser' => 'admin', 'updatedate' => '1189345182'],
            ['field_id' => 9, 'title' => 'translate:header_article_type',   'name' => 'art_type_id',        'prior' => '7', 'attributes' => 'size=1', 'type' => '3', 'default' => '', 'params' => 'Standard|Zugriff für alle', 'validate' => '', 'createuser' => 'admin', 'createdate' => '1191963797', 'updateuser' => 'admin', 'updatedate' => '1191964038'],
        ];
        $sql = \rex_sql::factory();
        foreach ($arrayValues as $values) {
            $sql->setTable($config->getConverterTable('62_params'));
            $sql->setValues($values);
            $sql->insert();
        }


        //CREATE TABLE `%TABLE_PREFIX%62_type` (
        //    `id` int(10) unsigned NOT NULL auto_increment,
        //    `label` varchar(255) default NULL,
        //    `dbtype` varchar(255) NOT NULL,
        //    `dblength` int(11) NOT NULL,
        //    PRIMARY KEY  (`id`)
        //) TYPE=MyISAM ;
        \rex_sql_table::get($config->getConverterTable('62_type'))
            ->ensureColumn(new \rex_sql_column('id', 'int(10)', false, null, 'auto_increment'), \rex_sql_table::FIRST)
            ->ensureColumn(new \rex_sql_column('label', 'varchar(255)', true))
            ->ensureColumn(new \rex_sql_column('dbtype', 'varchar(255)', false))
            ->ensureColumn(new \rex_sql_column('dblength', 'int(11)', false))
            ->setPrimaryKey('id')
            ->ensure();

        //INSERT INTO %TABLE_PREFIX%62_type VALUES (1,  'text', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (2,  'textarea', 'text', 0);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (3,  'select', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (4,  'radio', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (5,  'checkbox', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (10, 'date', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (11, 'datetime', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (6,  'REX_MEDIA_BUTTON', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (7,  'REX_MEDIALIST_BUTTON', 'varchar', 255);
        //INSERT INTO %TABLE_PREFIX%62_type VALUES (8,  'REX_LINK_BUTTON', 'varchar', 255);
        $arrayValues = [
            ['id' => 1, 'label' => 'text', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 2, 'label' => 'textarea', 'dbtype' => 'text', 'dblength' => 0],
            ['id' => 3, 'label' => 'select', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 4, 'label' => 'radio', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 5, 'label' => 'checkbox', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 10, 'label' => 'date', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 11, 'label' => 'datetime', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 6, 'label' => 'REX_MEDIA_BUTTON', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 7, 'label' => 'REX_MEDIALIST_BUTTON', 'dbtype' => 'varchar', 'dblength' => 255],
            ['id' => 8, 'label' => 'REX_LINK_BUTTON', 'dbtype' => 'varchar', 'dblength' => 255],
        ];
        $sql = \rex_sql::factory();
        foreach ($arrayValues as $values) {
            $sql->setTable($config->getConverterTable('62_type'));
            $sql->setValues($values);
            $sql->insert();
        }

        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_online_from` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_online_to` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_description` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_keywords` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_file` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_teaser` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%article` ADD `art_type_id` VARCHAR(255);
        \rex_sql_table::get($config->getConverterTable('article'))
            ->ensureColumn(new \rex_sql_column('art_online_from', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('art_online_to', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('art_description', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('art_keywords', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('art_file', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('art_teaser', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('art_type_id', 'varchar(255)'))
            ->alter();


        //ALTER TABLE `%TABLE_PREFIX%file` ADD `med_description` VARCHAR(255);
        //ALTER TABLE `%TABLE_PREFIX%file` ADD `med_copyright` VARCHAR(255);
        \rex_sql_table::get($config->getConverterTable('file'))
            ->ensureColumn(new \rex_sql_column('med_description', 'varchar(255)'))
            ->ensureColumn(new \rex_sql_column('med_copyright', 'varchar(255)'))
            ->alter();
    }

    private function to401()
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` CHANGE `value1` `value1` text NULL, CHANGE `value2` `value2` text NULL, CHANGE `value3` `value3` text NULL, CHANGE `value4` `value4` text NULL, CHANGE `value5` `value5` text NULL, CHANGE `value6` `value6` text NULL, CHANGE `value7` `value7` text NULL, CHANGE `value8` `value8` text NULL, CHANGE `value9` `value9` text NULL, CHANGE `value10` `value10` text NULL, CHANGE `value11` `value11` text NULL, CHANGE `value12` `value12` text NULL, CHANGE `value13` `value13` text NULL, CHANGE `value14` `value14` text NULL, CHANGE `value15` `value15` text NULL, CHANGE `value16` `value16` text NULL, CHANGE `value17` `value17` text NULL, CHANGE `value18` `value18` text NULL, CHANGE `value19` `value19` text NULL, CHANGE `value20` `value20` text NULL, CHANGE `file1` `file1` varchar(255) NULL, CHANGE `file2` `file2` varchar(255) NULL, CHANGE `file3` `file3` varchar(255) NULL, CHANGE `file4` `file4` varchar(255) NULL, CHANGE `file5` `file5` varchar(255) NULL, CHANGE `file6` `file6` varchar(255) NULL, CHANGE `file7` `file7` varchar(255) NULL, CHANGE `file8` `file8` varchar(255) NULL, CHANGE `file9` `file9` varchar(255) NULL, CHANGE `file10` `file10` varchar(255) NULL, CHANGE `filelist1` `filelist1` text NULL, CHANGE `filelist2` `filelist2` text NULL, CHANGE `filelist3` `filelist3` text NULL, CHANGE `filelist4` `filelist4` text NULL, CHANGE `filelist5` `filelist5` text NULL, CHANGE `filelist6` `filelist6` text NULL, CHANGE `filelist7` `filelist7` text NULL, CHANGE `filelist8` `filelist8` text NULL, CHANGE `filelist9` `filelist9` text NULL, CHANGE `filelist10` `filelist10` text NULL, CHANGE `link1` `link1` varchar(10) NULL, CHANGE `link2` `link2` varchar(10) NULL, CHANGE `link3` `link3` varchar(10) NULL, CHANGE `link4` `link4` varchar(10) NULL, CHANGE `link5` `link5` varchar(10) NULL, CHANGE `link6` `link6` varchar(10) NULL, CHANGE `link7` `link7` varchar(10) NULL, CHANGE `link8` `link8` varchar(10) NULL, CHANGE `link9` `link9` varchar(10) NULL, CHANGE `link10` `link10` varchar(10) NULL, CHANGE `linklist1` `linklist1` text NULL, CHANGE `linklist2` `linklist2` text NULL, CHANGE `linklist3` `linklist3` text NULL, CHANGE `linklist4` `linklist4` text NULL, CHANGE `linklist5` `linklist5` text NULL, CHANGE `linklist6` `linklist6` text NULL, CHANGE `linklist7` `linklist7` text NULL, CHANGE `linklist8` `linklist8` text NULL, CHANGE `linklist9` `linklist9` text NULL, CHANGE `linklist10` `linklist10` text NULL, CHANGE `php` `php` text NULL, CHANGE `html` `html` text NULL;');
    }

    private function to410()
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();
        $sql->setQuery('DROP TABLE `'.$config->getConverterTable('article_type').'`;');
    }

    private function to420()
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('action').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('clang').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file_category').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('module').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('module_action').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('user').'` CHANGE `revision` `revision` INT(11) NOT NULL DEFAULT "0";');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article').'` SET `revision` = 0 WHERE `revision` IS NULL;');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('article_slice').'` SET `revision` = 0 WHERE `revision` IS NULL;');


        // Metainfo aktualisieren
        // ----------------------------------------
        $sql->setQuery('INSERT INTO `'.$config->getConverterTable('62_type').'` (`id`, `label`, `dbtype`, `dblength`) VALUES (12, "legend", "text", 0)');
    }

    private function to430()
    {
        $config = $this->getConfig();

        //$this->sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `label`;');
        //$this->sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` DROP `url`;');
        \rex_sql_table::get($config->getConverterTable('article'))
            ->removeColumn('label')
            ->removeColumn('url')
            ->alter();

        $sql = \rex_sql::factory();
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article').'` ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD UNIQUE INDEX `find_articles` (`id`, `clang`), ADD INDEX `re_id` (`re_id`);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('article_slice').'` ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD INDEX `re_article_slice_id` (`re_article_slice_id`), ADD INDEX `article_id` (`article_id`), ADD INDEX `find_slices` (`clang`, `article_id`);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file').'` ADD INDEX `re_file_id` (`re_file_id`), ADD INDEX `category_id` (`category_id`);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('file_category').'` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `re_id` (`re_id`);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('module').'` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `category_id` (`category_id`);');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('user').'` ADD UNIQUE INDEX `login` (`login`(50));');


        // Metainfo aktualisieren
        // ----------------------------------------
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('62_params').'` ADD `restrictions` TEXT NOT NULL AFTER `validate`');
        $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('62_params').'` CHANGE `validate` `validate` TEXT DEFAULT NULL');
        // unlogisch - Spalte gibt es nicht
        // $sql->setQuery('ALTER TABLE `'.$config->getConverterTable('62_type').'` ADD UNIQUE INDEX `login` (`login`(50))');
        $sql->setQuery('UPDATE `'.$config->getConverterTable('62_type').'` set dbtype="text", dblength="0" where label="REX_MEDIALIST_BUTTON" or label="REX_LINKLIST_BUTTON"');
        $sql->setQuery('INSERT INTO `'.$config->getConverterTable('62_type').'` (`id`, `label`, `dbtype`, `dblength`) VALUES (13, "time", "text", 0)');


        // Media Manager installieren
        // ----------------------------------------

        //DROP TABLE IF EXISTS `%TABLE_PREFIX%679_types`;
        //CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%679_types` (
        //  `id` int(11) NOT NULL AUTO_INCREMENT,
        //  `status` int(11) NOT NULL,
        //  `name` varchar(255) NOT NULL,
        //  `description` varchar(255) NOT NULL,
        //  PRIMARY KEY (`id`),
        //  UNIQUE KEY `name` (`name`)
        //) TYPE=MyISAM;
        \rex_sql_table::get($config->getConverterTable('679_types'))
            ->ensureColumn(new \rex_sql_column('id', 'int(11) unsigned', false, null, 'auto_increment'), \rex_sql_table::FIRST)
            ->ensureColumn(new \rex_sql_column('status', 'int(11)', false))
            ->ensureColumn(new \rex_sql_column('name', 'varchar(255)', false))
            ->ensureColumn(new \rex_sql_column('description', 'varchar(255)', false))
            ->setPrimaryKey('id')
            ->ensure();

        //INSERT INTO `%TABLE_PREFIX%679_types` (`id`, `status`, `name`, `description`) VALUES
        //    (1, 1, 'rex_mediapool_detail', 'Zur Darstellung von Bildern in der Detailansicht im Medienpool'),
        //    (2, 1, 'rex_mediapool_maximized', 'Zur Darstellung von Bildern im Medienpool wenn maximiert'),
        //    (3, 1, 'rex_mediapool_preview', 'Zur Darstellung der Vorschaubilder im Medienpool'),
        //    (4, 1, 'rex_mediabutton_preview', 'Zur Darstellung der Vorschaubilder in REX_MEDIA_BUTTON[]s'),
        //    (5, 1, 'rex_medialistbutton_preview', 'Zur Darstellung der Vorschaubilder in REX_MEDIALIST_BUTTON[]s');
        $arrayValues = [
            ['id' => 1, 'status' => '1', 'name' => 'rex_mediapool_detail',        'description' => 'Zur Darstellung von Bildern in der Detailansicht im Medienpool', ],
            ['id' => 2, 'status' => '1', 'name' => 'rex_mediapool_maximized',     'description' => 'Zur Darstellung von Bildern im Medienpool wenn maximiert', ],
            ['id' => 3, 'status' => '1', 'name' => 'rex_mediapool_preview',       'description' => 'Zur Darstellung der Vorschaubilder im Medienpool', ],
            ['id' => 4, 'status' => '1', 'name' => 'rex_mediabutton_preview',     'description' => 'Zur Darstellung der Vorschaubilder in REX_MEDIA_BUTTON[]s', ],
            ['id' => 5, 'status' => '1', 'name' => 'rex_medialistbutton_preview', 'description' => 'Zur Darstellung der Vorschaubilder in REX_MEDIALIST_BUTTON[]s', ],
        ];
        $sql = \rex_sql::factory();
        foreach ($arrayValues as $values) {
            $sql->setTable($config->getConverterTable('679_types'));
            $sql->setValues($values);
            $sql->insert();
        }

        //DROP TABLE IF EXISTS `%TABLE_PREFIX%679_type_effects`;
        //CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%679_type_effects` (
        //  `id` int(11) NOT NULL AUTO_INCREMENT,
        //  `type_id` int(11) NOT NULL,
        //  `effect` varchar(255) NOT NULL,
        //  `parameters` text NOT NULL,
        //  `prior` int(11) NOT NULL,
        //  `updatedate` int(11) NOT NULL,
        //  `updateuser` varchar(255) NOT NULL,
        //  `createdate` int(11) NOT NULL,
        //  `createuser` varchar(255) NOT NULL,
        //  PRIMARY KEY (`id`)
        //) TYPE=MyISAM;
        \rex_sql_table::get($config->getConverterTable('679_type_effects'))
            ->ensureColumn(new \rex_sql_column('id', 'int(11) unsigned', false, null, 'auto_increment'), \rex_sql_table::FIRST)
            ->ensureColumn(new \rex_sql_column('type_id', 'int(11)', false))
            ->ensureColumn(new \rex_sql_column('effect', 'varchar(255)', false))
            ->ensureColumn(new \rex_sql_column('parameters', 'text', false))
            ->ensureColumn(new \rex_sql_column('prior', 'int(11)', false))
            ->ensureColumn(new \rex_sql_column('updatedate', 'int(11)', false))
            ->ensureColumn(new \rex_sql_column('updateuser', 'varchar(255)', false))
            ->ensureColumn(new \rex_sql_column('createdate', 'int(11)', false))
            ->ensureColumn(new \rex_sql_column('createuser', 'varchar(255)', false))
            ->setPrimaryKey('id')
            ->ensure();

        //INSERT INTO `%TABLE_PREFIX%679_type_effects` (`id`, `type_id`, `effect`, `parameters`, `prior`) VALUES
        //    (1, 1, 'resize', 'a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"200\";s:24:\"rex_effect_resize_height\";s:3:\"200\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}', 1),
        //    (2, 2, 'resize', 'a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"600\";s:24:\"rex_effect_resize_height\";s:3:\"600\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}', 1),
        //    (3, 3, 'resize', 'a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:2:\"80\";s:24:\"rex_effect_resize_height\";s:2:\"80\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}', 1),
        //    (4, 4, 'resize', 'a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"246\";s:24:\"rex_effect_resize_height\";s:3:\"246\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}', 1),
        //    (5, 5, 'resize', 'a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"246\";s:24:\"rex_effect_resize_height\";s:3:\"246\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}', 1);
        $arrayValues = [
            ['id' => 1, 'type_id' => '1', 'effect' => 'resize', 'parameters' => 'a:6:{s:15:"rex_effect_crop";a:5:{s:21:"rex_effect_crop_width";s:0:"";s:22:"rex_effect_crop_height";s:0:"";s:28:"rex_effect_crop_offset_width";s:0:"";s:29:"rex_effect_crop_offset_height";s:0:"";s:24:"rex_effect_crop_position";s:13:"middle_center";}s:22:"rex_effect_filter_blur";a:3:{s:29:"rex_effect_filter_blur_amount";s:2:"80";s:29:"rex_effect_filter_blur_radius";s:1:"8";s:32:"rex_effect_filter_blur_threshold";s:1:"3";}s:25:"rex_effect_filter_sharpen";a:3:{s:32:"rex_effect_filter_sharpen_amount";s:2:"80";s:32:"rex_effect_filter_sharpen_radius";s:3:"0.5";s:35:"rex_effect_filter_sharpen_threshold";s:1:"3";}s:15:"rex_effect_flip";a:1:{s:20:"rex_effect_flip_flip";s:1:"X";}s:23:"rex_effect_insert_image";a:5:{s:34:"rex_effect_insert_image_brandimage";s:0:"";s:28:"rex_effect_insert_image_hpos";s:5:"right";s:28:"rex_effect_insert_image_vpos";s:6:"bottom";s:33:"rex_effect_insert_image_padding_x";s:3:"-10";s:33:"rex_effect_insert_image_padding_y";s:3:"-10";}s:17:"rex_effect_resize";a:4:{s:23:"rex_effect_resize_width";s:3:"200";s:24:"rex_effect_resize_height";s:3:"200";s:23:"rex_effect_resize_style";s:7:"maximum";s:31:"rex_effect_resize_allow_enlarge";s:11:"not_enlarge";}}', 'prior' => '1', ],
            ['id' => 2, 'type_id' => '2', 'effect' => 'resize', 'parameters' => 'a:6:{s:15:"rex_effect_crop";a:5:{s:21:"rex_effect_crop_width";s:0:"";s:22:"rex_effect_crop_height";s:0:"";s:28:"rex_effect_crop_offset_width";s:0:"";s:29:"rex_effect_crop_offset_height";s:0:"";s:24:"rex_effect_crop_position";s:13:"middle_center";}s:22:"rex_effect_filter_blur";a:3:{s:29:"rex_effect_filter_blur_amount";s:2:"80";s:29:"rex_effect_filter_blur_radius";s:1:"8";s:32:"rex_effect_filter_blur_threshold";s:1:"3";}s:25:"rex_effect_filter_sharpen";a:3:{s:32:"rex_effect_filter_sharpen_amount";s:2:"80";s:32:"rex_effect_filter_sharpen_radius";s:3:"0.5";s:35:"rex_effect_filter_sharpen_threshold";s:1:"3";}s:15:"rex_effect_flip";a:1:{s:20:"rex_effect_flip_flip";s:1:"X";}s:23:"rex_effect_insert_image";a:5:{s:34:"rex_effect_insert_image_brandimage";s:0:"";s:28:"rex_effect_insert_image_hpos";s:5:"right";s:28:"rex_effect_insert_image_vpos";s:6:"bottom";s:33:"rex_effect_insert_image_padding_x";s:3:"-10";s:33:"rex_effect_insert_image_padding_y";s:3:"-10";}s:17:"rex_effect_resize";a:4:{s:23:"rex_effect_resize_width";s:3:"600";s:24:"rex_effect_resize_height";s:3:"600";s:23:"rex_effect_resize_style";s:7:"maximum";s:31:"rex_effect_resize_allow_enlarge";s:11:"not_enlarge";}}', 'prior' => '1', ],
            ['id' => 3, 'type_id' => '3', 'effect' => 'resize', 'parameters' => 'a:6:{s:15:"rex_effect_crop";a:5:{s:21:"rex_effect_crop_width";s:0:"";s:22:"rex_effect_crop_height";s:0:"";s:28:"rex_effect_crop_offset_width";s:0:"";s:29:"rex_effect_crop_offset_height";s:0:"";s:24:"rex_effect_crop_position";s:13:"middle_center";}s:22:"rex_effect_filter_blur";a:3:{s:29:"rex_effect_filter_blur_amount";s:2:"80";s:29:"rex_effect_filter_blur_radius";s:1:"8";s:32:"rex_effect_filter_blur_threshold";s:1:"3";}s:25:"rex_effect_filter_sharpen";a:3:{s:32:"rex_effect_filter_sharpen_amount";s:2:"80";s:32:"rex_effect_filter_sharpen_radius";s:3:"0.5";s:35:"rex_effect_filter_sharpen_threshold";s:1:"3";}s:15:"rex_effect_flip";a:1:{s:20:"rex_effect_flip_flip";s:1:"X";}s:23:"rex_effect_insert_image";a:5:{s:34:"rex_effect_insert_image_brandimage";s:0:"";s:28:"rex_effect_insert_image_hpos";s:5:"right";s:28:"rex_effect_insert_image_vpos";s:6:"bottom";s:33:"rex_effect_insert_image_padding_x";s:3:"-10";s:33:"rex_effect_insert_image_padding_y";s:3:"-10";}s:17:"rex_effect_resize";a:4:{s:23:"rex_effect_resize_width";s:2:"80";s:24:"rex_effect_resize_height";s:2:"80";s:23:"rex_effect_resize_style";s:7:"maximum";s:31:"rex_effect_resize_allow_enlarge";s:11:"not_enlarge";}}', 'prior' => '1', ],
            ['id' => 4, 'type_id' => '4', 'effect' => 'resize', 'parameters' => 'a:6:{s:15:"rex_effect_crop";a:5:{s:21:"rex_effect_crop_width";s:0:"";s:22:"rex_effect_crop_height";s:0:"";s:28:"rex_effect_crop_offset_width";s:0:"";s:29:"rex_effect_crop_offset_height";s:0:"";s:24:"rex_effect_crop_position";s:13:"middle_center";}s:22:"rex_effect_filter_blur";a:3:{s:29:"rex_effect_filter_blur_amount";s:2:"80";s:29:"rex_effect_filter_blur_radius";s:1:"8";s:32:"rex_effect_filter_blur_threshold";s:1:"3";}s:25:"rex_effect_filter_sharpen";a:3:{s:32:"rex_effect_filter_sharpen_amount";s:2:"80";s:32:"rex_effect_filter_sharpen_radius";s:3:"0.5";s:35:"rex_effect_filter_sharpen_threshold";s:1:"3";}s:15:"rex_effect_flip";a:1:{s:20:"rex_effect_flip_flip";s:1:"X";}s:23:"rex_effect_insert_image";a:5:{s:34:"rex_effect_insert_image_brandimage";s:0:"";s:28:"rex_effect_insert_image_hpos";s:5:"right";s:28:"rex_effect_insert_image_vpos";s:6:"bottom";s:33:"rex_effect_insert_image_padding_x";s:3:"-10";s:33:"rex_effect_insert_image_padding_y";s:3:"-10";}s:17:"rex_effect_resize";a:4:{s:23:"rex_effect_resize_width";s:3:"246";s:24:"rex_effect_resize_height";s:3:"246";s:23:"rex_effect_resize_style";s:7:"maximum";s:31:"rex_effect_resize_allow_enlarge";s:11:"not_enlarge";}}', 'prior' => '1', ],
            ['id' => 5, 'type_id' => '5', 'effect' => 'resize', 'parameters' => 'a:6:{s:15:"rex_effect_crop";a:5:{s:21:"rex_effect_crop_width";s:0:"";s:22:"rex_effect_crop_height";s:0:"";s:28:"rex_effect_crop_offset_width";s:0:"";s:29:"rex_effect_crop_offset_height";s:0:"";s:24:"rex_effect_crop_position";s:13:"middle_center";}s:22:"rex_effect_filter_blur";a:3:{s:29:"rex_effect_filter_blur_amount";s:2:"80";s:29:"rex_effect_filter_blur_radius";s:1:"8";s:32:"rex_effect_filter_blur_threshold";s:1:"3";}s:25:"rex_effect_filter_sharpen";a:3:{s:32:"rex_effect_filter_sharpen_amount";s:2:"80";s:32:"rex_effect_filter_sharpen_radius";s:3:"0.5";s:35:"rex_effect_filter_sharpen_threshold";s:1:"3";}s:15:"rex_effect_flip";a:1:{s:20:"rex_effect_flip_flip";s:1:"X";}s:23:"rex_effect_insert_image";a:5:{s:34:"rex_effect_insert_image_brandimage";s:0:"";s:28:"rex_effect_insert_image_hpos";s:5:"right";s:28:"rex_effect_insert_image_vpos";s:6:"bottom";s:33:"rex_effect_insert_image_padding_x";s:3:"-10";s:33:"rex_effect_insert_image_padding_y";s:3:"-10";}s:17:"rex_effect_resize";a:4:{s:23:"rex_effect_resize_width";s:3:"246";s:24:"rex_effect_resize_height";s:3:"246";s:23:"rex_effect_resize_style";s:7:"maximum";s:31:"rex_effect_resize_allow_enlarge";s:11:"not_enlarge";}}', 'prior' => '1', ],
        ];
        $sql = \rex_sql::factory();
        foreach ($arrayValues as $values) {
            $sql->setTable($config->getConverterTable('679_type_effects'));
            $sql->setValues($values);
            $sql->insert();
        }
    }

    private function to450()
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();

        // Metainfo aktualisieren
        // ----------------------------------------
        $sql->setQuery('UPDATE `'.$config->getConverterTable('62_type').'` set dbtype="text", dblength="0" where label="REX_MEDIALIST_BUTTON" or label="REX_LINKLIST_BUTTON" or label="text" or label="date" or label="datetime"');

        // Utf-8
        // ----------------------------------------
        Updater::convertTablesToUtf8([
            $config->getConverterTable('action'),
            $config->getConverterTable('article'),
            $config->getConverterTable('article_slice'),
            $config->getConverterTable('clang'),
            $config->getConverterTable('file'),
            $config->getConverterTable('file_category'),
            $config->getConverterTable('module'),
            $config->getConverterTable('module_action'),
            $config->getConverterTable('template'),
            $config->getConverterTable('user'),
        ]);
    }

    private function to5xx()
    {
        $config = $this->getConfig();

        // Article
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('article'))
            ->renameColumn('re_id', 'parent_id')
            ->renameColumn('catprior', 'catpriority')
            ->renameColumn('startpage', 'startarticle')
            ->renameColumn('prior', 'priority')
            ->renameColumn('clang', 'clang_id')
            ->removeColumn('attributes')
            ->alter();

        // Article Slices
        // ----------------------------------------
        $sql = \rex_sql::factory();
        $src = $sql->escapeIdentifier('php');
        $dest = $sql->escapeIdentifier('value'.$config->getNewPhpValueField());
        $sql->setQuery('UPDATE '.$config->getConverterTable('article_slice').' SET '.$dest.' = IF('.$src.' = "", '.$dest.', CONCAT('.$dest.', "\n\n\n", '.$src.'))');

        $src = $sql->escapeIdentifier('html');
        $dest = $sql->escapeIdentifier('value'.$config->getNewHtmlValueField());
        $sql->setQuery('UPDATE '.$config->getConverterTable('article_slice').' SET '.$dest.' = IF('.$src.' = "", '.$dest.', CONCAT('.$dest.', "\n\n\n", '.$src.'))');

        \rex_sql_table::get($config->getConverterTable('article_slice'))
            ->renameColumn('clang', 'clang_id')
            ->renameColumn('ctype', 'ctype_id')
            ->renameColumn('re_article_slice_id', 'priority')
            ->renameColumn('modultyp_id', 'module_id')
            ->renameColumn('file1', 'media1')
            ->renameColumn('file2', 'media2')
            ->renameColumn('file3', 'media3')
            ->renameColumn('file4', 'media4')
            ->renameColumn('file5', 'media5')
            ->renameColumn('file6', 'media6')
            ->renameColumn('file7', 'media7')
            ->renameColumn('file8', 'media8')
            ->renameColumn('file9', 'media9')
            ->renameColumn('file10', 'media10')
            ->renameColumn('filelist1', 'medialist1')
            ->renameColumn('filelist2', 'medialist2')
            ->renameColumn('filelist3', 'medialist3')
            ->renameColumn('filelist4', 'medialist4')
            ->renameColumn('filelist5', 'medialist5')
            ->renameColumn('filelist6', 'medialist6')
            ->renameColumn('filelist7', 'medialist7')
            ->renameColumn('filelist8', 'medialist8')
            ->renameColumn('filelist9', 'medialist9')
            ->renameColumn('filelist10', 'medialist10')
            ->removeColumn('next_article_slice_id')
            ->removeColumn('php')
            ->removeColumn('html')
            ->alter();

        // Clang
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('clang'))
            ->ensureColumn(new \rex_sql_column('code', 'varchar(255)'), 'id')
            ->ensureColumn(new \rex_sql_column('priority', 'int(10)'), 'name')
            ->ensureColumn(new \rex_sql_column('status', 'tinyint(1)'), 'priority')
            ->alter();

        // Media
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('file'))
            ->renameColumn('file_id', 'id')
            ->removeColumn('re_file_id')
            ->setName($config->getConverterTable('media'))
            ->ensurePrimaryIdColumn()
            ->alter();

        // Media Category
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('file_category'))
            ->renameColumn('re_id', 'parent_id')
            ->setName($config->getConverterTable('media_category'))
            ->alter();

        // Metainfo
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('62_params'))
            ->renameColumn('field_id', 'id')
            ->renameColumn('prior', 'priority')
            ->renameColumn('type', 'type_id')
            ->ensureColumn(new \rex_sql_column('callback', 'text'), 'validate')
            ->setName($config->getConverterTable('metainfo_field'))
            ->ensurePrimaryIdColumn()
            ->alter();

        \rex_sql_table::get($config->getConverterTable('62_type'))
            ->setName($config->getConverterTable('metainfo_type'))
            ->ensurePrimaryIdColumn()
            ->alter();

        // Media Manager
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('679_types'))
            ->ensureColumn(new \rex_sql_column('createdate', 'datetime'), 'description')
            ->ensureColumn(new \rex_sql_column('createuser', 'varchar(255)'), 'createdate')
            ->ensureColumn(new \rex_sql_column('updatedate', 'datetime'), 'createuser')
            ->ensureColumn(new \rex_sql_column('updateuser', 'varchar(255)'), 'updatedate')
            ->setName($config->getConverterTable('media_manager_type'))
            ->ensurePrimaryIdColumn()
            ->alter();

        \rex_sql_table::get($config->getConverterTable('679_type_effects'))
            ->renameColumn('prior', 'priority')
            ->ensureColumn(new \rex_sql_column('updatedate', 'datetime'), 'createuser')
            ->ensureColumn(new \rex_sql_column('updateuser', 'varchar(255)'), 'updatedate')
            ->setName($config->getConverterTable('media_manager_type_effect'))
            ->ensurePrimaryIdColumn()
            ->alter();

        // Module
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('module'))
            ->renameColumn('ausgabe', 'output')
            ->renameColumn('eingabe', 'input')
            ->ensureColumn(new \rex_sql_column('output', 'mediumtext'), 'name')
            ->ensureColumn(new \rex_sql_column('input', 'mediumtext'), 'output')
            ->removeColumn('category_id')
            ->alter();

        // Module Action
        // ----------------------------------------

        // Templates
        // ----------------------------------------
        \rex_sql_table::get($config->getConverterTable('template'))
            ->ensureColumn(new \rex_sql_column('content', 'mediumtext'), 'name')
            ->removeColumn('label')
            ->alter();

    }

    public function callbackModifyArticles($params)
    {
        $sql = \rex_sql::factory();
        // rex_article anpassen
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');
    }

    public function callbackModifyArticleSlices($params)
    {
        $config = $this->getConfig();

        $sql = \rex_sql::factory();

        // Sprachen anpassen
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');

        // Revisionen berücksichtigen
        $revision = $sql->getArray('SELECT MAX(`revision`) AS revision_max FROM '.$r5TableEscaped);
        $revision_max = isset($revision[0]['revision_max']) ? $revision[0]['revision_max'] : 0;

        // Prioritäten setzen
        $clangs = $sql->getArray('SELECT `id` FROM '.$config->getConverterTable('clang'));

        foreach ($clangs as $clang) {
            $clang_id = $clang['id'];
            $articles = $sql->getArray('SELECT `id` FROM '.$config->getConverterTable('article').' WHERE `clang_id` = :clang_id', ['clang_id' => $clang_id]);

            if ($sql->getRows() >= 1) {
                foreach ($articles as $article) {
                    for ($revision = 0; $revision <= $revision_max; ++$revision) {
                        $article_id = $article['id'];
                        //$article_clang_id = $clang_id - 1;
                        $slices = $this->getSortedSlices($article_id, $clang_id, $revision, $sql->escapeIdentifier($config->getConverterTable('article_slice')));
                        if (\count($slices)) {
                            $priorities = [];
                            foreach ($slices as $slice) {
                                $priority = isset($priorities[$slice['ctype_id']]) ? $priorities[$slice['ctype_id']] + 1 : 1;
                                $priorities[$slice['ctype_id']] = $priority;
                                $slice_id = $slice['id'];
                                $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `priority` = :priority WHERE `id` = :sliceId', ['priority' => $priority, 'sliceId' => $slice_id]);
                            }
                        }
                    }
                }
            }
        }

        // serialisierte Daten prüfen und umwandeln
        $modulesRexVar = $sql->getArray('SELECT `id` FROM '.$config->getConverterTable('module').' WHERE `output` LIKE "%rex_var::toArray%"');
        $modulesArray = $sql->getArray('SELECT `id` FROM '.$config->getConverterTable('module').' WHERE `input` REGEXP ".*VALUE\\\[.*\\\]\s*\\\["');
        $modules = array_merge($modulesArray, $modulesRexVar);
        if (\count($modules)) {
            $module_ids = [];
            foreach ($modules as $module) {
                if (!isset($module_ids[$module['id']])) {
                    $module_ids[$module['id']] = 'module_id = "'.$module['id'].'"';
                }
            }
            $slices = $sql->getArray('SELECT `id`, `value1`, `value2`, `value3`, `value4`, `value5`, `value6`, `value7`, `value8`, `value9`, `value10`, `value11`, `value12`, `value13`, `value14`, `value15`, `value16`, `value17`, `value18`, `value19`, `value20` FROM '.$r5TableEscaped.' WHERE '.implode(' OR ', $module_ids));
            foreach ($slices as $slice) {
                $sets = [];
                for ($i = 1; $i <= 20; ++$i) {
                    $column = 'value'.$i;
                    // Notices bei unserialize vermeiden
                    if (preg_match('@^a:\d+:{.*?}$@', $slice[$column])) {
                        $value = \rex_var::toArray($slice[$column]);
                        if (\is_array($value)) {
                            $sets[] = $sql->escapeIdentifier($column).'` = \''.addslashes(json_encode($value)).'\'';
                        }
                    }
                }
                if (\count($sets)) {
                    $sql->setQuery('UPDATE '.$r5TableEscaped.' SET '.implode(', ', $sets).' WHERE `id` = :sliceId', ['sliceId' => $slice['id']]);
                }
            }
        }
    }

    public function callbackModifyLanguages($params)
    {
        $sql = \rex_sql::factory();

        // rex_clang anpassen
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `id` = id +1 ORDER BY id DESC');
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `priority` = `id`');
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `status` = 1');
    }

    public function callbackModifyMetainfoTypes($params)
    {
        $sql = \rex_sql::factory();

        // rex_metainfo_type anpassen
        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `label` = REPLACE(`label`, "_BUTTON", "_WIDGET")');
    }

    private function getSortedSlices($articleId, $clangId, $revision, $table)
    {
        $sql = \rex_sql::factory();

        $items = $sql->getArray('
            SELECT  `id`, 
                    `priority`, 
                    `ctype_id` 
            FROM    '.$table.'
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
