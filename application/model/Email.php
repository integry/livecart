<?php

ClassLoader::import('library.smarty.libs.Smarty', true);
ClassLoader::import('library.swiftmailer.Swift', true);
ClassLoader::import('library.swiftmailer.Swift.Connection.NativeMail', true);
ClassLoader::import('library.swiftmailer.Swift.Connection.SMTP', true);
ClassLoader::import('library.swiftmailer.Swift.Message', true);
ClassLoader::import('application.model.template.EditedCssFile');

/**
 * E-mail handler
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>
 */
class Email
{
	private $connection;

	private $swiftInstance;

	private $values = array();

	private $subject;

	private $text;

	private $recipients;

	private $from;

	private $template;

	private $application;

	private $locale;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$this->set('request', $application->getRequest()->toArray());

		$config = $this->application->getConfig();

		if ('SMTP' == $config->get('EMAIL_METHOD'))
		{
			$server = $config->get('SMTP_SERVER');
			if (!$server)
			{
				$server = ini_get('SMTP');
			}

			$this->connection = new Swift_Connection_SMTP($server, $config->get('SMTP_PORT'));

			if ($config->get('SMTP_USERNAME'))
			{
				$this->connection->setUsername($config->get('SMTP_USERNAME'));
				$this->connection->setPassword($config->get('SMTP_PASSWORD'));
			}
		}
		else if ('FAKE' == $config->get('EMAIL_METHOD'))
		{
			$this->connection = new Swift_Connection_Fake();
		}
		else
		{
			$this->connection = new Swift_Connection_NativeMail();
		}

		$this->swiftInstance = new Swift($this->connection);
		$this->recipients = new Swift_RecipientList();

		$this->setFrom($config->get('MAIN_EMAIL'), $config->get('STORE_NAME'));
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function setText($text)
	{
		$parts = explode('<html>', $text);

		$this->text = array_shift($parts);
		$this->text = str_replace("\r", "", $this->text);
		$this->text = str_replace("\n\n\n", "\n\n", $this->text);

		if ($html = array_shift($parts))
		{
			$this->setHTML($html);
		}
		else
		{
			$this->setHTML($this->text);
		}

		$this->text = strip_tags($this->text);
	}

	public function setHTML($html)
	{
		$lines = explode("\n", $html);
		foreach ($lines as &$line)
		{
			if (ereg("[\"|'][[:alpha:]]+://", $line) === false)
			{
				$line = ereg_replace('([[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/])', '<a href="\\1">\\1</a>', $line);
			}
		}

		$html = implode("\n", $lines);

		// clean up whitespace between HTML tags
		$html = preg_replace('/\>\s+\</', '><', $html);
		$html = preg_replace('/td\>\s+/', 'td>', $html);

		// reduce the number of newlines
		$html = preg_replace('/\n{2,}/', "\n\n", $html);

		$html = str_replace("\n", '<br>', $html);

		$this->html = $html;
	}

	public function setFrom($email, $name)
	{
		$this->from = new Swift_Address($email, $name);
	}

	public function setTo($emailAddresses, $name = null)
	{
		foreach(explode(',', $emailAddresses) as $email)
		{
			$this->recipients->addTo($email, $name);
		}
	}

	public function setCc($email, $name)
	{
		$this->recipients->addCc($email, $name);
	}

	public function setBcc($email, $name)
	{
		$this->recipients->addBcc($email, $name);
	}

	public function setTemplate($templateFile)
	{
		if ($templateFile = $this->getTemplatePath($templateFile))
		{
			$this->template = $templateFile;
		}
	}

	protected function getTemplatePath($templateFile)
	{
		if (!file_exists($templateFile))
		{
			$locale = $this->getLocale();

			// find the email template file
			$paths = array(

							'storage.customize.view.email.' . $locale . '.' . $templateFile,
							'application.view.email.' . $locale . '.' . $templateFile,
							'storage.customize.view.email.en.' . $templateFile,
							'application.view.email.en.' . $templateFile,

						);

			foreach ($paths as $path)
			{
				$templateFile = ClassLoader::getRealPath($path) . '.tpl';

				if (file_exists($templateFile))
				{
					break;
				}
			}

			if (!file_exists($templateFile))
			{
				return false;
			}
		}

		return $templateFile;
	}

	public function set($key, $value)
	{
		$this->values[$key] = $value;
	}

	public function setUser(User $user)
	{
		$array = $user->toArray();
		$this->locale = $user->locale->get();
		$this->set('user', $array);
		$this->setTo($array['email'], $array['fullName']);
	}

	public function getLocale()
	{
		return $this->locale ? $this->locale : $this->application->getLocaleCode();
	}

	public function send()
	{
		if ($this->template)
		{
			$originalLocale = $this->application->getLocale();
			$emailLocale = Locale::getInstance($this->getLocale());
			$this->application->setLocale($emailLocale);
			$this->application->getLocale()->translationManager()->loadFile('User');
			$this->application->loadLanguageFiles();

			$smarty = $this->application->getRenderer()->getSmartyInstance();

			foreach ($this->values as $key => $value)
			{
				$smarty->assign($key, $value);
			}

			$router = $this->application->getRouter();

			$smarty->assign('html', false);
			$text = $smarty->fetch($this->template);

			$parts = explode("\n", $text, 2);
			$this->subject = array_shift($parts);
			$this->setText(array_shift($parts));

			// fix URLs
			$this->text = str_replace('&amp;', '&', $this->text);

			if ($this->application->getConfig()->get('HTML_EMAIL'))
			{
				$smarty->assign('html', true);
				$html = array_pop(explode("\n", $smarty->fetch($this->template), 2));

				$css = new EditedCssFile('email');
				$smarty->assign('cssStyle', str_replace("\n", ' ', $css->getCode()));

				$smarty->assign('messageHtml', $html);
				$html = $smarty->fetch($this->getTemplatePath('htmlWrapper'));

				$this->setHtml($html);
			}

			$this->application->setLocale($originalLocale);
		}

		$message = new Swift_Message($this->subject, $this->text);

		if ($this->html)
		{
			$message->attach(new Swift_Message_Part($this->text));
			$message->attach(new Swift_Message_Part($this->html, 'text/html'));
		}

		try
		{
			$res = $this->swiftInstance->send($message, $this->recipients, $this->from);
		}
		catch (Exception $e)
		{
//			throw $e;
			return false;
		}

		return $res;
	}
}

?>