<?php
/**
 * EHttpRequest
 *
 * @author Rupert
 * @copyright 2013 Rupert
 * @licence http://www.opensource.org/licenses/mit-license.php MIT License
 */


/**
 * EHttpRequest enables content negotiation and parses different content types
 *
 * @property const integer the enumerated content type.
 *
 * @author Itcoder
 */
class EHttpRequest extends CHttpRequest
{
	protected $_preferredAcceptTypes = null;
	protected $_contentType = null;
	protected $_OTHER = null;
	public $noCsrfValidationRoutes = array();

	protected $_preferredLanguages = null;

	const CONTENT_TYPE_FORM = 0;
	const CONTENT_TYPE_MULTIFORM = 1;
	const CONTENT_TYPE_JSON = 2;
	const CONTENT_TYPE_OTHER = 3;

	/**
	 * The mime array for the Html MIME type
	 * @const
	 * @return array
	 */
	public static function HTML_TYPE() {
		return array(
			'mime' => 'text/html',
			'type' => 'text',
			'subType' => 'html',
			'baseType' => null,
			'quality' => 0.9,
			'prolog' => '<!DOCTYPE html>',
			'viewModifier' => '',
			'html' => true,
		);
	}

	/**
	 * The mime array for the XHtml MIME type
	 * @const
	 * @return array
	 */
	public static function XHTML_TYPE() {
		return array(
			'mime' => 'application/xhtml+xml',
			'type' => 'application',
			'subType' => 'xhtml',
			'baseType' => 'xml',
			'quality' => 1,
			'prolog' => '<!DOCTYPE html>',
			'viewModifier' => '',
			'html' => true,
		);
	}

	/**
	 * The mime array for the XML MIME type
	 * @const
	 * @return array
	 */
	public static function XML_TYPE() {
		return array(
			'mime' => 'application/xml',
			'type' => 'application',
			'subType' => 'xml',
			'baseType' => null,
			'quality' => 0.9,
			'prolog' => '<?xml version="1.0" encoding="UTF-8"?>',
			'viewModifier' => '_xml',
			'html' => false,
		);
	}

	/**
	 * The mime array for the JSON MIME type
	 * @const
	 * @return array
	 */
	public static function JSON_TYPE() {
		return array(
			'mime' => 'application/json',
			'type' => 'application',
			'subType' => 'json',
			'baseType' => null,
			'quality' => 0.9,
			'prolog' => '',
			'viewModifier' => '_json',
			'html' => false,
		);
	}

	/**
	 * Returns user browser accept languages, null if not present.
	 * @return string user browser accept languages, null if not present
	 */
	public function getAcceptLanguages()
	{
		return isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:null;
	}

	/**
	 * Returns one of the enumerated content types according to the Content-Type header.
	 * @return static enum the content type
	 */
	public function getContentType()
	{
		if ($this->_contentType === null) {
			if ($this->isGetRequest()) {
				$this->_contentType = self::CONTENT_TYPE_FORM;
			}
			else {
				$content = isset($_SERVER['CONTENT_TYPE']) ? $_SESSION['CONTENT_TYPE'] : (isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : null);
				if ($content === null) {
					$this->_contentType = self::CONTENT_TYPE_OTHER;
				}
				else {
					$content = strtolower($content);
					if ($content === 'application/x-www-form-urlencoded') {
						$this->_contentType = self::CONTENT_TYPE_FORM;
					}
					elseif ($content === 'multipart/form-data') {
						$this->_contentType = self::CONTENT_TYPE_MULTIFORM;
					}
					elseif (preg_match('/^application\/([^+;,]+\+json|json)($|[ ,;+]{1})/i',$content)) {
						$this->_contentType = self::CONTENT_TYPE_JSON;
					}
					else {
						$this->_contentType = self::CONTENT_TYPE_OTHER;
					}
				}
			}
		}
		return $this->_contentType;
	}

	/**
	 * Gets a parameter from a request body that is in some format other than standard url-encoded form
	 * On first access, will extract all parameters from the request body.
	 * @param string $name the parameter name
	 * @param mixed $defaultValue the default value if the parameter does not exist.
	 * @return mixed the parameter value
	 * @see getPost
	 * @see getParam
	 */
	public function getOther($name, $defaultValue = null) {
		if ($this->_OTHER === null) {
			if (((isset($_SERVER['REQUEST_METHOD']) && (strcasecmp($_SERVER['REQUEST_METHOD'],'PUT') || strcasecmp($_SERVER['REQUEST_METHOD'],'DELETE'))) || $this->getContentType() === self::CONTENT_TYPE_JSON)) {
				$body = $this->getRawBody();
				$result=array();
				switch ($this->_contentType) {
					case self::CONTENT_TYPE_FORM:
						if(function_exists('mb_parse_str')) {
							mb_parse_str($body, $result);
						}
						else {
							parse_str($body, $result);
						}
						break;
					case self::CONTENT_TYPE_JSON:
						$result = json_decode($body, true);
						break;
					case self::CONTENT_TYPE_MULTIFORM:
						// no parser as yet
					case self::CONTENT_TYPE_OTHER:
						// no parser
					default:
						break;
				}
				$this->_OTHER = $result;
				if ($this->getIsPostRequest() && !$this->getIsDeleteRequest() && !$this->getIsPutRequest()) {
					foreach ($result as $key => $value) {
						if (!isset($_POST[$key])) {
							$_POST[$key] = $value;
						}
					}
				}
				$this->_OTHER = $result;
			}
			else {
				$this->_OTHER = array();
			}
		}
		return isset($this->_OTHER[$name]) ? $this->_OTHER[$name] : $defaultValue;
	}

