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

class YConverter
{
    private $config;
    private $sql;
    private $message;

    public function __construct()
    {

        $this->sql = \rex_sql::factory();
        $this->sql->setDebug(false);

        $this->config = new Config();
        $this->message = new Message();
    }

    public function cloneTables()
    {
        $cloner = new Cloner($this->config, $this->message);
        $cloner->fetchTables();
        $this->message = $cloner->getMessage();
    }

    public function getMessages()
    {
        return $this->message->getAll();
    }

    public function updateTables()
    {
        $updater = new Updater($this->config, $this->message);
        $updater->run();
        $this->message = $updater->getMessage();
    }

    public function modifyTables()
    {
        $updater = new Modifier($this->config, $this->message);
        $updater->updateTables();
        $this->message = $updater->getMessage();
    }

    public function callCallbacks()
    {
        $updater = new Modifier($this->config, $this->message);
        $updater->callCallbacks();
        $this->message = $updater->getMessage();
    }

    public function getMissinColumns()
    {
        $updater = new Modifier($this->config, $this->message);
        $updater->checkMissingColumns();
        $this->message = $updater->getMessage();
    }

    public function transferData()
    {
        $updater = new Shuttle($this->config, $this->message);
        $updater->transfer();
        $this->message = $updater->getMessage();
    }
}
