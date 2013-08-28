<?php



/**
 * Manage design themes
 *
 * @package application.controller.backend
 * @author Integry Systems
 */
class ThemeController extends StoreManagementController
{
	// for theme copying
	private $fromTheme;
	private $toTheme;

	public function index()
	{
		$themes = array_merge(array('barebone' => 'barebone'), array_diff($this->application->getRenderer()->getThemeList(), array('barebone')));
		unset($themes['default'], $themes['default-3column'], $themes['light'], $themes['light-3column']);
		$response = new ActionResponse();
		$response->set('themes', json_encode($themes));
		$response->set('addForm', $this->buildForm());
		$response->set('maxSize', ini_get('upload_max_filesize'));
		$response->set('importForm', $this->buildImportForm());
		$response->set('copyForm', $this->buildCopyForm());

		return $response;
	}

	public function edit()
	{
		$theme = new Theme($this->request->gget('id'), $this->application);
		$arr = $theme->toArray();

		$form = $this->buildSettingsForm();
		$form->setData($arr);

		foreach ($theme->getParentThemes() as $key => $parent)
		{
			$form->set('parent_' . ($key + 1), $parent);
		}

		$response = new ActionResponse();
		$response->set('theme', $arr);
		$response->set('form', $form);
		$response->set('themes', $this->application->getRenderer()->getThemeList());
		return $response;
	}

	public function saveSettings()
	{
		$themes = array();
		for ($k = 1; $k <= 3; $k++)
		{
			if ($theme = $this->request->gget('parent_' . $k))
			{
				$themes[] = $theme;
			}
		}

		$inst = new Theme($this->request->gget('id'), $this->application);
		$inst->setParentThemes($themes);
		$inst->saveConfig();

		return new JSONResponse(false, 'success', $this->translate('_theme_saved'));
	}

	public function add()
	{
		$inst = new Theme($this->request->gget('name'), $this->application);

		$errors = array();
		$validator = $this->buildValidator();
		$validator->isValid();

		if ($inst->isExistingTheme())
		{
			$validator->triggerError('name', $this->translate('_err_theme_exists'));
		}

		if ($errors = $validator->getErrorList())
		{
			return new JSONResponse(array('errors' => $errors));
		}
		else
		{
			$inst->create();
			return new JSONResponse($inst->toArray(), 'success', $this->translate('_theme_created'));
		}
	}

	public function delete()
	{
		$inst = new Theme($this->request->gget('id'), $this->application);
		if ($inst->isCoreTheme())
		{
			return new JSONResponse($inst->toArray(), 'failure', $this->translate('_err_cannot_delete_core_theme'));
		}
		else
		{
			$inst->delete();
			return new JSONResponse($inst->toArray(), 'success', $this->maketext('_theme_deleted', array($inst->getName())));
		}
	}

	public function colors()
	{
		$inst = new Theme($this->request->gget('id'), $this->application);

		$response = new ActionResponse();
		$response->set('config', $this->getParsedStyleConfig($inst));
		$response->set('form', $this->buildColorsForm($inst));
		$response->set('measurements', $this->getSelectOptions(array('', 'auto', 'px', '%', 'em')));
		$response->set('borderStyles', $this->getSelectOptions(array('', 'hidden', 'dotted', 'dashed', 'solid', 'double', 'groove', 'ridge', 'inset', 'outset')));
		$response->set('textStyles', $this->getSelectOptions(array('', 'none', 'underline')));
		$response->set('bgRepeat', $this->getSelectOptions(array('repeat', 'no-repeat', 'repeat-x', 'repeat-y')));
		$response->set('bgPosition', $this->getSelectOptions(array('left top', 'left center', 'left bottom', 'center top', 'center center', 'center bottom', 'right top', 'right center', 'right bottom')));
		$response->set('theme', $this->request->gget('id'));
		return $response;
	}

	private function getSelectOptions($options)
	{
		$out = array();
		foreach ($options as $opt)
		{
			$out[$opt] = $this->translate($opt);
		}

		return $out;
	}

