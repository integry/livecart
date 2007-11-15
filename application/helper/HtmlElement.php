<?php

/**
 * Renders HTML element code
 *
 * Usage example:
 * <code>
 * 	$a = new HtmlElement('a');
 * 	$a->setAttribute('href', 'http://integry.net');
 * 	$a->setAttribute('onClick', "return confirm('Really go there?')");
 * 	$a->setContent('Click me, please!');
 * 	echo $a->render();
 * </code>
 *
 * @package application.helper
 * @author Integry Systems
 */
class HtmlElement
{
	/**
	 * Element type (e.g. a, div, span)
	 */
  	private $tag;
  	
	/**
	 * Element attribute values (e.g href, class, id)
	 */
  	private $attributes = array();
  	
	/**
	 * Content between element opening and closing tags (e.g. <a href="">CONTENT HERE</a>)
	 */
  	private $content;
	  
	/**
	 * Constructor
	 * 
	 * @param string $tag Element tag name (e.g. a, div, span)
	 */
	public function __construct($tag)
  	{
		$this->tag = $tag;		
	}
	
	/**
	 * Sets element attribute value
	 * 
	 * @param string $attribute Attribute name (e.g. class, id, href)
	 * @param string $value Attribute value
	 */
	public function setAttribute($attribute, $value)
	{
	  	if (!$value)
	  	{
			unset($this->attributes[$attribute]);
		}
		
		$this->attributes[$attribute] = $value;
	}
	
	/**
	 * Sets element content
	 * 
	 * @param string $content
	 */
	public function setContent($content)
	{
	  	$this->content = $content;
	}
	
	/**
	 * Generates HTML code
	 * 
	 * @return string Element HTML code
	 */
	public function render()
	{
		$item = $this->getParts();
		
		if ($this->content)
		{
		  	$item[] = '>' . $this->content . '</' . $this->tag . '>';
		} 
		else
		{
		  	$item[] = '/>';
		}

		return implode(' ', $item);
	}

	public function getContent()
	{
	  	return $this->content;
	}

	/**
	 * Generates HTML code for opening tag
	 * 
	 * @return string Element HTML code
	 */
	private /* useless */ function renderOpeningTag()
	{
		$item = $this->getParts();
		$item[] = '>';

		return implode(' ', $item);
	}
	
	private function getParts()
	{
		$item = array();
		$item[] = '<' . $this->tag;

		foreach ($this->attributes as $attr => $value)
		{
		  	$value = str_replace('"', '&quot;', $value);
			$item[] = $attr . '="' . htmlspecialchars($value, ENT_NOQUOTES) .'"';
		}
		return $item;	  
	}

}

?>