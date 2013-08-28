<?php

/**
 * Handles imports through a XML file
 *
 * @package application.api
 * @author Integry Systems
 *
 */


class ApiController extends BaseController
{
	// where to look for API model classes
	protected $loadModelsFrom = array('application.model.datasync.api');

	private $modelApi = null;

	public function indexAction()
	{
		return new ActionRedirectResponse('api', 'doc');
	}

	public function xmlAction()
	{
		$this->user->allowBackendAccess();

		try {
			$model = $this->loadModelApi();
			$apiActionName = $model->getApiActionName();
			if($model->respondsToApiAction($apiActionName))
			{
				if (!$model->isAuthorized())
				{
					throw new Exception('Unauthorized');
				}

				//echo '<br />[executing '.$model->getClassName().'Api->'.$apiActionName.'()]<br />';
				return $model->$apiActionName();
			} else {
				if($apiActionName == '')
				{
					throw new Exception('Failed to detect model API action name');
				} else {
					throw new Exception('Model '.$model->getClassName().' does not support '.$apiActionName);
				}
			}

		} catch(Exception $e) {
			$xml = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
			$xml->addChild('error', $e->getMessage());
			if($this->application->isDevMode())
			{
				$xmlTrace = $xml->addChild('trace');
				foreach($e->getTrace() as $row)
				{
					$line = array();
					if(isset($row['line']))
					{
						$line[] = '['.$row['line'].']';
					} else {
						$line[] = '[ - ]';
					}

					if(isset($row['file']))
					{
						$line[] = '['.$row['file'].']';
					} else {
						$line[] = '[ - ]';
					}

					$line[] = ' [';
					if(isset($row['class']))
					{
						$line[] = $row['class'].'::';
					}
					if(isset($row['function']))
					{
						$line[] = $row['function'].'(..)';
					}
					$line[] = ']';
					$xmlTrace->addChild('r',  implode(' ',$line));
				}
			}
			return new SimpleXMLResponse($xml);
			//return new RawResponse($e->getMessage());
		}
	}

	/**
	 *	@role backend
	 */
	public function docAction()
	{
		$this->loadLanguageFile('backend/Settings/API');

		$response = new ActionResponse();
		$response->set('classes', $this->getDocInfo());
		$response->set('authMethods', ModelApi::getAuthMethods($this->application));
		return $response;
	}

	/**
	 *	@role backend
	 */
	public function docviewAction()
	{
		$docinfo = $this->getDocInfo();
		$className = $this->request->gget('class');

		if (!empty($docinfo[$className]))
		{
			$info = $docinfo[$className];
		}
		else
		{
			throw new ApplicationException('API class ' . $className . ' not found');
		}

		$response = new ActionResponse('info', $info);
		$response->set('className', $className);
		return $response;
	}

	public function docactionAction()
	{
		$response = $this->docview();

		$info = $response->get('info');
		$action = $this->request->gget('actn');

		$xmlSamples = $this->getXMLSamples();
		if (isset($xmlSamples[$info['path']][$action]))
		{
			$samples = array();
			foreach ($xmlSamples[$info['path']][$action] as $example => $foo)
			{
				list($foo, $xml) = explode('?xml=', $example);
				$parts = explode(' | ', $xml);
				$xml = array_shift($parts);
				$comments = array_pop($parts);

				$samples[] = array('xml' => $xml, 'formatted' => $this->formatXmlString($xml), 'comments' => $comments);
			}
			$response->set('xmlSamples', $samples);
		}

		// get search field names
		$inst = $info['inst'];
		$this->request->set(ApiReader::API_PARSER_CLASS_NAME, 'Xml' . get_class($inst) . 'Reader');
		$inst->loadRequest(false);

		$this->loadLanguageFile('backend/CsvImport');
		$this->loadLanguageFile('backend/UserGroup');
		$this->loadLanguageFile('backend/' . $inst->getClassName());

		if ('list' == $action)
		{
			$searchFields = array();;
			foreach ($inst->getParser()->getValidSearchFields($inst->getClassName()) as $key => $field)
			{
				$fieldName = $field[0]->toString();
				$translation = $this->translate($fieldName);
				$searchFields[$fieldName] = array('field' => $key);
				if ($translation != $fieldName)
				{
					$searchFields[$fieldName]['descr'] = $translation;
				}
			}

			$response->set('searchFields', $searchFields);
		}

		if (in_array($action, array('create', 'update')) && ($importer = $inst->getImportHandler()))
		{
			$createFields = array();
			$className = $inst->getClassName();
			foreach ($importer->getFields() as $groupName => $fields)
			{
				$apiFields = array();
				foreach ($fields as $field => $translation)
				{
					list($class, $column) = explode('.', $field);
					if ($class == $className)
					{
						$field = $column;
					}

					$field = str_replace('.', '_', $field);
					$apiFields[$field] = $translation;
				}

				$createFields[$this->translate($groupName)] = $apiFields;
			}

			$response->set('createFields', $createFields);
		}

		$response->set('action', $action);
		return $response;
	}

