<?php


/**
 * E-mail template file logic - saving and retrieving template code.
 *
 * There are two sets of template files active at the same time:
 *
 *		1) application/view/email/en - default view template files
 *		2) storage/customize/view.email.en - edited template files.
 *
 * This system allows to modify template files without overwriting the existing ones, among other benefits.
 *
 * Each language has its own email template set
 *
 * @package application/model/template
 * @author Integry Systems <http://integry.com>
 */
class EmailTemplate extends Template
{
	protected $language;

	protected $subject;

	protected $body;

	protected $html;

	public function getSubject()
	{
		return array_shift(explode("\n", $this->code, 2));
	}

	private function load()
	{
		if (!$this->hasPlainText())
		{
			$this->body = $this->code;
			$this->html = '';
		}

		if ($this->isFragment())
		{
			$code = $this->code;
		}
		else
		{
			$code = array_pop(explode("\n", $this->code, 2));
		}

		preg_match('/\{if \!\$html\}\s+(.*)\s+{\/if}\{\*html\*\}\s+{if \$html\}\s+(.*)\s+{\/if}\{\*html\*\}/msU', $code, $matches);

		if ($matches)
		{
			$this->body = $matches[1];
			$this->html = $matches[2];
		}
		else
		{
			$this->body = $code;
		}
	}

	public function getBody()
	{
		$this->load();
		return $this->body;
	}

	public function getHTML()
	{
		$this->load();
		return $this->html;
	}

	public function setSubject($subj)
	{
		$this->subject = $subj;
	}

	public function setBody($body)
	{
		$this->body = $body;
	}

	public function setHTML($html)
	{
		$this->html = $html;
	}

	public function save()
	{
		if ($this->html)
		{
			$body = '{if !$html}' . "\n" . $this->body . "\n" . '{/if}{*html*}' . "\n" . '{if $html}' . "\n" . $this->html . "\n" . '{/if}{*html*}';
		}
		else
		{
			$body = $this->body;
		}

		if ($this->isFragment())
		{
			$this->code = $body;
		}
		else
		{
			$this->code = $this->subject . "\n" . $body;
		}

		return parent::save();
	}

	/**
	 *	Determines if the template file is an includable fragment (like signature) instead of a whole e-mail message
	 */
	public function isFragment()
	{
		if (preg_match('/(signature|htmlWrapper|block([\/a-zA-Z]+))\.tpl$/', $this->file))
		{
			return true;
		}

		if ($this->getSubject() == '{if !$html}')
		{
			return true;
		}
	}

	public function hasPlainText()
	{
		if (!preg_match('/(htmlWrapper)\.tpl$/', $this->file))
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
		if (substr($this->file, 0, 11) == 'email/block')
		{
			return $this->file;
		}
		else
		{
			return preg_replace('/email\/[a-z]{2}/', 'email/' . $lang, $this->file);
		}
	}

	public function toArray()
	{
		$ret = parent::toArray();
		$ret['subject'] = $this->getSubject();
		$ret['body'] = $this->getBody();
		$ret['html'] = $this->getHTML();
		$ret['bodyEncoded'] = base64_encode($ret['body']);
		$ret['isFragment'] = $this->isFragment();
		$ret['hasPlainText'] = $this->hasPlainText();
		return $ret;
	}
}

?>