	/**
	 * Returns the named GET or POST (or PUT or DELETE) parameter value.
	 * If the parameter does not exist, the second parameter to this method will be returned.
	 * GET parameter takes precedence, then the POST parameter then OTHER.
	 * @param string $name the parameter name
	 * @param mixed $defaultValue the default parameter value if the parameter does not exist.
	 * @return mixed the parameter value
	 * @see getQuery
	 * @see getPost
	 */
	public function getParam($name,$defaultValue=null)
	{
		if (isset($_GET[$name])) {
			return $_GET[$name];
		}
		elseif (isset($_POST[$name])) {
			return $_POST[$name];
		}
		else {
			return $this->getOther($name, $defaultValue);
		}
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param mixed $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return mixed the POST parameter value
	 * @see getParam
	 * @see getQuery
	 */
	public function getPost($name,$defaultValue=null)
	{
		return isset($_POST[$name]) ? $_POST[$name] : $this->getOther($name, $defaultValue);
	}

	/**
	 * Parses an HTTP Accept header, returning an array map with all parts of each entry.
	 * Each array entry consists of a map with the type, subType, baseType, initial array position and params, an array map of key-value parameters,
	 * obligatorily including a `q` value (i.e. preference ranking) as a double.
	 * For example, an Accept header value of <code>'application/xhtml+xml;q=0.9;level=1'</code> would give an array entry of
	 * <pre>
	 * array(
	 *        'type' => 'application',
	 *        'subType' => 'xhtml',
	 *        'baseType' => 'xml',
	 *        'position' => 0,
	 *        'params' => array(
	 *            'q' => 0.9,
	 *            'level' => '1',
	 *        ),
	 * )
	 * </pre>
	 *
	 * <b>Please note:</b>
	 * To avoid great complexity, there are no steps taken to ensure that quoted strings are treated properly.
	 * If the header text includes quoted strings containing space or the , or ; characters then the results may not be correct!
	 *
	 * See also {@link http://tools.ietf.org/html/rfc2616#section-14.1} for details on Accept header.
	 * @param string $header the accept header value to parse
	 * @return array the user accepted MIME types.
	 */
	public static function parseAcceptHeader($header)
	{
		$matches=array();
		$accepts=array();
		// get individual entries with their type, subtype, basetype and params
		preg_match_all('/(?:\G\s?,\s?|^)(\w+|\*)\/(\w+|\*)(?:\+(\w+))?|(?<!^)\G(?:\s?;\s?(\w+)=([\w\.]+))/',$header,$matches);
		// the regexp should (in theory) always return an array of 6 arrays
		if(count($matches)===6)
		{
			$i=$x=0;
			$itemLen=count($matches[1]);
			while($i<$itemLen)
			{
				// fill out a content type
				$accept=array(
					'type'=>$matches[1][$i],
					'subType'=>$matches[2][$i],
					'baseType'=>null,
					'position'=>$x++,
					'params'=>array(),
				);
				// fill in the base type if it exists
				if($matches[3][$i]!==null && $matches[3][$i]!=='')
					$accept['baseType']=$matches[3][$i];
				// continue looping while there is no new content type, to fill in all accompanying params
				for($i++;$i<$itemLen;$i++)
				{
					// if the next content type is null, then the item is a param for the current content type
					if($matches[1][$i]===null || $matches[1][$i]==='')
					{
						// if this is the quality param, convert it to a double
						if($matches[4][$i]==='q')
						{
							// sanity check on q value
							$q=(double)$matches[5][$i];
							if($q>1)
								$q=(double)1;
							elseif($q<0)
								$q=(double)0;
							$accept['params'][$matches[4][$i]]=$q;
						}
						else
							$accept['params'][$matches[4][$i]]=$matches[5][$i];
					}
					else
						break;
				}
				// q defaults to 1 if not explicitly given
				if(!isset($accept['params']['q']))
					$accept['params']['q']=(double)1;
				$accepts[] = $accept;
			}
		}
		return $accepts;
	}

	/**
	 * Parses an HTTP Accept-Language header, returning an array map with all parts of each entry.
	 * Each array entry consists of a map with the q value, language code and initial array position.
	 * For example, an Accept-Language header value of <code>'fr;q=0.8'</code> would give an array entry of
	 * <pre>
	 * array(
	 *        'q' => 0.8,
	 *        'language' => 'fr',
	 *        'position' => 0,
	 * )
	 * </pre>
	 *
	 * See also {@link http://tools.ietf.org/html/rfc2616#section-14.1} for details on Accept header.
	 * @param string $header the accept-language header value to parse
	 * @return array the user accepted languages.
	 */
	public static function parseAcceptLanguagesHeader($header)
	{
		$languages=array();
		$matches=array();
		if($n=preg_match_all('/([\w\-_]+)(?:\s*;\s*q\s*=\s*(\d*\.?\d*))?/',$header,$matches))
		{
			for($i=$x=0;$i<$n;++$i)
			{
				$q=(double)1;
				if($matches[2][$i]!=='')
					$q=(double)$matches[2][$i];
				if($q>1)
					$q=(double)1;
				if($q>0)
					$languages[]=array('q'=>(double)$q,'language'=>$matches[1][$i],'position'=>$x++);
			}
		}
		return $languages;
	}

	/**
	 * Compare function for determining the preference of accepted MIME type array maps
	 * See {@link parseAcceptHeader()} for the format of $a and $b
	 * @param array $a user accepted MIME type as an array map
	 * @param array $b user accepted MIME type as an array map
	 * @return integer -1, 0 or 1 if $a has respectively greater preference, equal preference or less preference than $b (higher preference comes first).
	 */
	public static function compareAcceptTypes($a,$b)
	{
		// check for equal quality first
		if($a['params']['q']===$b['params']['q'])
			if(!($a['type']==='*' xor $b['type']==='*'))
				if (!($a['subType']==='*' xor $b['subType']==='*'))
					// finally, higher number of parameters counts as greater precedence
					if(count($a['params'])===count($b['params'])) {
						// then check for initial position
						if ($a['position']===$b['position'])
							return 0;
						else
							// earlier position gives higher priority
							return ($a['position']<$b['position'] ? -1 : 1);
					}
					else
						return count($a['params'])<count($b['params']) ? 1 : -1;
				// more specific takes precedence - whichever one doesn't have a * subType
				else
					return $a['subType']==='*' ? 1 : -1;
			// more specific takes precedence - whichever one doesn't have a * type
			else
				return $a['type']==='*' ? 1 : -1;
		else
			return ($a['params']['q']<$b['params']['q']) ? 1 : -1;
	}

	/**
	 * Compare function for determining the preference of accepted language array maps
	 * See {@link parseLanguageHeader()} for the format of $a and $b
	 * @param array $a user accepted language as an array map
	 * @param array $b user accepted language as an array map
	 * @return integer -1, 0 or 1 if $a has respectively greater preference, equal preference or less preference than $b (higher preference comes first).
	 */
	public static function compareAcceptLanguages($a,$b)
	{
		// check for equal quality first
		if($a['q']===$b['q']) {
			// then check for initial positions
			if ($a['position']===$b['position'])
				return 0;
			else
				// earlier position gives higher priority
				return ($a['position']<$b['position'] ? -1 : 1);
		}
		else
			// higher q value gives earlier position
			return ($a['q']<$b['q']) ? 1 : -1;
	}

	/**
	 * Returns an array of user accepted MIME types in order of preference.
	 * Each array entry consists of a map with the type, subType, baseType and params, an array map of key-value parameters.
	 * See {@link parseAcceptHeader()} for a description of the array map.
	 * @return array the user accepted MIME types, as array maps, in the order of preference.
	 */
	public function getPreferredAcceptTypes()
	{
		if($this->_preferredAcceptTypes===null)
		{
			$accepts=self::parseAcceptHeader($this->getAcceptTypes());
			usort($accepts,array(get_class($this),'compareAcceptTypes'));
			$this->_preferredAcceptTypes=$accepts;
		}
		return $this->_preferredAcceptTypes;
	}

	/**
	 * Returns the user preferred accept MIME type.
	 * The MIME type is returned as an array map (see {@link parseAcceptHeader()}).
	 * @return array the user preferred accept MIME type or false if the user does not have any.
	 */
	public function getPreferredAcceptType()
	{
		$preferredAcceptTypes=$this->getPreferredAcceptTypes();
		return empty($preferredAcceptTypes) ? false : $preferredAcceptTypes[0];
	}

	/**
	 * Returns an array of user accepted languages in order of preference.
	 * The returned language IDs will NOT be canonicalized using {@link CLocale::getCanonicalID}.
	 * @return array the user accepted languages in the order of preference.
	 * See {@link http://tools.ietf.org/html/rfc2616#section-14.4}
	 */
	public function getPreferredLanguages()
	{
		if($this->_preferredLanguages===null)
		{
			$accepts=self::parseAcceptLanguagesHeader($this->getAcceptLanguages());
			usort($accepts,array(get_class($this),'compareAcceptLanguages'));
			$this->_preferredLanguages=$accepts;
		}
		return $this->_preferredLanguages;
	}
	protected function normalizeRequest() {
		parent::normalizeRequest();
		$route = Yii::app()->getUrlManager()->parseUrl($this);
		if($this->enableCsrfValidation && false !== array_search($route, $this->noCsrfValidationRoutes))
			Yii::app()->detachEventHandler('onbeginRequest', array($this, 'validateCsrfToken'));
	}
}
