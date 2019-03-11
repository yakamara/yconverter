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

class Config
{
    private $config;

    public function __construct()
    {
        $configFile = \rex_addon::get('yconverter')->getDataPath('config.yml');
        $this->config = \rex_file::getConfig($configFile);

        if (!isset($this->config['core_version'])) {
            return null;
        }
        if (!isset($this->config['table_prefix']) || $this->config['table_prefix'] == '') {
            return null;
        }
    }


    public function getOutdatedCoreVersion()
    {
        return $this->config['core_version'];
    }

    public function getOutdatedTablePrefix()
    {
        return $this->config['table_prefix'];
    }

    public function getOutdatedTable($table)
    {
        return $this->getOutdatedTablePrefix().$table;
    }

    public function getConverterTablePrefix()
    {
        return 'yconverter_';
    }

    public function getConverterTable($table)
    {
        return $this->getConverterTablePrefix().$table;
    }

    public function getOutdatedDatabaseId()
    {
        return '5';
    }

    public function getNewPhpValueField()
    {
        return '19';
    }

    public function getNewHtmlValueField()
    {
        return '20';
    }
}
