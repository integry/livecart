<?php

ClassLoader::import('application.model.template.Template');

/**
 * E-mail template file logic - saving and retrieving template code.
 *
 * There are two sets of template files active at the same time:
 *	
 *		1) application.view.email.en - default view template files
 *		2) storage.customize.view.email.en - edited template files.
 *
 * This system allows to modify template files without overwriting the existing ones, among other benefits.
 *
 * Each language has its own email template set
 *
 * @package application.model.template
 * @author Integry Systems <http://integry.com>
 */
class EmailTemplate extends Template
{
    protected $language;

    public function getSubject()
    {
        return array_shift(explode("\n", $this->code, 2));
    }

    public function getBody()
    {
        return array_pop(explode("\n", $this->code, 2));
    }

    public function toArray()
    {
        $ret = array();
        $ret['subject'] = $this->getSubject();
        $ret['body'] = $this->getBody();
        return $ret;
    }
}

?>