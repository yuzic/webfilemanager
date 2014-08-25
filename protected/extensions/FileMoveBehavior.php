<?php
/**
 * Created by PhpStorm.
 * User: itcoder
 * Date: 23.08.14
 * Time: 12:37
 */

class FileMoveBehavior extends CBehavior{
    public $uploadPath = 'fileManager';
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
            $directoryPath = '/' .$this->directoryPath;
        }
        $filePathUpload = Yii::getPathOfAlias('webroot') .'/'. $this->uploadPath .$directoryPath;
        $fileName = $_FILES['File']['name']['uploadFile'];
        if (move_uploaded_file($_FILES['File']['tmp_name']['uploadFile'], $filePathUpload.'/'.$fileName )) {
             $this->owner->name = $fileName;
             $this->owner->size = filesize($filePathUpload.'/'.$fileName);
             return true;
        }
        else {
                $this->owner->addError('uploadFile', Yii::t('Admin', 'Unable to save file to server'));
                $event->isValid = false;
                return false;
            }
        }
}
