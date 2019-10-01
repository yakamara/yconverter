<?php

namespace YConcerter\Package;

class Sprog extends Package
{
    public function getName(): string
    {
        return 'sprog';
    }

    public function getTables(): array
    {
        return [
            'sprog_wildcard' => [
                '2.7.0' => [
                    'callbacks' => [
                        [
                            'function' => 'callbackModifyWildard',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function updateTableStructure()
    {
        $config = $this->getConfig();

        \rex_sql_table::get($config->getConverterTable('b_1_opf_lang'))
            ->renameColumn('clang', 'clang_id')
            ->renameColumn('replacement', 'replace')
            ->ensureColumn(new \rex_sql_column('replace', 'text'), 'wildcard')
            ->ensureColumn(new \rex_sql_column('createdate', 'datetime'), 'replace')
            ->ensureColumn(new \rex_sql_column('createuser', 'varchar(255)'), 'createdate')
            ->ensureColumn(new \rex_sql_column('updatedate', 'datetime'), 'createuser')
            ->ensureColumn(new \rex_sql_column('updateuser', 'varchar(255)'), 'updatedate')
            ->ensureColumn(new \rex_sql_column('revision', 'int(10) unsigned'))
            ->setName($config->getConverterTable('sprog_wildcard'))
            ->alter();
    }

    public function callbackModifyWildard($params)
    {
        $sql = \rex_sql::factory();

        $r5TableEscaped = $sql->escapeIdentifier($params['r5Table']);
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `wildcard` = REPLACE(`wildcard`, "###", "")');
        $sql->setQuery('UPDATE '.$r5TableEscaped.' SET `clang_id` = clang_id +1 ORDER BY clang_id DESC');
    }
}
