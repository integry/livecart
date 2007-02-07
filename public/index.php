<?php
/* temp */
class PHPErrorException extends Exception
{
   private $context = null;
   public function __construct
       ($code, $message, $file, $line, $context = null)
   {       
	   parent::__construct($message, $code);
       $this->file = $file;
       $this->line = $line;
       $this->context = $context;
   }
};

function error_handler($code, $message, $file, $line) {
	if ($code > 4)
   	{
     	return false;	
	}

   throw new PHPErrorException($code, $message, $file, $line);
}

//set_error_handler('error_handler');

	/**
	 * livecart front controller
	 *
	 * @author Saulius Rupainis <saulius@integry.net>
	 * @package application
	 */

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
		include("404.php");
	}
	catch (ControllerNotFoundException $e)
	{
		include("404.php");
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