	public function saveColors()
	{
		$theme = $this->request->gget('id');
		$css = new EditedCssFile($theme);
		$code = $this->request->gget('css');

		// process uploaded files
		$filePath = ClassLoader::getRealPath('public.upload.theme.' . $theme . '.');
		if (!file_exists($filePath))
		{
			mkdir($filePath, 0777, true);
			chmod($filePath, 0777);
		}

		foreach ($_FILES as $var => $file)
		{
			if (!$file['name'])
			{
				continue;
			}

			$name = $var . '_' . $file['name'];
			move_uploaded_file($file['tmp_name'], $filePath . $name);
			$code = str_replace('url("' . $var . '")', 'url(\'../theme/' . $theme .'/' . $name . '\')', $code);
		}

		$code = preg_replace('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/e', '"#" . str_pad(dechex(\\1), 2, "0", STR_PAD_LEFT) . str_pad(dechex(\\2), 2, "0", STR_PAD_LEFT) . str_pad(dechex(\\3), 2, "0", STR_PAD_LEFT)', $code);

		$css->setCode($code);
		$res = $css->save();

		return new ActionRedirectResponse('backend.theme', 'cssIframe', array('query' => array('theme' => $theme, 'saved' => true)));
	}

	public function cssIframe()
	{
		$this->setLayout('empty');

		$theme = $this->request->gget('theme');
		$css = new EditedCssFile($theme);

		if (!$css->getCode())
		{
			$css->setCode(' ');
			$css->save();
		}

		$response = new ActionResponse();
		$response->set('theme', $theme);
		$response->set('file', $css->getFileName());
		return $response;
	}

	private function getParsedStyleConfig(Theme $theme)
	{
		$themeName = $theme->getName();
		$conf = array();
		foreach ($theme->getStyleConfig() as $name => $sectionData)
		{
			$section = array();
			$open = true;
			if ('-' == $name[0])
			{
				$open = false;
				$name = substr($name, 1);
			}

			$section['name'] = $this->translate($name);
			$section['open'] = $open;

			$properties = array();
			foreach ($sectionData as $name => $value)
			{
				$property = array('var' => $name, 'name' => $this->translate($name), 'id' => $themeName . '_' . $name);
				$parts = explode(' _ ', $value);
				$property['type'] = array_shift($parts);
				$property['selector'] = array_shift($parts);
				$property['append'] = str_replace('__', ';', array_shift($parts));

				if ($property['append'])
				{
					// determines whether the auto-append properties need to be set
					$property['append'] .= '; richness: 100;';
				}

				$properties[] = $property;
			}

			$section['properties'] = $properties;

			$conf[] = $section;
		}

		return $conf;
	}

	public function copyTheme()
	{
		$res = $this->doCopyTheme();
		return new JSONResponse
		(
			array_key_exists('id', $res) ? array('id' => $res['id']) : null,
			array_key_exists('status', $res) ? $res['status'] : 'failure',
			array_key_exists('message', $res) ? $res['message'] : null
		);
	}

	private function doCopyTheme()
	{
		ClassLoader::importNow('application.helper.CopyRecursive');

		$request = $this->getRequest();
		$this->fromTheme = $request->gget('id');
		$this->toTheme = $request->gget('name');
		$files = $this->getThemeFiles($this->fromTheme);
		$copyFiles = $this->getThemeFiles($this->toTheme, false);
		$baseDir = ClassLoader::getBaseDir();
		foreach ($files as $key => $orginalFileName)
		{
			if (array_key_exists($key, $copyFiles))
			{
				$copyToFileName = $copyFiles[$key];
			}
			else if (preg_match('/public.?upload.?css.?delete/',$orginalFileName))
			{
				// orginal theme files matching glob('public/upload/css/delete/<theme>-*.php')
				// get copyTo file name by replacing
				$copyToFileName = str_replace('public/upload/css/delete/'.$this->fromTheme.'-', 'public/upload/css/delete/'.$this->toTheme.'-', $orginalFileName);
				$copyFiles[] = $copyToFileName;
			}
			else
			{
				continue; // only if new type of files added in themes
			}
			copyRecursive($baseDir.DIRECTORY_SEPARATOR.$orginalFileName,
				$baseDir.DIRECTORY_SEPARATOR.$copyToFileName, array($this, 'onThemeFileCopied'));
		}
		return array('status'=>'success', 'id'=>$this->toTheme,
			'message'=>$this->maketext('_theme_copied', array($this->fromTheme, $this->toTheme)));
	}

