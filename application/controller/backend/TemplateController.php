<?php


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
	public function initAction()
	{
		parent::init();
		$this->application->setTheme('');
	}

	public function indexAction()
	{
		$files = Template::getFiles();

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

	public function editAction()
	{
		$template = new Template($this->getFileName());
		$response = new ActionResponse();
		$response->set('tabid', $this->getRequest()->get('tabid'));
		$response->set('fileName', $template->getFileName());
		$response->set('form', $this->getTemplateForm($template));
		$response->set('code', base64_encode($template->getCode()));
		$response->set('template', $template->toArray());
		$response->set('themes', $this->application->getRenderer()->getThemeList());
		$response->set('theme', $this->request->gget('theme'));

		return $response;
	}

	public function templateDataAction()
	{
		$request = $this->getRequest();
		$theme = $request->gget('theme');
		$version = $request->gget('version');
		$template = new Template($this->getFileName(), strlen($theme) ? $theme : '', $version);
		return new JSONResponse($template->toArray());
	}
	

	public function addAction()
	{
		$response = $this->edit();
		$response->get('form')->getValidator()->addCheck('fileName', new IsNotEmptyCheck($this->translate('_file_name_empty')));
		$response->set('tabid', $this->getRequest()->get('tabid'));
		return $response;
	}

	public function editEmailAction()
	{
		$template = new EmailTemplate($this->getFileName());

		// base language other than English?
		$langTemplate = $template->getLangTemplate($this->application->getDefaultLanguageCode());
		if ($langTemplate->getBody())
		{
			$template = $langTemplate;
		}

		$template->getOtherLanguages();

		$fileName = $template->getFileName();
		$response = new ActionResponse();
		$response->set('tabid', $this->getRequest()->get('tabid'));
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

	public function editPopupAction()
	{
	   return $this->edit();
	}

	public function emailAction()
	{
		$files = Template::getFiles();

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

		$response = new ActionResponse();
		$response->set('categories', json_encode($files));
		return $response;
	}

	/**
	 * @role save
	 */
	public function saveAction()
	{
		$request = $this->getRequest();
		$code = $request->gget('code');

		if ($this->request->gget('fileName'))
		{
			$fileName = $this->request->gget('fileName');
			if (strtolower(substr($fileName, -4)) != '.tpl')
			{
				$fileName .= '.tpl';
			}
			$template = new Template($fileName, $this->request->gget('theme'));
		}
		else
		{
			$template = new Template($this->getFileName(), $this->request->gget('theme'));
		}

		$origPath = $this->getFileName();
		if ($template->isCustomFile() && !$this->request->gget('new') && ($template->getFileName() != $origPath) && !$this->request->gget('theme'))
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
			return new JSONResponse(array('template' => $template->toArray(), 'isNew' => $this->request->gget('new')), 'success', $this->translate('_template_has_been_successfully_updated'));
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_update_template'));
		}
	}

	public function deleteAction()
	{
		$template = new Template($this->getFileName());
		$template->delete();
		

		return new JSONResponse(false, 'success', $this->translate('_template_has_been_successfully_deleted'));
	}

	/**
	 * @role saveEmail
	 */
	public function saveEmailAction()
	{
		$file = str_replace('\\', '/', $this->getFileName());
		$template = new EmailTemplate($file);
		$template = $template->getLangTemplate($this->application->getDefaultLanguageCode());
		$template->setSubject($this->request->gget('subject'));
		$template->setBody($this->request->gget('body'));
		$template->setHTML($this->request->gget('html'));
		$res = $template->save();

		if (substr($file, 0, 11) != 'email/block')
		{
			foreach ($this->application->getLanguageArray() as $lang)
			{
				$langTemplate = $template->getLangTemplate($lang);

				if ($this->request->gget('body_' . $lang) || $this->request->gget('subject_' . $lang))
				{
					$langTemplate->setSubject($this->request->gget('subject_' . $lang, $this->request->gget('subject')));
					$langTemplate->setBody($this->request->gget('body_' . $lang, $this->request->gget('body')));
					$langTemplate->setHTML($this->request->gget('html_' . $lang));
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

	public function emptyPageAction()
	{
		return new ActionResponse();
	}

	private function getFileName()
	{
		return array_shift(explode(',', $this->request->gget('file')));
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