<?php

class LivePaginator extends \Phalcon\Paginator\Adapter\Model
{
	public function getNumPages()
	{
		return ceil($this->_config['data']->count() / $this->_limitRows);
	}
	
	public function getCompactNumbers()
	{
		$page = $this->_config['page'];
		if (!$page)
		{
			$page = 1;
		}
		
		$pages = array();
		$total = $this->getNumPages();
		for ($i = $page - 3; $i <= min($page + 2, $total); $i++)
		{
			if ($i > 0)
			{
				$pages[] = $i;
			}
		}
		
		if ($pages[0] > 2)
		{
			$pages = array_merge(array(1, null), $pages);
		}
		else if ($pages[0] > 1)
		{
			$pages = array_merge(array(1), $pages);
		}

		if ($i <= $total - 1)
		{
			$pages = array_merge($pages, array(null, $total));
		}
		else if ($i < $total - 1)
		{
			$pages = array_merge($pages, array($total));
		}
		
		if ($pages[count($pages) - 1] != $total)
		{
			$pages[] = $total;
		}
		
		return $pages;
	}
	
	public function getCurrentPage()
	{
		return $this->_config['page'];
	}
	
	public function getPrev()
	{
		if ($this->getCurrentPage() > 1)
		{
			return $this->getCurrentPage() - 1;
		}
	}
	
	public function getNext()
	{
		if ($this->getCurrentPage() < $this->getNumPages())
		{
			return $this->getCurrentPage() + 1;
		}
	}
	
	public function getFrom()
	{
		return (($this->getCurrentPage() - 1) * $this->_limitRows) + 1;
	}
	
	public function getTo()
	{
		return ($this->getCurrentPage() * $this->_limitRows);
	}
}

?>
