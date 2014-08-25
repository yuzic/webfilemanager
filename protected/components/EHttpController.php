<?php

/**
 * EHttpController
 * Extends CController to provide default Html and Http mechanisms
 * @author Itcoder, Dizbook, 2013
 * @copyright Itcoder, 2013
 * @licence http://www.opensource.org/licenses/mit-license.php MIT License
 */
class EHttpController extends CController
{
	/**
	 * @var array of all acceptable MIME type parameters
	 */
	public $mediaTypes = array(
		'text/html' => array(
			'type' => 'text',
			'subType' => 'html',
			'baseType' => null,
			'quality' => 0.9,
			'prolog' => '<!DOCTYPE html>',
			'viewModifier' => '',
			'html' => true,
		),
		'application/xhtml+xml' => array(
			'type' => 'application',
			'subType' => 'xhtml',
			'baseType' => 'xml',
			'quality' => 1,
			'prolog' => '<!DOCTYPE html>',
			'viewModifier' => '',
			'html' => true,
		),
		'application/xml' => array(
			'type' => 'application',
			'subType' => 'xml',
			'baseType' => null,
			'quality' => 0.9,
			'prolog' => '<?xml version="1.0" encoding="UTF-8"?>',
			'viewModifier' => '_xml',
			'html' => false,
		),
		'application/json' => array(
			'type' => 'application',
			'subType' => 'json',
			'baseType' => null,
			'quality' => 0.9,
			'prolog' => '',
			'viewModifier' => '_json',
			'html' => false,
		),
	);

	/**
	 * @var string the MIME type for the generated view
	 */
	public $mediaType = null;

	/**
	 * @var string the HTML prolog (if applicable) for the generated view (usually taken from the MIME type params)
	 */
	public $prolog = null;

	/**
	 * @var string the text to be appended to the view name to use the appropriate view file for the MIME type
	 */
	public $viewModifier = null;

	/**
	 * @var boolean whether the rendered view is an Html page or not (and so whether we should include things like analytics)
	 */
	public $htmlRender = true;

	/**
	 * @var string the character set to be used in the Content-Type header
	 */
	public $charSet = 'UTF-8';

	/**
	 * @var string the Google Analytics ID for the web site (analytics are not included if null)
	 */
	public $googleAnalyticsId = null;

	/**
	 * @var string the Yandex Analytics ID for the web site (analytics are not included if null)
	 */
	public $yandexAnalyticsId = null;

	/**
	 * @var array of meta tags to be inserted into the web page (if Html rendering)
	 */
	public $metaTags = array(
		'mimeType' => null,
		'language' => null,
		'author' => array(
			'content' => '',
			'name' => 'author',
			'httpEquiv' => null,
		),
		'description' => null,
		'keywords' => null,
		'rating' => array(
			'content' => 'general',
			'name' => 'rating',
			'httpEquiv' => null),
		'robots' => array(
			'content' => 'index,follow',
			'name' => 'robots',
			'httpEquiv' => null),
		'charset' => array(
			'content' => 'text/html; charset=UTF-8',
			'name' => null,
			'httpEquiv' => 'Content-Type'),
		
	);

	/**
	 * @var array of link tags to be inserted into the web page (if Html rendering)
	 */
	public $linkTags = array(
		'shortcut icon' => null,
		'icon' => null,
	);

	/**
	 * @var array of CSS files to be included in the web page (if Html rendering)
	 */
	public $cssFiles = array(
		'main' => null,
	);

	/**
	 * @var array map of HTTP headers to be used for the response
	 */
	public $headers = array(
		// included as blank so that it is first in the array
		'Content-Type' => '',
		'Content-Language' => '',
		'Vary' => 'Accept, Accept-Language',
	);

	/**
	 * The filter method for 'postOnly' filter.
	 * This filter reports an error if the applied action is receiving a non-POST request.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @throws CHttpException if the current request is not a POST request
	 */
	public function filterPostOnly($filterChain)
	{
		if(Yii::app()->getRequest()->getIsPostRequest())
			$filterChain->run();
		else
			throw new CHttpException(405,Yii::t('yii','Your request is not valid.'));
	}

	/**
	 * The filter method for 'mediaType' filter.
	 * This filter analyses the Accept headers to determine which media type should be sent to the client.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 */
	public function filterMediaType($filterChain) {
		$this->mediaType = self::preferredMediaType($this->mediaTypes, Yii::app()->request->getPreferredAcceptTypes());
		if ($this->mediaType === null) {
			$this->noAcceptedMedia();
		}
		else {
			// special case to disable xHtml when debugging (because Yii adds output after the body element)
			if (YII_DEBUG && $this->mediaType == 'application/xhtml+xml') {
				Yii::trace('Would return xhtml but debugging is enabled', 'extensions.components.RController');
				$this->mediaType = 'text/html';
			}
		}

		if ($this->mediaType !== null && isset($this->mediaTypes[$this->mediaType])) {
			if (isset($this->mediaTypes[$this->mediaType]['prolog'])) {
				$this->prolog = $this->mediaTypes[$this->mediaType]['prolog'];
			}
			if (isset($this->mediaTypes[$this->mediaType]['viewModifier'])) {
				$this->viewModifier = $this->mediaTypes[$this->mediaType]['viewModifier'];
			}
			if (isset($this->mediaTypes[$this->mediaType]['html'])) {
				$this->htmlRender = $this->mediaTypes[$this->mediaType]['html'];
			}
		}

		$filterChain->run();
	}

