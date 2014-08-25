<?php
class FileManagerController extends EHttpController{
    /**
     * Path to upload
     * @var string
     */
    public $uploadPath = 'fileManager';

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'actions'=>array(
                    'listFile',
                    'createFolder',
                    'createFile',
                    'deleteFile',
                ),
                'users' => array(
                    '@',
                ),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function deleteFile($path){
        $path = Yii::getPathOfAlias('webroot') . '/' .$this->uploadPath .'/'.$path;
        return unlink($path);
    }

    public function deleteDir($path){
        $uploadPath = Yii::getPathOfAlias('webroot') . '/' .$this->uploadPath;
        if ($path !== $uploadPath){
            $callback = array(__CLASS__, __FUNCTION__);
            return is_file($path)
                ? unlink($path)
                : array_map($callback, glob($path.'/*')) == rmdir($path);
        }
    }

    /**
     * List file and directory
     * @param int $id
     * @return array
     */
    public function listFileFromDirectory($id){
        $directoryList = FileDirectory::model()->findAllByAttributes(array('parentId' => $id));
        $fileList = File::model()->findAllByAttributes(array('directoryId' => $id));
        $dataFileCollection = array();
        foreach ($directoryList as $directory)
            $dataFileCollection[] = $directory->jsonSerialize();
        foreach ($fileList as $file)
            $dataFileCollection[] = $file->jsonSerialize();
        return $dataFileCollection;
    }

    public function actionCreateDirectory() {
        $model = new FileDirectory();
        $fileDirectory = FileDirectory::model()->findByPk($_POST['parentId']);
        $model->attachBehavior('DirectorySave', array(
            'class' => 'ext.DirectoryBehavior',
            'directoryName' => $_POST['name'],
            'directoryPath' => $fileDirectory->path,
        ));
        if (Yii::app()->request->getPost('name') !== null) {
            $model->attributes = $_POST;
            if ($model->save()) {
                $this->headers['HTTP/1.1 201 Created'] = '';
                $this->headers['Location'] = $this->createAbsoluteUrl('//directory/view', array('id' => $model->id));
            }
            else {
                $this->headers['HTTP/1.1 400 Bad request'] = '';
            }
        }

        if (Yii::app()->request->isAjaxRequest) {
            $this->renderAjax('create_json', array('model' => $model));
        }
    }

    public function actionlistFile(){
        if (isset($_POST['directoryId'])){
            $directoryId = $_POST['directoryId'];
        }else{
            $directoryId = 1;
        }
        $this->headers['HTTP/1.1 201 Created'] = '';
        $this->renderAjax('view_json',array('model' => $this->listFileFromDirectory($directoryId)));
    }


    public function actionCreateFile() {
        $model = new File;
        $fileDirectory = FileDirectory::model()->findByPk($_POST['File']['directoryId']);
        $model->attachBehavior('FileSave', array(
            'class' => 'ext.FileMoveBehavior',
            'directoryPath' => $fileDirectory->path,
        ));
        if (Yii::app()->request->getPost('File') !== null) {
            $model->attributes = Yii::app()->request->getPost('File');
            $model->remoteIp = ip2long($_SERVER['REMOTE_ADDR']);
            if ($model->save()) {
                $this->headers['HTTP/1.1 201 Created'] = '';
                $this->headers['Location'] = $this->createAbsoluteUrl('//file/view');
            }else{
                echo 'Error save file'.serialize($model->getErrors()), 'error','application.SiteController';
                $this->headers['HTTP/1.1 400 Bad request'] = '';
            }
        }else{
            throw new CHttpException(403, Yii::t('yii','You are not authorized to perform this action.'));
        }

        if (Yii::app()->request->isAjaxRequest) {
            $this->renderAjax('createFile_json', array('model' => $model->jsonSerialize()));
        }
    }

    public function actionDeleteDirectory(){
        if (isset($_POST['id'])){
            $id  = (int) $_POST['id'];
            $model = FileDirectory::model()->findByPk($id);
            if ($model === null) {
                throw new CHttpException(404, Yii::t('Admin', 'File not found'));
            }
            $path = Yii::getPathOfAlias('webroot') . '/' .$this->uploadPath .'/'.$model->path;
            $this->deleteDir($path);
            //$this->deleteFile($model->path);
            if (!$model->delete()){
                $this->headers['HTTP/1.1 400 Bad request'] = '';
            }

            if (Yii::app()->request->isAjaxRequest) {
                $this->headers['HTTP/1.1 201 Created'] = '';
                $this->renderAjax('deleteFile_json', array('model' => $model, 'status' => true));
            }
            else {
                $this->render('deleteFile_json', array('model' => $model));
            }
        }
        else {
            throw new CHttpException(403, Yii::t('yii','You are not authorized to perform this action.'));
        }
    }

    public function actionDeleteFile() {
        if (isset($_POST['id'])){
            $id  = (int) $_POST['id'];
            $model = File::model()->findByPk($id);
            if ($model === null) {
                throw new CHttpException(404, Yii::t('Admin', 'File not found'));
            }
            $this->deleteFile($model->directory->path. '/' .$model->name);
            if ($model->delete()){

            }else{
                $this->headers['HTTP/1.1 400 Bad request'] = '';
            }

            if (Yii::app()->request->isAjaxRequest) {
                $this->headers['HTTP/1.1 201 Created'] = '';
                $this->renderAjax('deleteFile_json', array('model' => $model, 'status' => true));
            }
            else {
                $this->render('deleteFile_json', array('model' => $model));
            }
        }
        else {
            throw new CHttpException(403, Yii::t('yii','You are not authorized to perform this action.'));
        }
    }
}
