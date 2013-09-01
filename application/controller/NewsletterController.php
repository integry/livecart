<?php


class NewsletterController extends FrontendController
{
	public function unsubscribeAction()
	{
		$email = $this->request->get('email');

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


	}

	public function subscribeAction()
	{
		$email = $this->request->get('email');

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

		$this->set('subscriber', $instance->toArray());
	}

	public function alreadySubscribedAction()
	{

	}

	public function confirmAction()
	{
		$instance = NewsletterSubscriber::getInstanceByEmail($this->request->get('email'));
		if ($instance && ($instance->confirmationCode->get() == $this->request->get('code')))
		{
			$instance->isEnabled->set(true);
			$instance->save();
		}

		$this->set('subscriber', $instance->toArray());
	}

	public function getSubscribeValidatorAction()
	{
		$this->loadLanguageFile('Newsletter');
		$validator = $this->getValidator("newsletterSubscribe", $this->getRequest());
		$validator->add('email', new Validator\PresenceOf(array('message' => $this->translate('_err_email_empty'))));
		$validator->add('email', new Validator\Email(array('message' => $this->translate('_err_invalid_email'))));
		return $validator;
	}

}

?>