<?php

/**
 * E-mail handler
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>   
 */
class Email
{
    public static $connection;
    
    private $swiftInstance;
    
    private $values = array();
    
    private $subject;

    private $text;

    private $recipients;
    
    private $from;
    
    private $template;
    
    private $isHtml = false;
        
    public function __construct()
    {
        ClassLoader::import('library.swiftmailer.Swift');
        ClassLoader::import('library.swiftmailer.Swift.Connection.NativeMail');
        ClassLoader::import('library.swiftmailer.Swift.Connection.SMTP');
        ClassLoader::import('library.swiftmailer.Swift.Message');
                
        if (!self::$connection)
        {
            self::$connection = new Swift_Connection_NativeMail();
//            self::$connection = new Swift_Connection_SMTP(ini_get('SMTP'));
        }
        
        $this->swiftInstance = new Swift(self::$connection);
        $this->recipients = new Swift_RecipientList();
        
        $config = Config::getInstance();
        $this->setFrom($config->getValue('MAIN_EMAIL'), $config->getValue('STORE_NAME'));
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
    
    public function setValue($key, $value)
    {
        $this->values[$key] = $value;
    }
    
    public function setUser(User $user)
    {
        $array = $user->toArray();
        $this->setValue('user', $array);
        $this->setTo($array['email'], $array['fullName']);
    }
    
    public function send()
    {
        if ($this->template)
        {
            ClassLoader::import('library.smarty.libs.Smarty');
        
            $renderer = new TemplateRenderer(Router::getInstance());
            $smarty = TemplateRenderer::getSmartyInstance();

            $smarty->compile_dir = ClassLoader::getRealPath('cache.templates_c');
            $smarty->template_dir = ClassLoader::getRealPath('application.view');
    
            foreach ($this->values as $key => $value)
            {
                $smarty->assign($key, $value);
            }

            $smarty->assign('config', Config::getInstance()->toArray());
            
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