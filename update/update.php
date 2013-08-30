<?php

/**
 *  Handles LiveCart update process when called from command line
 *
 *  @author Integry Systems
 */

// change to application root directory
chdir('..');

// initialize LiveCart
include_once('application/Initialize.php');
session_start();
$livecart = new LiveCart;

// process update

$user = $this->sessionUser->getUser();
$user->allowBackendAccess();
$user->setID(1);
$this->sessionUser->setUser($user);

$controller = new UpdateController($livecart);
$response = $controller->update();

if ($response instanceof RawResponse)
{
	echo $response->getContent() . "\n";
}
elseif ($response instanceof ActionResponse)
{
	foreach ($response->get('progress') as $key => $value)
	{
		echo $key . ': OK' . "\n";
	}

	if ($response->get('errors'))
	{
		echo "\n" . 'Errors:' . "\n\n";

		foreach ($response->get('errors') as $key => $value)
		{
			echo $key . ': ' . $value . "\n";
		}

		echo "\n" . 'Failed to complete update. If you\'re not able to resolve the problems and complete the update successfuly, please contact the LiveCart support team at http://support.livecart.com';
	}
	else
	{
		echo "\n" . 'Update completed successfuly!';
	}
}

?>