<?php

ClassLoader::import('library.smarty.libs.Smarty');
ClassLoader::import('library.swiftmailer.Swift');
ClassLoader::import('library.swiftmailer.Swift.Connection.NativeMail');
ClassLoader::import('library.swiftmailer.Swift.Connection.SMTP');
ClassLoader::import('library.swiftmailer.Swift.Message');

/**
 * E-mail handler
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>   
 */
class Email
{
    private $connection;
    
    private $swiftInstance;
    
    private $values = array();
    
    private $subject;

    private $text;

    private $recipients;
    
    private $from;
    
    private $template;
    
    private $isHtml = false;
        
    private $application;
        
    public function __construct(LiveCart $application)
    {                
        $this->application = $application;
        
        $this->connection = new Swift_Connection_NativeMail();
//      self::$connection = new Swift_Connection_SMTP(ini_get('SMTP'));
        
        $this->swiftInstance = new Swift(self::$connection);
        $this->recipients = new Swift_RecipientList();
        
        $config = $this->application->getConfig();
        $this->setFrom($config->get('MAIN_EMAIL'), $config->get('STORE_NAME'));
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setText($text)
    {
        $this->text = $text;
    }
    
    public function setFrom($email, $name)
    {
        $this->from = new Swift_Address($email, $name);
    }
    
    public function setTo($email, $name)
    {
        $this->recipients->addTo($email, $name);
    }
    
    public function setCc($email, $name)
    {
        $this->recipients->addCc($email, $name);        
    }
    
    public function setBcc($email, $name)
    {
        $this->recipients->addBcc($email, $name);        
    }

    public function setAsHtml()
    {
        $this->isHtml = true;   
    }
    
    public function setTemplate($templateFile)
    {
        if (!file_exists($templateFile))
        {
            $templateFile = ClassLoader::getRealPath('application.view.email.' . $templateFile) . '.tpl';
            
            if (!file_exists($templateFile))
            {
                return false;                
            }
        }   
        
        $this->template = $templateFile;        
    }
    
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
    
    public function setUser(User $user)
    {
        $array = $user->toArray();
        $this->set('user', $array);
        $this->setTo($array['email'], $array['fullName']);
    }
    
    public function send()
    {
        if ($this->template)
        {        
            $smarty = new Smarty();
            $smarty->compile_dir = ClassLoader::getRealPath('cache.templates_c');
            $smarty->template_dir = ClassLoader::getRealPath('application.view');
    
            foreach ($this->values as $key => $value)
            {
                $smarty->assign($key, $value);
            }

            $smarty->assign('config', self::getApplication()->getConfig()->toArray());
            
            $html = $smarty->fetch($this->template);
            
            $parts = explode("\n", $html, 2);
            $this->subject = array_shift($parts);
            $this->text = array_shift($parts);
        }
        
        $message = new Swift_Message($this->subject, $this->text);
        
        try
        {
            $res = $this->swiftInstance->send($message, $this->recipients, $this->from);
        }
        catch (Exception $e)
        {
//            throw $e;
            return false;      
        }        
        
        return $res;
    }
}

?>