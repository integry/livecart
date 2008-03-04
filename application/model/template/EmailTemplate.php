<?php

ClassLoader::import('application.model.template.Template');

/**
 * E-mail template file logic - saving and retrieving template code.
 *
 * There are two sets of template files active at the same time:
 *
 *		1) application.view.email.en - default view template files
 *		2) storage.customize.view.email.en - edited template files.
 *
 * This system allows to modify template files without overwriting the existing ones, among other benefits.
 *
 * Each language has its own email template set
 *
 * @package application.model.template
 * @author Integry Systems <http://integry.com>
 */
class EmailTemplate extends Template
{
	protected $language;

	protected $subject;

	protected $body;

	public function getSubject()
	{
		return array_shift(explode("\n", $this->code, 2));
	}

	public function getBody()
	{
		if ($this->isFragment())
		{
			return $this->code;
		}
		else
		{
			return array_pop(explode("\n", $this->code, 2));
		}
	}

	public function setSubject($subj)
	{
		$this->subject = $subj;
	}

	public function setBody($body)
	{
		$this->body = $body;
	}

	public function save()
	{
		if ($this->isFragment())
		{
			$this->code = $this->body;
		}
		else
		{
			$this->code = $this->subject . "\n" . $this->body;
		}

		return parent::save();
	}

	/**
	 *	Determines if the template file is an includable fragment (like signature) instead of a whole e-mail message
	 */
	public function isFragment()
	{
		if (preg_match('/signature\.tpl$/', $this->file))
		{
			return true;
		}
	}

	public function getOtherLanguages()
	{
		$result = array();

		$app = ActiveRecordModel::getApplication();
		foreach ($app->getLanguageArray() as $lang)
		{
			$file = $this->getLangTemplatePath($lang);
			if ($path = self::getRealFilePath($file))
			{
				$result[$lang] = $this->getLangTemplate($lang);
			}
		}

		return $result;
	}

	public function getLangTemplate($lang)
	{
		return new EmailTemplate($this->getLangTemplatePath($lang));
	}

	public function getLangTemplatePath($lang)
	{
		return 'email/' . $lang . '/' . substr($this->file, 9);
	}

	public function toArray()
	{
		$ret = parent::toArray();
		$ret['subject'] = $this->getSubject();
		$ret['body'] = $this->isFragment() ? $this->code : $this->getBody();
		$ret['bodyEncoded'] = base64_encode($ret['body']);
		$ret['isFragment'] = $this->isFragment();
		return $ret;
	}
}

?>