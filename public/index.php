<?php

	/**
	 * livecart front controller
	 * 
	 * @author Saulius Rupainis <saulius@integry.net>
	 * @package application
	 */

	require_once(".." . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "ClassLoader.php");
	
	ClassLoader::mountPath(".", dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
	//ClassLoader::mountPath("framework", "C:/projects/framework/");

	ClassLoader::import("library.stat.Stat");
	$stat = new Stat(true);

	ClassLoader::import("framework.*");
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
		include("404.php");
	} 
	catch (ControllerNotFoundException $e) 
	{
		include("404.php");
	}
	catch (ClassLoaderException $e) 
	{
		echo "<br/><strong>CLASS LOADER ERROR:</strong> " . $e->getMessage(); 
		echo "<br /><strong>FILE TRACE:</strong><br />";
		echo $e->getFileTrace();
	}
	catch (ApplicationException $e) 
	{
		echo "<br/><strong>APPLICATION ERROR:</strong> " . $e->getMessage();
		echo "<br /><strong>FILE TRACE:</strong><br />";
		echo $e->getFileTrace();
	} 
	catch (Exception $e) 
	{
		echo "<br/>\n<strong>UNKNOWN ERROR:</strong> " . $e->getMessage();
		echo "<pre>"; print_r($e); echo "</pre>";
	}
	
	if (!empty($_GET['stat']))
	{
		$stat->display();
	}
	
	//echo "<pre>"; print_r($_SERVER); echo "</pre>";
?>