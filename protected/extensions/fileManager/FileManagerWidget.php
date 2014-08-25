<?php
class FileManagerWidget extends CWidget{
	/**
	 * Path to assets for this widget
	 * The widget will publish its own directory if none is specified
	 * @var string
	 */
	public $assetsPath = null;
    /**
     * @var array the HTML attributes that should be rendered in the HTML tag representing the JUI widget.
     */
    public $htmlOptions=array();
    /**
     * @var CModel the data model associated with this widget.
     */
    public $model;
    /**
     * @var string the attribute associated with this widget.
     * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
     */
    public $attribute;
    /**
     * @var string the input name. This must be set if {@link model} is not set.
     */
    public $name;
    /**
     * @var string the input value.
     */
    public $value;
    /**
     * @var string id of gallery container
     */
    public $fileManagerContainerId = 'fileManagerContainer';
    /**
     * @var string path (above 'images') to which to save photos
     */
    public $initialSavePath;

    /**
     * Resolves name and ID of the input. Source property of the name and/or source property of the attribute
     * could be customized by specifying first and/or second parameter accordingly.
     * @param string $nameProperty class property name which holds element name to be used. This parameter
     * is available since 1.1.14.
     * @param string $attributeProperty class property name which holds model attribute name to be used. This
     * parameter is available since 1.1.14.
     * @return array name and ID of the input: array('name','id').
     * @throws CException in case model and attribute property or name property cannot be resolved.
     */
    protected function resolveNameID($nameProperty='name',$attributeProperty='attribute')
    {
    	if($this->$nameProperty!==null)
    		$name=$this->$nameProperty;
    	elseif(isset($this->htmlOptions[$nameProperty]))
    	$name=$this->htmlOptions[$nameProperty];
    	elseif($this->hasModel())
    	$name=CHtml::activeName($this->model,$this->$attributeProperty);
    	else
    		throw new CException(Yii::t('zii','{class} must specify "model" and "{attribute}" or "{name}" property values.',
    			array('{class}'=>get_class($this),'{attribute}'=>$attributeProperty,'{name}'=>$nameProperty)));

    	if(($id=$this->getId(false))===null)
    	{
    		if(isset($this->htmlOptions['id']))
    			$id=$this->htmlOptions['id'];
    		else
    			$id=CHtml::getIdByName($name);
    	}

    	return array($name,$id);
    }

    /**
     * @return boolean whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
    	return $this->model instanceof CModel && $this->attribute!==null;
    }

    public function init()
    {
    	parent::init();
    	if ($this->assetsPath === null) {
    		$dir = dirname(__FILE__) . '/assets';
    		$this->assetsPath = Yii::app()->assetManager->publish($dir);
    	}

    	$this->registerClientScript();

    }

    /**
	 * Registers necessary client scripts.
	 */
	public function registerClientScript() {
		$cs = Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery');
		$cs->registerCssFile($this->assetsPath.'/css/fileManager.css');
		$cs->registerScriptFile($this->assetsPath.'/js/jquery.fileManager.js', CClientScript::POS_END);
	}

    public function run()
    {
        list($name, $id) = $this->resolveNameID();

        if (isset($this->htmlOptions['id'])) {
        	$id = $this->htmlOptions['id'];
        }
        else {
        	$this->htmlOptions['id']=$id;
        }
        if (isset($this->htmlOptions['name'])) {
        	$name = $this->htmlOptions['name'];
        }

        if ($this->hasModel()) {
        	echo CHtml::activeHiddenField($this->model, $this->attribute, $this->htmlOptions);
        }
        else {
        	echo CHtml::hiddenField($name, $this->value, $this->htmlOptions);
        }

//        $galleryHtml = CJavaScript::encode(
//        	CHtml::openTag('ul', array('id' => $this->fileManagerContainerId, 'class'=>'uploadList') )
//        	.CHtml::openTag('li', array('class' => 'uploadifyButton', 'title' => Yii::t('Admin', 'Add Photo')))
//        	.CHtml::fileField($this->fileManagerContainerId.'Uploader[]', null, array('id' => $this->fileManagerContainerId.'Uploader', 'class' => 'uploadInput', 'multiple'=>'multiple', 'accept'=>'image' ))
//        	.CHtml::closeTag('li')
//        	.CHtml::closeTag('ul')
//        );
        $galleryOptions = array(
        	'csrfTokenName' => Yii::app()->request->csrfTokenName,
        	'csrfToken' => Yii::app()->request->csrfToken,
        	'pickerSelector' => '#'.$this->fileManagerContainerId.'Uploader',
        	'defaultPhotoAlt' => Yii::t('Admin', 'Photo'),
        	'galleryIdPlaceholder' => 'xxxGalleryIdxxx',
        	'photoIdPlaceholder' => 'xxxPhotoIdxxx',
        	'createFileRoute' => $this->controller->createUrl('//fileManager/createFile'),
        	'createDirectoryRoute' => $this->controller->createUrl('//fileManager/createDirectory'),
        	'deleteFileRoute' => $this->controller->createUrl('//fileManager/deleteFile'),
        	'deleteDirectoryRoute' => $this->controller->createUrl('//fileManager/deleteDirectory'),
        	'listFile' => $this->controller->createUrl('//fileManager/listFile'),
        	'galleryIdInputSelector' => '#'.$id,
        );

        $gOptions = CJavaScript::encode($galleryOptions);
        $js = <<<EOD
jQuery('#{$this->fileManagerContainerId}').fileManager($gOptions);
EOD;
        $cs = Yii::app()->getClientScript();
        $cs->registerScript(__CLASS__.'#'.$id, $js, CClientScript::POS_READY);
        $this->render('view');
    }

}
