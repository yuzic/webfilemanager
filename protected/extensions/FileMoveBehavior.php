<?php
/**
 * Created by PhpStorm.
 * User: itcoder
 * Date: 23.08.14
 * Time: 12:37
 */

class FileMoveBehavior extends CBehavior{
    /**
     * Upload path  Files
     * @var string
     */
    public $uploadPath = 'fileManager';
    /**
     * Directory Path  files
     * @var string
     */
    public $directoryPath = null;

    public function events()
    {
    	return array_merge(parent::events(), array(
    		'onBeforeSave' => 'beforeSave',
    	));
    }

    public function beforeSave($event)
    {
        $fileName = $_FILES['File']['name']['uploadFile'];
        if (move_uploaded_file($_FILES['File']['tmp_name']['uploadFile'], $this->getFullPath() .'/'. $fileName ))
        {
            $this->owner->name = $fileName;
            $this->owner->size = filesize($this->getFullPath() .'/'. $fileName);
            $event->isValid = true;
            return true;
        }
        else
        {
            $this->owner->addError('uploadFile', Yii::t('Admin', 'Unable to save file to server'));
            $event->isValid = false;
            return false;
        }
    }

    private function getFullPath()
    {
        return $this->getWebRootPath() .'/'. $this->getUploadPath() . $this->getDirectoryPath();
    }

    private function getWebRootPath()
    {
        return Yii::getPathOfAlias('webroot');
    }

    private function getUploadPath()
    {
        return $this->uploadPath;
    }

    private function getDirectoryPath()
    {
        if ($this->directoryPath !== null)
            $this->directoryPath = '/' . $this->directoryPath;
        return $this->directoryPath;
    }

}
