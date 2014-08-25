<?php

class m140824_064240_addFileTable extends CDbMigration
{
    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{file}}';
    }

    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
        $prefix = $this->getDbConnection()->tablePrefix;
        $this->createTable($this->tableName(), array(
            'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
            'directoryId' => 'int(10) unsigned NOT NULL',
            'name' => 'varchar(255)  NOT NULL',
            'size' => 'int(10) unsigned NOT NULL',
            'remoteIp' => 'int(10) unsigned NOT NULL',
            'created' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'modified' => 'timestamp NULL DEFAULT NULL',
            'statusId' => 'int(10) unsigned DEFAULT 1',
            'PRIMARY KEY (`id`)',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'style (to which fileDirectory should belong)\';');

        $this->addForeignKey($prefix.'file_directory_fk_constraint', $this->tableName(), 'directoryId', '{{fileDirectory}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $prefix = $this->getDbConnection()->tablePrefix;
        $this->dropForeignKey($prefix.'file_directory_fk_constraint', $this->tableName());
        $this->dropTable($this->tableName());
    }
}
