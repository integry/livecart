<?php

	/**
	 * livecart application controller
	 * 
	 * @author Saulius Rupainis <saulius.r>
	 */

	require_once(".." . DIRECTORY_SEPARATOR . "framework" . DIRECTORY_SEPARATOR . "ClassLoader.php");
	
	ClassLoader::mountPath(".", dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
	//ClassLoader::mountPath("framework", "C:\\projects\\framework\\");
	
	ClassLoader::import("library.stat.Stat");
	$stat = new Stat(true);
	
	ClassLoader::import("framework.*");
	ClassLoader::import("framework.request.Request");
	ClassLoader::import("framework.request.Router");
	ClassLoader::import("framework.renderer.TemplateRenderer");
	ClassLoader::import("framework.controller.*");
	ClassLoader::import("framework.response.*");
	ClassLoader::import("application.controller.*");
	
	ClassLoader::import("application.configuration.route.backend");
	
	//Router::$baseDir = "k-shop";
	
	TemplateRenderer::setCompileDir(ClassLoader::getRealPath("cache.templates_c"));
	$app = Application::getInstance();
	
	try {
		$app->run();
	} catch (ControllerNotFoundException $e) {
		include("404.html");
		
	} catch (ApplicationException $e) {
		echo "<br/><strong>APPLICATION ERROR:</strong> " . $e->getMessage();
		
	} catch (Exception $e) {
		echo "<br/>\n<strong>UNKNOWN ERROR:</strong> " . $e->getMessage();
		echo "<pre>"; print_r($e); echo "</pre>";
	}
	
	if (!empty($_GET['stat']))
	{
		$stat->display();
	}
?>