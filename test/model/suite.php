<?php
require_once '../Initialize.php';

//class LiveCartTests extends GroupTest
//{
//    private $directory;
//    private $subSuites = array();
//    private $hasTests = false;
//    
//    public function __construct($directory)
//    {
//        $this->directory = $directory;
//        $this->addTestFiles();
//    }
//    
//    public function addTestFiles()
//    {
//        foreach(scandir($this->directory) as $fileName)
//        {
//            if(preg_match('/^\./', $fileName)) continue;
//            
//            $file = $this->directory . '/' . $fileName;
//            if(is_dir($file)) 
//            {
//                $this->subSuites[$file] = new LiveCartTests($file);
//                continue;
//            } 
//            else if(preg_match('/.*Test.php$/', $fileName))
//            {
//                $this->hasTests = true;
//                $this->addTestFile($file);
//            }
//            
//            
//        }
//    }
//    
//    public function run($reporter)
//    {
//        if($this->hasTests) parent::run($reporter);
//        
//        foreach($this->subSuites as $suite)
//        {
//            $suite->run($reporter);
//        }
//    }
//    
//}
//print_r(debug_backtrace());
//echo count(debug_backtrace());
//
//$liveCartTests = new LiveCartTests(dirname(__FILE__));
//$liveCartTests->run(new HtmlReporter());

$suite = new UTGroupTest('All livecart test');
$suite->addDir(getcwd());
$suite->run();
?>