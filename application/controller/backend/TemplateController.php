<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.template.Template');
ClassLoader::import('application.model.template.EmailTemplate');

/**
 * Template modification
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role template
 */
class TemplateController extends StoreManagementController
{
	public function init()
	{
		parent::init();
		$this->application->setTheme('');
	}

	public function index()
	{
		$files = $this->getFiles();

		unset($files['install'], $files['email']);

		if (!$this->config->get('SHOW_BACKEND_TEMPLATE_FILES'))
		{
			unset($files['backend']);
			unset($files['customize']);
			unset($files['block']['subs']['backend']);
			unset($files['block']['subs']['activeGrid']);
			unset($files['layout']['subs']['backend']);
			unset($files['layout']['subs']['dev']);
			unset($files['layout']['subs']['empty.tpl']);
		}

		return new ActionResponse('categories', json_encode($files));
	}

	public function edit()
	{
		$template = new Template($this->request->get('file'));

		$response = new ActionResponse();
	  	$response->set('fileName', $template->getFileName());
	  	$response->set('form', $this->getTemplateForm($template));
	  	$response->set('code', base64_encode($template->getCode()));
	  	$response->set('template', $template->toArray());
	  	$response->set('themes', $this->application->getRenderer()->getThemeList());
		$response->set('theme', $this->request->get('theme'));
		return $response;
	}

	public function add()
	{
		$response = $this->edit();
		$response->get('form')->getValidator()->addCheck('fileName', new IsNotEmptyCheck($this->translate('_file_name_empty')));
		return $response;
	}

	public function editEmail()
	{
		$template = new EmailTemplate($this->request->get('file'));

		// base language other than English?
		$langTemplate = $template->getLangTemplate($this->application->getDefaultLanguageCode());
		if ($langTemplate->getBody())
		{
			$template = $langTemplate;
		}

		$template->getOtherLanguages();

		$fileName = $template->getFileName();
		$response = new ActionResponse();
	  	$response->set('fileName', $fileName);
	  	$response->set('form', $this->getEmailTemplateForm($template));
	  	$response->set('template', $template->toArray());

	  	if (substr($fileName, 0, 11) == 'email/block')
	  	{
	  		$fileName = 'blocks/' . substr($fileName, 6);
		}
		else
		{
			$fileName = preg_replace('/email\/[a-z]{2}\//', '', $fileName);
		}
		$response->set('displayFileName', $fileName);

		return $response;
	}

	public function editPopup()
	{
	   return $this->edit();
	}

	public function email()
	{
		$files = $this->getFiles();

		$blocks = $files['email']['subs'];
		foreach ($blocks as $key => $data)
		{
			if (substr($key, 0, 5) != 'block')
			{
				unset($blocks[$key]);
			}
		}

		$modules = array();

		if (isset($files['module']))
		{
			foreach ($files['module']['subs'] as $name => $module)
			{
				if (isset($module['subs']['email']))
				{
					$subs = $module['subs']['email']['subs'];
					$modules[$name] = array('id' => 'module/' . $name, 'subs' => $subs['en']['subs']);
				}
			}
		}

		$files = $files['email']['subs']['en']['subs'];
		$files['blocks'] = array('id' => '/', 'subs' => $blocks);

		if ($modules)
		{
			$files['module'] = array('id' => 'module', 'subs' => $modules);
		}
//var_Dump($files['contactForm']);
//var_Dump($files['module']['subs']);
		$response = new ActionResponse();
		$response->set('categories', json_encode($files));
		return $response;
	}

