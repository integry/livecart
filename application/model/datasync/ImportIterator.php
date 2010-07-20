<?php

class FooIterator implements Iterator
{
	protected $iteratorKey = 0;
	protected $content;

	public function __construct()
	{
		$args = func_get_args();
		while($a = array_shift($args))
		{
			$this->content[] = $a;
		}
	}

	public function rewind()
	{
		$this->iteratorKey = 0;
	}

	public function valid()
	{
		return $this->iteratorKey < count($this->content);
	}

	public function next()
	{
		$this->iteratorKey++;
	}

	public function key()
	{
		return $this->iteratorKey;
	}

	public function current()
	{
		return $this->content[$this->iteratorKey];
	}
}

?>
