<?php

function backtrace($sError = 'test')
{
   echo "<hr /><div>".$sError."<br /><table border='1'>";
   $sOut=""; $aCallstack=debug_backtrace();
  
   echo "<thead><tr><th>file</th><th>line</th><th>function</th>".
       "</tr></thead>";
   foreach($aCallstack as $aCall)
   {
       if (!isset($aCall['file'])) $aCall['file'] = '[PHP Kernel]';
       if (!isset($aCall['line'])) $aCall['line'] = '';

       echo "<tr><td>{$aCall["file"]}</td><td>{$aCall["line"]}</td>".
           "<td>{$aCall["function"]}</td></tr>";
   }
   echo "</table></div><hr /></p>";
   die();
}

require_once dirname(__FILE__) . "../../../framework/ClassLoader.php";

ClassLoader::mountPath(".", dirname(dirname(dirname(dirname(__file__)))) . '/');
ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.system.*");
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("library.activerecord.ActiveRecord");

//	ActiveRecordModel::setDSN("mysql://root@192.168.1.6/livecart_dev");

?>