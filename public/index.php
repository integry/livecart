<?php

	/**
	 * livecart front controller
	 *
	 * @author Integry Systems
	 * @package application
	 */

	// session cookie expires in 180 days
	session_set_cookie_params(180 * 60 * 60 * 24);

	require_once(".." . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "ClassLoader.php");

	ClassLoader::mountPath(".", dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

	ClassLoader::import("library.stat.Stat");
	$stat = new Stat(true);

	ClassLoader::import("framework.request.Request");
	ClassLoader::import("framework.request.Router");
	ClassLoader::import("framework.renderer.TemplateRenderer");
	ClassLoader::import("framework.controller.*");
	ClassLoader::import("framework.response.*");
	ClassLoader::import("application.controller.*");
	ClassLoader::import("application.model.system.*");

	// LiveCart request routing rules
	ClassLoader::import("application.configuration.route.backend");
	TemplateRenderer::setCompileDir(ClassLoader::getRealPath("cache.templates_c"));
	$app = Application::getInstance();

	try
	{
		$app->run();
	}
	catch (ActionNotFoundException $e)
	{
		Router::getInstance()->setRequestedRoute('error/404');
		$app->run();
		include("404.php");
	}
	catch (ControllerNotFoundException $e)
	{
		include("404.php");
	}
	catch (AccessDeniedException $e)
	{
		
	}
	catch (ClassLoaderException $e)
	{
		echo "<br/><strong>CLASS LOADER ERROR:</strong> " . $e->getMessage()."\n\n";
		echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
		echo ApplicationException::getFileTrace($e->getTrace());
	}
	catch (ApplicationException $e)
	{
		echo "<pre>"; print_r($_SERVER); echo "</pre>\n\n";
		echo "<br/><strong>APPLICATION ERROR:</strong> " . $e->getMessage()."\n\n";
		echo "<br /><strong>FILE TRACE:</strong><br />\n\n";
		echo ApplicationException::getFileTrace($e->getTrace());
	}
	catch (Exception $e)
	{
		echo "<br/>\n<strong>UNKNOWN ERROR:</strong> " . $e->getMessage()."\n\n";
		// echo "<pre>"; print_r($e); echo "</pre>";
		echo ApplicationException::getFileTrace($e->getTrace());
	}

	if (!empty($_GET['stat']))
	{
		$stat->display();
	}
	
?>