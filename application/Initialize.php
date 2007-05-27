<?php

/**
 *  Initialize framework, load main classes
 */ 

require_once(dirname(dirname(__file__)) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'ClassLoader.php');

ClassLoader::mountPath('.', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

ClassLoader::import('library.stat.Stat');
$stat = new Stat(true);

ClassLoader::import('framework.request.Request');
ClassLoader::import('framework.request.Router');
ClassLoader::import('framework.renderer.TemplateRenderer');
ClassLoader::import('framework.controller.*');
ClassLoader::import('framework.response.*');
ClassLoader::import('application.controller.*');
ClassLoader::import('application.model.*');
ClassLoader::import('application.model.system.*');

// LiveCart request routing rules
ClassLoader::import('application.configuration.route.backend');

// Custom initialization tasks
$custom = ClassLoader::getRealPath('storage.configuration.CustomInitialize') . '.php';
if (file_exists($custom))
{
    ClassLoader::import('storage.configuration.CustomInitialize');    
}

?>