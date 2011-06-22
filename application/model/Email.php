<?php

ClassLoader::import('library.smarty.libs.Smarty', true);

ClassLoader::ignoreMissingClasses();
ClassLoader::import('library.swiftmailer.lib.swift_required', true);
ClassLoader::ignoreMissingClasses(false);

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

	private $html;

	private $recipients;

	private $from;

	private $template;

	private $relativeTemplatePath;

	private $application;

	private $locale;

	private $message;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$this->set('request', $application->getRequest()->toArray());

		$config = $this->application->getConfig();

		ClassLoader::ignoreMissingClasses();

		if ('SMTP' == $config->get('EMAIL_METHOD'))
		{
			$server = $config->get('SMTP_SERVER');
			if (!$server)
			{
				$server = ini_get('SMTP');
			}

			$this->connection = Swift_SmtpTransport::newInstance($server, $config->get('SMTP_PORT'));

			if ($config->get('SMTP_USERNAME'))
			{
				$this->connection->setUsername($config->get('SMTP_USERNAME'));
				$this->connection->setPassword($config->get('SMTP_PASSWORD'));
			}
		}
		else if ('FAKE' == $config->get('EMAIL_METHOD'))
		{
			$this->connection = null;
		}
		else
		{
			$this->connection = Swift_MailTransport::newInstance();
		}

		$this->swiftInstance = Swift_Mailer::newInstance($this->connection);

		$this->message = Swift_Message::newInstance();

		$this->setFrom($config->get('MAIN_EMAIL'), $config->get('STORE_NAME'));

		ClassLoader::ignoreMissingClasses(false);
	}

	public function getMessage()
	{
		return $this->message;
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
		if ($name)
		{
			$name = '"' . $name . '"';
		}

		$this->message->setFrom(array($email => $name));
	}

	public function resetRecipients()
	{
		$headers = $this->message->getHeaders();
		$headers->remove('To');
		$headers->remove('Cc');
		$headers->remove('Bcc');
	}

	public function setTo($emailAddresses, $name = null)
	{
		foreach(explode(',', $emailAddresses) as $email)
		{
			if ($name)
			{
				$name = '"' . $name . '"';
			}

			$this->message->addTo($email, $name);
		}
	}

	public function setCc($email, $name)
	{
		$this->message->addCc($email, $name);
	}

	public function setBcc($email, $name)
	{
		$this->message->addBcc($email, $name);
	}

	public function setTemplate($templateFile)
	{
		$this->relativeTemplatePath = $templateFile;

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
			if (substr($templateFile, 0, 7) == 'module/')
			{
				$parts = explode('/', $templateFile, 3);
				$module = $parts[1];
				$path = $parts[2];

				$paths = array(
								'storage.customize.view.email.' . $locale . '.' . $templateFile,
								'module.' . $module . '.application.view.email.' . $locale . '.' . $path,
								'storage.customize.view.email.en.' . $templateFile,
								'module.' . $module . '.application.view.email.en.' . $path,
							);
			}
			else
			{
				$paths = array(
								'storage.customize.view.email.' . $locale . '.' . $templateFile,
								'application.view.email.' . $locale . '.' . $templateFile,
								'storage.customize.view.email.en.' . $templateFile,
								'application.view.email.en.' . $templateFile,
							);
			}

			foreach ($paths as $path)
			{
				$templateFile = array_shift(ClassLoader::mapToMountPoint($path)) . '.tpl';

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
		if (!$user->isLoaded())
		{
			$user->load();
		}

		$user->resetArrayData();

		$array = $user->toArray();
		$this->locale = $user->locale->get();
		$this->set('user', $array);
		$this->setTo($array['email'], $array['fullName']);
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getLocale()
	{
		return $this->locale ? $this->locale : $this->application->getLocaleCode();
	}

	public function send()
	{
		ClassLoader::ignoreMissingClasses();

		$this->application->processInstancePlugins('email-prepare-send', $this);
		$this->application->processInstancePlugins('email-prepare-send/' . $this->relativeTemplatePath, $this);

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

			$smarty->disableTemplateLocator();
			$text = $smarty->fetch($this->template);
			$smarty->enableTemplateLocator();

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

		$this->application->processInstancePlugins('email-before-send', $this);
		$this->application->processInstancePlugins('email-before-send/' . $this->relativeTemplatePath, $this);

		$this->message->setSubject($this->subject);

		if ($this->html)
		{
			$this->message->setBody($this->html, 'text/html');
		}

		if ($this->text)
		{
			if (!$this->html)
			{
				$this->message->setBody($this->text, 'text/plain');
			}
			else
			{
				$this->message->addPart($this->text, 'text/plain');
			}
		}

		if (!$this->text && !$this->html)
		{
			return false;
		}

		try
		{
			$res = $this->swiftInstance->send($this->message);
			ClassLoader::ignoreMissingClasses(false);
		}
		catch (Exception $e)
		{
			$this->application->processInstancePlugins('email-fail-send/' . $this->relativeTemplatePath, $this, array('exception' => $e));
			$this->application->processInstancePlugins('email-fail-send', $this, array('exception' => $e));
			ClassLoader::ignoreMissingClasses(false);
			return false;
		}

		$this->application->processInstancePlugins('email-after-send/' . $this->relativeTemplatePath, $this);
		$this->application->processInstancePlugins('email-after-send', $this);

		return $res;
	}
}

?>