<?php

namespace YConcerter\Package;

class Cronjob extends Package
{
    public function getName(): string
    {
        return 'cronjob';
    }

    public function getTables(): array
    {
        return [
            'cronjob' => [
                '2.7.0' => [
                    'convertColumns' => [
                        'parameters' => 'serialize',
                        'nexttime' => 'timestamp',
                        'createdate' => 'timestamp',
                        'updatedate' => 'timestamp',
                    ],
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyCronjob',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function updateTableStructure()
    {
        $config = $this->getConfig();

        \rex_sql_table::get($config->getConverterTable('630_cronjobs'))
            ->ensureColumn(new \rex_sql_column('description', 'varchar(255)'), 'name')
            ->ensureColumn(new \rex_sql_column('interval', 'text'), 'parameters')
            ->ensureColumn(new \rex_sql_column('execution_moment', 'tinyint(1)'), 'environment')
            ->ensureColumn(new \rex_sql_column('execution_start', 'datetime'), 'execution_moment')
            // Typ wird im Modifier angepasst, da vorher die Timestamps erst in Datetime umgewandelt werden.
            //->ensureColumn(new \rex_sql_column('nexttime', 'datetime'), 'interval')
            //->ensureColumn(new \rex_sql_column('createdate', 'datetime'), 'status')
            //->ensureColumn(new \rex_sql_column('updatedate', 'datetime'), 'createuser')
            ->setName($config->getConverterTable('cronjob'))
            ->alter();
    }

    public function callbackModifyCronjob($params)
    {
        $sql = \rex_sql::factory();

        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `environment` = REPLACE(`environment`, "|0|", "|frontend|")');
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `environment` = REPLACE(`environment`, "|1|", "|backend|")');
    }
}
