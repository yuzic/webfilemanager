<?php
/**
 * Created by PhpStorm.
 * User: itcoder
 * Date: 23.08.14
 * Time: 12:37
 */

class DirectoryBehavior extends CBehavior{
    /**
     * Upload path to directory
     * @var string
     */
    public $uploadPath = 'fileManager';
    /**
     * Directory name new path
     * @var string
     */
    public $directoryName = null;
    /**
     * Directory Path
     * @var string
     */
    public $directoryPath = null;

    public function events()
    {
    	return array_merge(parent::events(), array(
    		'onBeforeSave' => 'beforeSave',
    	));
    }

    public function beforeSave($event){
        if ($this->getDirectoryName() !== null)
        {
            if (file_exists($this->getFullPath()))
            {
                $this->owner->addError('Error create direcotory', Yii::t('Admin', 'Directory exist'));
                $event->isValid = false;
                return false;
            }
            if (mkdir($this->getFullPath()))
            {
                $this->owner->path = $this->getSavePath();
                $this->owner->name = $this->getDirectoryName();
                $event->isValid = true;
                return true;
            }
            else
            {
                $this->owner->addError('create direcotory', Yii::t('Admin', 'Unable to create directory to server'));
                $event->isValid = false;
            }
        }
        else
        {
            $this->owner->addError('uploadFile', Yii::t('Admin', 'directoryName is empty'));
            $event->isValid = false;
            return false;
        }
    }

    private function getSavePath()
    {
        return !empty($this->directoryPath)
            ? $this->getDirectoryPath() . $this->getDirectoryName()
            :  $this->getDirectoryName();
    }

    private function getFullPath()
    {
        return $this->getWebRootPath() .'/'. $this->getUploadPath(). $this->getDirectoryPath(). '/'. $this->getDirectoryName();
    }

    private function getDirectoryName()
    {
        return $this->directoryName;
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
        $directoryPath = null;
        if ($this->directoryPath !== null){
            $directoryPath = '/' .$this->directoryPath .'/';
        }
        return $directoryPath;
    }

}
