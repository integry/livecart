<?php

ClassLoader::import('library.smarty.libs.Smarty', true);
ClassLoader::import('library.swiftmailer.Swift', true);
ClassLoader::import('library.swiftmailer.Swift.Connection.NativeMail', true);
ClassLoader::import('library.swiftmailer.Swift.Connection.SMTP', true);
ClassLoader::import('library.swiftmailer.Swift.Message', true);

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

	private $isHtml = false;

	private $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
		$this->set('request', $application->getRequest()->toArray());

		$config = $this->application->getConfig();
		$this->application->getLocale()->translationManager()->loadFile('User');

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
		$this->text = $text;
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

	public function setAsHtml()
	{
		$this->isHtml = true;
	}

	public function setTemplate($templateFile)
	{
		if (!file_exists($templateFile))
		{
			$locale = $this->application->getLocale()->getLocaleCode();

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

		$this->template = $templateFile;
	}

	public function set($key, $value)
	{
		$this->values[$key] = $value;
	}

	public function setUser(User $user)
	{
		$array = $user->toArray();
		$this->set('user', $array);
		$this->setTo($array['email'], $array['fullName']);
	}

	public function send()
	{
		if ($this->template)
		{
			$smarty = $this->application->getRenderer()->getSmartyInstance();

			foreach ($this->values as $key => $value)
			{
				$smarty->assign($key, $value);
			}

			$router = $this->application->getRouter();
			$html = $smarty->fetch($this->template);

			// fix URLs
			$html = str_replace('&amp;', '&', $html);

			$parts = explode("\n", $html, 2);
			$this->subject = array_shift($parts);
			$this->text = array_shift($parts);
		}

		$this->text = str_replace("\r", "", $this->text);
		$this->text = str_replace("\n\n\n", "\n\n", $this->text);
		$message = new Swift_Message($this->subject, $this->text);

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