	public function onThemeFileCopied($file)
	{
		if (preg_match('/\.(tpl|css)$/i',$file))
		{
			file_put_contents($file,
				str_replace(
					'/'.$this->fromTheme.'/',
					'/'.$this->toTheme.'/',
					file_get_contents($file)
				)
			);
		}
	}

	public function import()
	{
		$this->setLayout('iframeJs');
		$response = new ActionResponse();
		$res = $this->doImport();
		foreach($res as $key=>$value)
		{
			$response->set($key, $value);
		}
		return $response;
	}

	private function doImport()
	{
		require_once(ClassLoader::getRealPath('library.pclzip') . '/pclzip.lib.php');
		$request = $this->getRequest();
		$validator = $this->buildImportValidator($request);
		if($validator->isValid() == false)
		{
			return array('status'=>'failure');
		}
		$file = $_FILES['theme'];

		if (!$file['name'] || $file['error'] != 0)
		{
			return array('status'=>'failure');
		}
		do
		{
			$path = ClassLoader::getRealPath('cache.tmp.theme_import_' . rand(1, 10000000));
		} while(is_dir($path));

		mkdir($path, 0777, true);
		$zipFilePath = $path.'_archive.zip';
		move_uploaded_file($file['tmp_name'], $zipFilePath);

		$archive = new PclZip($zipFilePath);
		$archive->extract($path);

		if (file_exists($path.DIRECTORY_SEPARATOR.'theme.conf') == false)
		{
			$this->clearImportFiles($path);
			return array('status'=>'failure');
		}
		$ini = parse_ini_file($path.DIRECTORY_SEPARATOR.'theme.conf', true);
		$id = trim($ini['Theme']['name']);
		if (preg_match('/^[\_\-a-zA-Z0-9]{1,}$/', $id) == false) // todo: how to reuse validator for theme name?
		{
			return array('status'=>'failure');
		}
		$files = array_merge(
			array(
				$path . DIRECTORY_SEPARATOR. 'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'theme'.DIRECTORY_SEPARATOR.$id,
				$path . DIRECTORY_SEPARATOR. 'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.$id.'.css',
				$path . DIRECTORY_SEPARATOR. 'storage'.DIRECTORY_SEPARATOR.'customize'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'theme'.DIRECTORY_SEPARATOR.$id
			),
			glob($path . DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'delete'.DIRECTORY_SEPARATOR.$id.'-*.php')
		);

		//print_r($files);

		$baseDir = ClassLoader::getBaseDir();
		$len = strlen($path.DIRECTORY_SEPARATOR);
		foreach ($files as $fn)
		{
			if(file_exists($fn))
			{
				$fn = substr($fn, $len);
				$to = $baseDir.DIRECTORY_SEPARATOR.$fn;
				if (is_dir($to))
				{
					$this->application->rmdir_recurse($to);
				}
				else if (file_exists($to))
				{
					unlink($to);
				}

				if (!file_exists(dirname($to)))
				{
					mkdir(dirname($to), 0777, true);
				}

				rename($path.DIRECTORY_SEPARATOR.$fn, $to);
			}
		}
		$this->clearImportFiles($path);
		return array('id'=>$id, 'status'=>'success');
	}

	private function clearImportFiles($path)
	{
		$this->application->rmdir_recurse($path);
		unlink($path.'_archive.zip');
	}

