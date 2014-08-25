<div class="file-manager">
    <div class="header"><h1>File Manager</h1>  </div>
    <div class="header-panel">
        <div class="add-directory-filed">
            <input type="text" id="directory-name-selector" placeholder="<?php echo Yii::t('Site','Add Directory');?>">
        </div>
        <div class="add-directory-button" title="<?php echo Yii::t('Site','Add Directory');?>"></div>
        <div class="download-button" title="Загрузить" id="<?php echo $this->fileManagerContainerId;?>" >
            <?php echo CHtml::fileField($this->fileManagerContainerId.'Uploader[]', null, array('id' => $this->fileManagerContainerId.'Uploader', 'class' => 'uploadInput', 'multiple'=>'multiple',));?></div>
    </div>
    <div class="body-area">
        <div class="directory-up" title="<?php echo Yii::t('Site','Up');?>">..</div>
        <ul id="file-list-container">

        </ul>

    </div>
    <div class="footer-panel"></div>
</div>