	/**
	 * The filter method for 'putOnly' filter.
	 * This filter reports an error if the applied action is receiving a non-PUT request.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @throws CHttpException if the current request is not a PUT request
	 */
	public function filterPutOnly($filterChain)
	{
		if(Yii::app()->getRequest()->getIsPutRequest())
			$filterChain->run();
		else
			throw new CHttpException(405,Yii::t('yii','Your request is not valid.'));
	}

	/**
	 * The method to be called when no acceptable media type has been found.
	 * This method gives us the choice of throwing an exception, using a default or dealing with it in some other way.
	 * @param Array $mediaTypes the array of available media types.
	 */
	public function noAcceptedMedia() {
		Yii::log('No accepted media for header: '.Yii::app()->request->getAcceptTypes(), 'warning', 'EController');
		throw new CHttpException(406, Yii::t('yii','Not Acceptable'));
	}

	/**
	 * Method to perform negotiation between client requested MIME types and
	 * MIME types available for response. Finds the mutually preferred MIME type
	 * @param array $availableTypes map with details of MIME types available for the request
	 * @param array $headerTypes map of MIME types requested by client in order of preference
	 * @return string $key the key for the available MIME type that is mutually preferred
	 */
	public static function preferredMediaType($availableTypes, $headerTypes) {
		$preferred = array();
		foreach ($headerTypes as $type) {
			foreach ($availableTypes as $availableType => $params) {
				if (($params['type'] === $type['type'] || $type['type'] == '*') && ($params['subType'] === $type['subType'] || $type['subType'] == '*') && ($params['baseType'] === $type['baseType']) && !isset($preferred[$availableType])) {
					$quality = isset($params['quality']) ? $params['quality'] : 1;
					$preferred[$availableType] = $quality * $type['params']['q'];
				}
				$availableDec = null;
			}
		}
		if (count($preferred) > 0) {
			arsort($preferred);
			reset($preferred);
			return key($preferred);
		}
		return null;
	}

	/**
	 *
	 * Function to cycle through the array of meta tags (both those automatically
	 * generated and those added manually) to insert them into the view to be
	 * rendered.
	 */
	public function doTags() {
		$cs = Yii::app()->clientScript;
		foreach($this->metaTags as $tag) {
			if ($tag !== null) {
				$cs->registerMetaTag($tag['content'], $tag['name'], $tag['httpEquiv'], isset($tag['options']) ? $tag['options'] : array());
			}
		}
		foreach($this->linkTags as $tag) {
			if ($tag !== null) {
				$cs->registerLinkTag($tag['relation'], $tag['type'], $tag['href'], null, isset($tag['options']) ? $tag['options'] : array());
			}
		}
		foreach($this->cssFiles as $tag) {
			if ($tag !== null) {
				$cs->registerCssFile($tag['file'], $tag['media']);
			}
		}
	}

	/**
	 * Method to generate appropriate content type and language headers.
	 */
	public function doContentHeader() {
		$this->headers['Content-Type'] = $this->mediaType.'; charset='.$this->charSet;
		$this->headers['Content-Language'] = Yii::app()->language;
	}

	/**
	 * Method to cycle through all headers included in the header array and print them.
	 * Calls the content header generation method first.
	 */
	public function printHeaders() {
		$this->doContentHeader();
		foreach($this->headers as $type => $value) {
			header($type.($value == '' ? '' : ': '.$value));
		}
	}

	/**
	 * Sets all Html page defaults before rendering the page (if rendering Html) and prints all headers.
	 * @see CController::beforeRender()
	 * @return boolean whether to continue rendering the page or not
	 */
	protected function beforeRender($view) {
		if (parent::beforeRender($view)) {
			if ($this->mediaType !== null && isset($this->mediaTypes[$this->mediaType]) && isset($this->mediaTypes[$this->mediaType]['html']) && $this->mediaTypes[$this->mediaType]['html'] === true) {
				//$this->doDefaultTags();
				//$this->doDefaultCss();
					$this->doTags();
			}
			$this->printHeaders();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Modifies the name of the view to append a string according to the MIME type rendering should generate.
	 * @param string $view
	 * @return string name of view file appropriate for content type
	 */
	protected function prepareRender($view) {
		if ($this->viewModifier !== '') {
			if ($this->layout != false) {
				$this->layout .= $this->viewModifier;
			}
			$view .= $this->viewModifier;
		}
		return $view;
	}

	/**
	 * Overridden method in order to make use of any view modifier for MIME type.
	 * @see CController::renderPartial()
	 */
	public function renderPartial($view, $data=null, $return=false, $processOutput=false) {
		return parent::renderPartial($this->prepareRender($view), $data, $return, $processOutput);
	}

	/**
	 * Convenience method to render a partial page for an AJAX request with all the processing of a complete page.
	 * Also converts XHtml type to Html type since Xhtml is not a valid type for a partially rendered Html page.
	 * @see EHttpController::renderPartial()
	 */
	public function renderAjax($view, $data=null, $return=false, $processOutput=false) {
		/* application/xhtml+xml is not a valid type for a partially rendered html page */
		if ($this->mediaType === 'application/xhtml+xml') {
			$this->mediaType = 'text/html';
			$this->prolog = $this->mediaTypes['text/html']['prolog'];
		}
		$this->printHeaders();
		$this->renderPartial($view, $data, $return, $processOutput);
	}

	/**
	 * Overridden method in order to make use of any view modifier for MIME type.
	 * @see CController::render()
	 */
	public function render($view, $data = null, $return = false) {
		return parent::render($this->prepareRender($view), $data, $return);
	}
}
