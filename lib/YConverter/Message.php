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

class Message
{
    protected $messages = [];

    public function addError($value)
    {
        $this->messages[] = \rex_view::error($value);
    }

    public function addInfo($value)
    {
        $this->messages[] = \rex_view::info($value);
    }

    public function addSuccess($value)
    {
        $this->messages[] = \rex_view::success($value);
    }

    public function addWarning($value)
    {
        $this->messages[] = \rex_view::warning($value);
    }

    public function getAll()
    {
        return implode('', $this->messages);
    }
}
