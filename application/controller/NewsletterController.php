<?php

ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.model.newsletter.*');

class NewsletterController extends FrontendController
{
	public function unsubscribe()
	{
		$email = $this->request->gget('email');

		// delete from subscriber table
		$f = new ARDeleteFilter(new EqualsCond(new ARFieldHandle('NewsletterSubscriber', 'email'), $email));
		ActiveRecordModel::deleteRecordSet('NewsletterSubscriber', $f);

		// add user to subscriber table
		if ($user = User::getInstanceByEmail($email))
		{
			$s = NewsletterSubscriber::getNewInstanceByUser($user);
			$s->isEnabled->set(false);
			$s->save();
		}

		return new ActionResponse();
	}

	public function subscribe()
	{
		$email = $this->request->gget('email');

		if (!$this->user->isAnonymous() || User::getInstanceByEmail($email))
		{
			return new ActionRedirectResponse('newsletter', 'alreadySubscribed');
		}

		$validator = $this->getSubscribeValidator();
		if (!$validator->isValid())
		{
			return new ActionRedirectResponse('index', 'index');
		}

		$instance = NewsletterSubscriber::getInstanceByEmail($email);
		if (!$instance)
		{
			$instance = NewsletterSubscriber::getNewInstanceByEmail($email);
		}

		$instance->save();

		$mail = new Email($this->application);
		$mail->setTo($email);
		$mail->setTemplate('newsletter/confirm');
		$mail->set('subscriber', $instance->toArray());
		$mail->set('email', $email);
		$mail->send();

		return new ActionResponse('subscriber', $instance->toArray());
	}

	public function alreadySubscribed()
	{
		return new ActionResponse();
	}

	public function confirm()
	{
		$instance = NewsletterSubscriber::getInstanceByEmail($this->request->gget('email'));
		if ($instance && ($instance->confirmationCode->get() == $this->request->gget('code')))
		{
			$instance->isEnabled->set(true);
			$instance->save();
		}

		return new ActionResponse('subscriber', $instance->toArray());
	}

	public function getSubscribeValidator()
	{
		$this->loadLanguageFile('Newsletter');
		$validator = $this->getValidator("newsletterSubscribe", $this->getRequest());
		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_email_empty')));
		$validator->addCheck('email', new IsValidEmailCheck($this->translate('_err_invalid_email')));
		return $validator;
	}

}

?>