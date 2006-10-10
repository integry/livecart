<?php


class Groups extends Tree
{

	/**
	 * Notice: You should't use words "depth", "lft", "rgt" to name fields.
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);

		$schema->setName("Groups");
		$schema->registerField(new ARField("name", Varchar::instance(100)));
		$schema->registerField(new ARField("description", Varchar::instance(200)));
	}

	/**
	 * Shows childrens.
	 */
	public function showChildren($recursive = true, $depth = 0)
	{
		foreach($this->getChildren()as $child)
		{

			for ($i = 0; $i < $depth; $i++)
			{
				echo" &nbsp; &nbsp; &nbsp; ";
			}
			echo $child->name->get()."<br>\n";

			if ($recursive && $child->getChildrenCount() > 0)
			{
				$child->showchildren($recursive, $depth + 1);
			}
		}
		if ($depth == 0)
		{
			echo"----------<br>\n";
		}
	}

}






















?>
