<?php

/**
 * E-mail handler
 *
 * @package application/model
 * @author Integry Systems <http://integry.com>
 */
class Email extends \Phalcon\DI\Injectable
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

	private $locale;

	private $message;

	public function __construct(\Phalcon\DI\FactoryDefault $di)
	{
		$this->setDI($di);

		$this->set('request', $_REQUEST);

		$config = $this->config;

		require_once($this->config->getPath('library/swiftmailer/lib/swift_required.php'));

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
			$this->connection = Swift_Connection_Fake::newInstance();
		}
		else
		{
			$this->connection = Swift_MailTransport::newInstance();
		}

		$this->swiftInstance = Swift_Mailer::newInstance($this->connection);

		$this->message = Swift_Message::newInstance();

		$this->setFrom($config->get('MAIN_EMAIL'), $config->get('STORE_NAME'));
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getConnection()
	{
		return $this->connection;
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
			if (preg_match("#[\"|'][[:alpha:]]+://#", $line) === false)
			{
				$line = preg_replace('#([[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/])#', '<a href="\\1">\\1</a>', $line);
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
			
			if(preg_match('#[^ a-zA-Z0-9]#', $name))
			{
				$name = '';
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
								'storage/customize/view/email/' . $locale . '/' . $templateFile,
								'module/' . $module . '/application/view/email/' . $locale . '/' . $path,
								'storage/customize/view/email/en/' . $templateFile,
								'module/' . $module . '.application/view/email/en/' . $path,
							);
			}
			else
			{
				$paths = array(
								'storage/customize/view/email/' . $locale . '/' . $templateFile,
								'application/view/email/' . $locale . '/' . $templateFile,
								'storage/customize/view/email/en/' . $templateFile,
								'application/view/email/en/' . $templateFile,
							);
			}

			foreach ($paths as $path)
			{
				$templateFile = $this->config->getPath($path) . '.tpl';
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

	public function setUser(\user\User $user)
	{
		$this->locale = $user->locale;
		$this->set('usr', $user);
		$this->setTo($user->email, $user->getFullName());
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
		$this->application->processInstancePlugins('email-prepare-send', $this);
		$this->application->processInstancePlugins('email-prepare-send/' . $this->relativeTemplatePath, $this);

		if ($this->template)
		{
			$originalLocale = $this->application->getLocale();
			$emailLocale = \locale\Locale::getInstance($this->getLocale(), $this->getDI());
			$this->application->setLocale($emailLocale);
			$this->application->getLocale()->translationManager()->loadFile('User');
			$this->application->loadLanguageFiles();

			$view = new \Phalcon\Mvc\View\Simple();
			$view->setDI($this->getDI());
			$view->setViewsDir(dirname($this->template) . '/');

			$view->registerEngines(array(
				".tpl" => function($view, $di)
				{
					$volt = new LiveVolt($view, $di);
					$volt->setOptions(array('compiledPath' => __ROOT__ . 'cache/templates/', 'compileAlways' => true));
					return $volt;
				}
			));

			foreach ($this->values as $key => $value)
			{
				$view->$key = $value;
			}

			$view->html = false;

			$text = $view->render(basename($this->template, '.tpl'));

			$parts = explode("\n", $text, 2);
			$this->subject = array_shift($parts);
			$this->setText(array_shift($parts));

			// fix URLs
			$this->text = str_replace('&amp;', '&', $this->text);

			if ($this->application->getConfig()->get('HTML_EMAIL'))
			{
				$view->html = true;
				$view->setViewsDir(dirname($this->template) . '/');
				$text = $view->render(basename($this->template, '.tpl'));

				$parts = explode("\n", $text, 2);
				$html = array_pop($parts);

				$css = new \template\EditedCssFile('email', null, $this->getDI());
				$view->cssStyle = str_replace("\n", ' ', $css->getCode());

				$view->messageHtml = $html;
				$wrapper = $this->getTemplatePath('htmlWrapper');
				$view->setViewsDir(dirname($wrapper) . '/');
				$html = $view->render(basename($wrapper, '.tpl'));

				$this->setHtml($html);
			}

			$this->application->setLocale($originalLocale);
		}

		$this->application->processInstancePlugins('email-before-send', $this);
		$this->application->processInstancePlugins('email-before-send/' . $this->relativeTemplatePath, $this);

		$this->message->setSubject($this->subject);

		$this->text = str_replace('{*html*}', '', $this->text);
		$this->html = str_replace('{*html*}', '', $this->html);

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
		}
		catch (Exception $e)
		{
			$this->application->processInstancePlugins('email-fail-send/' . $this->relativeTemplatePath, $this, array('exception' => $e));
			$this->application->processInstancePlugins('email-fail-send', $this, array('exception' => $e));
			return false;
		}

		$this->application->processInstancePlugins('email-after-send/' . $this->relativeTemplatePath, $this);
		$this->application->processInstancePlugins('email-after-send', $this);

		return $res;
	}
}

?>
