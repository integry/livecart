<?php

ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.model.newsletter.*');

class NewsletterController extends FrontendController
{
	public function unsubscribe()
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

		return new ActionResponse();
	}

}

?>