	private function getThemeFiles($id, $onlyExistingFiles=true)
	{
		if (strlen($id) == 0)
		{
			return null;
		}
		$files =  array_merge(
			array(
				'A' => ClassLoader::getRealPath('public.upload.theme.'.$id),
				'B' => ClassLoader::getRealPath('public.upload.css.'.$id).'.css',
				'C' => ClassLoader::getRealPath('storage.customize.view.theme.'.$id)
			),
			glob(ClassLoader::getRealPath('public.upload.css.delete.').$id.'-*.php')
		);

		// Make paths relative
		// ClassLoader::getRealPath(<p.a.t.h>) returns <base dir> + <path>
		// $len is required to chop off <base dir> part
		$len = strlen(ClassLoader::getBaseDir());

		if ($onlyExistingFiles)
		{
			foreach ($files as &$fn)
			{
				$fn = file_exists($fn) ? substr($fn, $len) : null;
			}
			$files = array_filter($files);
			if (count($files) == 0)
			{
				return null;
			}
		}
		else
		{
			foreach ($files as &$fn)
			{
				$fn = substr($fn, $len);
			}
		}

		return $files;
	}

	public function export()
	{
		require_once(ClassLoader::getRealPath('library.pclzip') . '/pclzip.lib.php');
		$id = $this->getRequest()->get('id');
		$files = $this->getThemeFiles($id);
		if ($files === null)
		{
			return new ActionRedirectResponse('backend.theme', 'index');
		}

		do
		{
			$path = ClassLoader::getRealPath('cache.tmp.theme_export_' . rand(1, 10000000));
		} while(file_exists($path));
		$zipFilePath = $path.'_archive.zip';
		$confFilePath = $path.DIRECTORY_SEPARATOR .'theme.conf';
		foreach(array($zipFilePath, $confFilePath) as $fp)
		{
			if (!is_dir(dirname($fp)))
			{
				mkdir(dirname($fp), 0777, true);
			}
		}
		file_put_contents($confFilePath, sprintf(
			'[Theme]'."\n".
			'name = %s', $id)
		);
		$archive = new PclZip($zipFilePath);
		chdir(ClassLoader::getBaseDir());
		$files[] = $confFilePath;

		$archive->add($files, PCLZIP_OPT_REMOVE_PATH, $path);
		$this->application->rmdir_recurse($path);
		$response = new ObjectFileResponse(ObjectFile::getNewInstance('ObjectFile', $zipFilePath, $id.'.zip'));
		$response->deleteFileOnComplete();

		return $response;
	}

	/**
	 * @return RequestValidator
	 */
	private function buildValidator()
	{
		$validator = $this->getValidator("theme", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate('_err_theme_name_empty')));
		$validator->addFilter("name", new RegexFilter('[^_-a-zA-Z0-9]'));

		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildColorsForm(Theme $theme)
	{
		return new Form($this->buildColorsValidator($theme));
	}

	/**
	 * @return RequestValidator
	 */
	private function buildColorsValidator($theme)
	{
		$validator = $this->getValidator("themeColors", $this->request);

		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildSettingsForm()
	{
		return new Form($this->getValidator("foo", $this->request));
	}

	/**
	 * Builds an theme import form validator
	 *
	 * @return RequestValidator
	 */
	protected function buildImportValidator()
	{
		$validator = $this->getValidator('themeImportValidator', $this->request);

		$uploadCheck = new IsFileUploadedCheck($this->translate(!empty($_FILES['theme']['name']) ? '_err_too_large' :'_err_not_uploaded'));
		$uploadCheck->setFieldName('theme');

		$validator->addCheck('theme', $uploadCheck);

		return $validator;
	}

	/**
	 * Builds a import theme form instance
	 *
	 * @return Form
	 */
	protected function buildImportForm()
	{
		return new Form($this->buildImportValidator());
	}

	/**
	 * @return Form
	 */
	protected function buildCopyForm()
	{
		return new Form($this->buildValidator()); // copy and add actions use the same validator
	}

}

?>