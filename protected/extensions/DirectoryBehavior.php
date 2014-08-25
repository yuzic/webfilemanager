<?php
/**
 * Created by PhpStorm.
 * User: itcoder
 * Date: 23.08.14
 * Time: 12:37
 */

class DirectoryBehavior extends CBehavior{
    public $uploadPath = 'fileManager';
    public $directoryName = null;
    public $directoryPath = null;


    public function events() {
    	return array_merge(parent::events(), array(
    		'onBeforeSave' => 'beforeSave',
    	));
    }


    private function isValidPath($webPath, $path){
    	$path = pathinfo(realpath($path), PATHINFO_DIRNAME );
    	if (strlen($webPath) == strlen($path) && is_dir($path) ){
    		return true;
    	}else{
    		return false;
    	}
    }


    public function beforeSave($event){
        $directoryPath = null;
        if ($this->directoryPath !== null){
            $directoryPath = '/' .$this->directoryPath .'/';
        }
        $pathUpload = Yii::getPathOfAlias('webroot') .'/'. $this->uploadPath . $directoryPath;
        $fullPathUpload  = $pathUpload . '/' . $this->directoryName;
        if ($this->directoryName !== null){
            if (file_exists($fullPathUpload)){
                $this->owner->addError('Error create direcotory', Yii::t('Admin', 'Directory exist'));
                $event->isValid = false;
                return false;
            }
            if (mkdir($fullPathUpload)){
                $this->owner->path = !empty($this->directoryPath)
                    ? $this->directoryPath .'/'. $this->directoryName
                    :  $this->directoryName;
                $this->owner->name = $this->directoryName;
                $event->isValid = true;
                return true;
            }else{
                $this->owner->addError('create direcotory', Yii::t('Admin', 'Unable to create directory to server'));
                $event->isValid = false;
            }
        }else{
            $event->isValid = false;
            $this->owner->addError('uploadFile', Yii::t('Admin', 'directoryName is empty'));
            return false;
        }
    }

}