	/**
	 * @role save
	 */
	public function save()
	{
		$code = $this->request->get('code');

		if ($this->request->get('fileName'))
		{
			$fileName = $this->request->get('fileName');
			if (strtolower(substr($fileName, -4)) != '.tpl')
			{
				$fileName .= '.tpl';
			}
			$template = new Template($fileName, $this->request->get('theme'));
		}
		else
		{
			$template = new Template($this->request->get('file'), $this->request->get('theme'));
		}

		$origPath = $this->request->get('file');
		if ($template->isCustomFile() && !$this->request->get('new') && ($template->getFileName() != $origPath) && !$this->request->get('theme'))
		{
			$origPath = Template::getCustomizedFilePath($origPath);
			if (file_exists($origPath))
			{
				unlink($origPath);
			}
		}

		$template->setCode($code);
		$res = $template->save();

		if($res)
		{
			return new JSONResponse(array('template' => $template->toArray(), 'isNew' => $this->request->get('new')), 'success', $this->translate('_template_has_been_successfully_updated'));
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_update_template'));
		}
	}

	public function delete()
	{
		$custPath = Template::getCustomizedFilePath($this->request->get('file'));
		if (file_exists($custPath))
		{
			unlink($custPath);
		}

		return new JSONResponse(false, 'success', $this->translate('_template_has_been_successfully_deleted'));
	}

	/**
	 * @role saveEmail
	 */
	public function saveEmail()
	{
		$file = str_replace('\\', '/', $this->request->get('file'));
		$template = new EmailTemplate($file);
		$template = $template->getLangTemplate($this->application->getDefaultLanguageCode());
		$template->setSubject($this->request->get('subject'));
		$template->setBody($this->request->get('body'));
		$template->setHTML($this->request->get('html'));
		$res = $template->save();

		if (substr($file, 0, 11) != 'email/block')
		{
			foreach ($this->application->getLanguageArray() as $lang)
			{
				$langTemplate = $template->getLangTemplate($lang);

				if ($this->request->get('body_' . $lang) || $this->request->get('subject_' . $lang))
				{
					$langTemplate->setSubject($this->request->get('subject_' . $lang, $this->request->get('subject')));
					$langTemplate->setBody($this->request->get('body_' . $lang, $this->request->get('body')));
					$langTemplate->setHTML($this->request->get('html_' . $lang));
					$langTemplate->save();
				}
				else
				{
					// remove language templates without content
					$custPath = EmailTemplate::getCustomizedFilePath($langTemplate->getFileName());
					if (file_exists($custPath))
					{
						unlink($custPath);
					}
				}
			}
		}

		if($res)
		{
			return new JSONResponse(false, 'success', $this->translate('_template_has_been_successfully_updated'));
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_update_template'));
		}
	}

	public function emptyPage()
	{
		return new ActionResponse();
	}

	private function getFiles()
	{
		$files = Template::getTree();

		$dirs = $this->application->getConfigContainer()->getViewDirectories();
		array_shift($dirs);
		foreach ($dirs as $d)
		{
			$d = $d[1];
			$rel = $this->application->getRenderer()->getRelativeTemplatePath($d);
			$rel = str_replace('application/view', '', $rel);

			$root = array();
			$f =& $root;
			$ids = array();
			foreach (explode('/', $rel) as $part)
			{
				if ($part)
				{
					$ids[] = $part;
					$root[$part] = array('id' => implode('/', $ids), 'subs' => array());
					$root =& $root[$part]['subs'];
				}
			}

			$root = Template::getTree($d, null, $rel);
			$files = array_merge_recursive($files, $f);

			unset($root, $f);
		}

		return $files;
	}

	private function getTemplateForm(Template $template)
	{
		$form = new Form($this->getValidator('template', $this->request));
		$form->setData($template->toArray());
		$form->set('code', '');
		$form->set('fileName', $template->getFileName());
		return $form;
	}

	private function getEmailTemplateForm(EmailTemplate $template)
	{
		$form = new Form($this->getValidator('template', $this->request));
		$form->setData($template->toArray());
		$form->set('code', '');

		foreach ($template->getOtherLanguages() as $lang => $temp)
		{
			$form->set('body_' . $lang, $temp->getBody());
			$form->set('html_' . $lang, $temp->getHTML());
			$form->set('subject_' . $lang, $temp->getSubject());
		}

		return $form;
	}
}

?>