	public function docAuthAction()
	{
		$this->loadLanguageFile('backend/Settings/API');

		$class = $this->request->gget('class');
		$this->application->loadPluginClass('application.model.datasync.api.auth', $class);

		$reflection = new ReflectionClass($class);
		$comment = $reflection->getDocComment();

		$expr = "/\/\*\*|\s\*\s|@.*\n|\*\//";
        $comment = htmlspecialchars(trim(preg_replace($expr, '', $comment)));

        $response = new ActionResponse('class', $class);
        $response->set('comment', $comment);

        return $response;
	}

	private function formatXmlString($xml)
	{
		// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
		$xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

		// now indent the tags
		$token      = strtok($xml, "\n");
		$result     = ''; // holds formatted version as it is built
		$pad        = 0; // initial indent
		$matches    = array(); // returns from preg_matches()

		// scan each line and adjust indent based on opening/closing tags
		while ($token !== false) :

		// test for the various tag states

		// 1. open and closing tags on same line - no change
		if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
		  $indent=0;
		// 2. closing tag - outdent now
		elseif (preg_match('/^<\/\w/', $token, $matches)) :
		  $pad--;
		// 3. opening tag - don't pad this one, only subsequent tags
		elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
		  $indent=1;
		// 4. no indentation needed
		else :
		  $indent = 0;
		endif;

		// pad the line with the required number of leading spaces
		$line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
		$result .= $line . "\n"; // add to the cumulative result, with linefeed
		$token   = strtok("\n"); // get the next token
		$pad    += $indent; // update the pad size for subsequent lines
		endwhile;

		return $result;
		}

	private function getXMLSamples()
	{
		$samples = array();
		$levelPointers = array();
		$contents = explode("\n", file_get_contents(ClassLoader::getRealPath('doc.api') . '.txt'));
		foreach ($contents as $line)
		{
			$level = 0;
			while (substr($line, 0, 1) == '	')
			{
				$level++;
				$line = substr($line, 1);
			}

			$line = trim($line);
			if (!$line)
			{
				continue;
			}

			if (!$level)
			{
				$samples[$line] = array();
				$levelPointers[$level] =& $samples[$line];
			}

			if (isset($levelPointers[$level - 1]))
			{
				$levelPointers[$level - 1][$line] = array();
				$levelPointers[$level] =& $levelPointers[$level - 1][$line];
			}
		}

		return $samples;
	}

	private function getDocInfo()
	{
		$classes = $this->getApiClasses();
		$methods = array();

		foreach ($classes as $class => $file)
		{
			$classes[$class] = array('file' => $file);

			$reader = 'Xml' . $class . 'Reader';
			$this->application->loadPluginClass('application.model.datasync.api.reader', 'Xml' . $class . 'Reader');
			include_once $file;
			$inst = new $class($this->application);

			$classes[$class]['path'] = array_pop(explode('/', call_user_func(array($reader, 'getXMLPath'))));
			$classes[$class]['actions'] = $inst->getActions();
			$classes[$class]['inst'] = $inst;

			if (($i = array_search('filter', $classes[$class]['actions'])) !== false)
			{
				$classes[$class]['actions'][$i] = 'list';
			}
		}

		return $classes;
	}

	private function setModelApi(ModelApi $apiInst)
	{
		$this->modelApi = $apiInst;
		$this->modelApi->loadRequest();
	}

	private function getModelApi()
	{
		if($this->modelApi == null)
		{
			throw new Exception('Cannot find Model to parse request'); // Cannot parse request
		}
		return $this->modelApi;
	}

	private function getApiClasses()
	{
		$classes = array();
		foreach($this->loadModelsFrom as $classLoaderPath)
		{
			$modelFilenames = glob(ClassLoader::getRealPath($classLoaderPath.'.').'*Api.php');

			foreach($modelFilenames as $modelFilename)
			{
				preg_match('/([a-z0-9]+Api)\.php$/i', $modelFilename, $match);
				if(count($match) == 2)
				{
					$classes[$match[1]] = $modelFilename;
				}
			}
		}

		return $classes;
	}

	private function loadModelApi()
	{
		static $triedToLoadModel = false;
		if(!$triedToLoadModel)
		{
			$request = $this->getRequest();
			foreach ($this->getApiClasses() as $modelApiClassName => $path)
			{
				include_once $path;

				if (!class_exists($modelApiClassName, false))
				{
					continue;
				}

				if (call_user_func_array(array($modelApiClassName, "canParse"), array($request)))
				{
					$this->setModelApi(new $modelApiClassName($this->application));
					break;
				}
			}
			$triedToLoadModel = true;
		}
		return $this->getModelApi();
	}
}

?>
