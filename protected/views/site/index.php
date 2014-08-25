<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>
<div id="file-manager-id"></div>
<?php $this->beginWidget('ext.fileManager.FileManagerWidget', array(
    'model' => $model,
    'attribute'	=> 'name',
    'initialSavePath' => 'news',
    'htmlOptions' => array(
        'id' => 'file-manager-id'
    )
));?>
<?php $this->endWidget(); ?>
