<?php

/**
 * This is the model class for table "{{fileDirectory}}".
 *
 * The followings are the available columns in table '{{fileDirectory}}':
 * @property string $id
 * @property string $parentId
 * @property string $path
 * @property string $name
 * @property string $created
 * @property string $modified
 *
 */
class FileDirectory extends CActiveRecord implements JsonSerializable
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{fileDirectory}}';
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'modified' => array(
                'class' => 'EModificationBehavior',
            ),
        ));
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('path, name, parentId', 'default', 'value' => null),
            array('name, path', 'length', 'max'=>255),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, name, path, created, modified, modifierId', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(

        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(

        );
    }


    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CActiveRecord the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function jsonSerialize() {
        return array(
            'id' => $this->id === null ? null : intval($this->id),
            'name'     => $this->name,
            'path' =>$this->path,
            'type' => 'directory',
        );
    }
}
