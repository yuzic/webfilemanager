<?php

class m140824_063511_addFileDirectoryTable extends CDbMigration
{

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{fileDirectory}}';
    }

    public function safeUp()
    {
        $prefix = $this->getDbConnection()->tablePrefix;
        $this->createTable($this->tableName(), array(
                'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
                'parentId' => 'int(10) unsigned NULL',
                'path' => 'varchar(500)  NOT NULL',
                'name' => 'varchar(255) NOT NULL',
                'created' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
                'modified' => 'timestamp NULL DEFAULT NULL',
                'PRIMARY KEY (`id`)'
            ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'File Directory (to which file should belong)\';');
        $this->addForeignKey($prefix.'fileDirectory_parent_fk_constraint', $this->tableName(), 'parentId', $this->tableName(), 'id', 'CASCADE', 'CASCADE');

    }

    public function safeDown()
    {
        $prefix = $this->getDbConnection()->tablePrefix;
        $this->dropForeignKey($prefix.'fileDirectory_parent_fk_constraint', $this->tableName());
        $this->dropTable($this->tableName());
    }

}
