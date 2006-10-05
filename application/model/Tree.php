<?php

/**
 * Class for working with tree structures.
 * @todo Reik padaryti kad rekursiskai trauktu medi pvz tik iki nurodyto lygio
 */
abstract class Tree extends ActiveRecord implements IteratorAggregate {	
	
	public $parents_instance;
	
	private $children = array();	
	
	private static $instances_map = array();

	private $class_name;
		
	public static function defineSchema($className = __CLASS__) {
				
		$schema = self::getSchemaInstance($className);		
		
		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));		
		$schema->registerField(new ARField("parent", Integer::instance()));					
		$schema->registerField(new ARField("lft", Integer::instance()));
		$schema->registerField(new ARField("rgt", Integer::instance()));		
	}		
	
	/**
	 * Gets new tree instance.
	 * @param string className
	 * @param null|int|Tree Parent tree or it's id. If null, has now parent.
	 * <code>
	 * $menu = Tree::getNewTreeInstance("Menu");
	 * ....
	 * $menu->Save();
	 * </code>
	 */
	public static function getNewTreeInstance($className, $parent = null) {
	  		  	
	  	$tree = ActiveRecord::GetNewInstance($className);	
		$tree->class_name = $className;	  	
	  	if ($parent != null) { 	  	
			
			if (is_Object($parent)) {
			  
			 	$tree->parent->set($parent->GetId()); 				 			  	
			} else {
			 
			 	$tree->parent->Set($parent); 				 	
			}		  	
		}	  	
	  	return $tree;  	
	}
	
	/**
	 * Gets name of table.
	 * @return string
	 */	
	protected function getTableName() {
	  	
	  	$schema = self::getSchemaInstance($this->class_name);
		return $schema->getName();
	}
	
	/**
	 * Saves instance to database. Also updates instances map.
	 */	
	public function save() {
		
		if (!$this->lft->hasValue()) {
		  
		  	$db = ActiveRecord::GetDbConnection();		  		  	
		  	
		 	if ($this->parent->get()) {
		 	  	
		 	  	if (!empty(self::$instances_map[$this->parent->get()])) {
			
					$this->parents_instance = self::$instances_map[$this->parent->get()];
					$current_right = $this->parents_instance->rgt->get();	 							  		     
				} else {
				  
				  	$res = $db->executeQuery("SELECT rgt FROM ".$this->getTableName()." WHERE id = ".$this->parent->get());				  
					$res->next();
					$current_right = (int)$res->getInt("rgt");		
				}	  						  
				  		  			  	
				$lft = $current_right;
			  	$rgt = $current_right + 1;
		
				$db->executeUpdate("UPDATE ".$this->getTableName()." SET lft = lft + 2 WHERE lft >= ".$current_right);
			    $db->executeUpdate("UPDATE ".$this->getTableName()." SET rgt = rgt + 2 WHERE rgt >= ".$current_right);
			    
			    foreach (self::$instances_map as $key => $tree) {
				 		 	
				 	if ($tree->lft->get() >= $current_right) {
					   
					   	$tree->lft->Set($tree->lft->get() + 2);
					} 
					
					if ($tree->rgt->get() >= $current_right) {
					  
					  	$tree->rgt->Set($tree->rgt->get() + 2);
					}
				}
				
			} else {
	   
			   	$res = $db->executeQuery("SELECT max(rgt) AS max FROM ".$this->getTableName());
				$res->next();				
				$max = (int)$res->getInt("max");		
	
				$lft = $max + 1;
				$rgt = $max + 2;
			} 			
			
			$this->lft->Set($lft);
		  	$this->rgt->Set($rgt);			  		  	
		
			ActiveRecord::save();		

			if ($this->parents_instance != null) {
			  
				$this->SetParent($this->parents_instance);	
			} else if (!empty(self::$instances_map[0])) {
			  		  
			  	$this->setParent(self::$instances_map[0]);			  	
			}

			self::$instances_map[$this->getId()] = $this;
		} else {
				  
			ActiveRecord::save();
		}		
	}
	
	/**
	 * Modifies parent of tree.	 
	 * @param string $className
	 * @param int|Tree Tree or it's id
	 * @param null|int|Tree Parent tree or it's id. If null, tree will have no parent.
	 */
	public static function modifyTreeParent($className, $tree, $parent) {
	  
	  	$schema = self::getSchemaInstance($className);
		$table = $schema->getName();
		
		if (is_Object($tree)) {

		  	$tree_id = $tree->getId();		  	
		} else {
		  
		  	$tree_id = $tree;		  	
		}

		if (is_Object($parent)) {

	  		$parent_id = $parent->getId();						
	  	} else {
		    
	  		$parent_id = $parent;			
		}
		
		$db = ActiveRecord::GetDbConnection();	
					
		if (!empty(self::$instances_map[$tree_id])) {
		  
		  	$tree_instance = self::$instances_map[$tree_id];
		  	$current_left = $tree_instance->lft->get();
	  		$current_right = $tree_instance->rgt->get();	 
		} else {
		  
		  	$res = $db->executeQuery("SELECT lft, rgt FROM ".$table." WHERE id = ".$tree_id);			

			$res->next();			
			$current_left = (int)$res->getInt("lft");
		  	$current_right = (int)$res->getInt("rgt");
		}
		
		if (empty($parent_id)) {
			  
		 	$res = $db->executeQuery("SELECT max(rgt) AS max FROM ".$table);
			$res->next();				
			$parent_left = (int)$res->getInt("max") + 1;	 	
			$parent_right = (int)$res->getInt("max") + 2;	 					
		} else if (!empty(self::$instances_map[$parent_id])) {
		  		  
		  	$parents_instance = self::$instances_map[$parent_id];		  	
		  	$parent_left = $parents_instance->lft->get();
		  	$parent_right = $parents_instance->rgt->get();
		} else {

			$res = $db->executeQuery("SELECT lft, rgt FROM ".$table." WHERE id = ".$parent_id);				  
			$res->next();			
			$parent_left = (int)$res->getInt("lft");
			$parent_right = (int)$res->getInt("rgt");			
		}	  		  	
	  	 	  	
	  	$diff = $parent_right - $current_left;
	  	
	  	
	  	if ($diff > 0) {	  	
	  		
		  	$db->executeUpdate("UPDATE ".$table." SET lft = lft + ".$diff." WHERE lft >= ".$parent_right." OR ( lft >= ".$current_left." AND rgt <= ".$current_right." ) ");
			$db->executeUpdate("UPDATE ".$table." SET rgt = rgt + ".$diff." WHERE rgt >= ".$parent_right." OR ( lft >= ".$current_left." AND rgt <= ".$current_right." ) ");	
			
			foreach (self::$instances_map as $key => $value) {
			  		  
			 	if ($value->lft->get() >= $parent_right ||
			 	 		($value->lft->get() >= $current_left && $value->rgt->get() <= $current_right)) {
						    
					$value->lft->set($value->lft->get() + $diff);		
				}
				if ($value->rgt->get() >= $parent_right ||
			 	 		($value->lft->get() >= $current_left && $value->rgt->get() <= $current_right)) {
						    
					$value->rgt->set($value->rgt->get() + $diff);		
				}
			}		
				  	
	  	} else {
	  	  	
			$diff2 = $current_right - $current_left + 1;			
			$diff3 = -$diff + $diff2;				
				    
		    $db->executeUpdate("UPDATE ".$table." SET lft = lft + ".$diff2." WHERE lft >= ".$parent_right);		    
			$db->executeUpdate("UPDATE ".$table." SET rgt = rgt + ".$diff2." WHERE rgt >= ".$parent_right);				
			$db->executeUpdate("UPDATE ".$table." SET lft = lft - ".$diff3.", rgt = rgt- ".$diff3."  WHERE lft >= ".($current_left + $diff2)." AND rgt <= ".($current_right + $diff2)."  ");
			
			foreach (self::$instances_map as $key => $value) {
			  		  
			 	if ($value->lft->get() >= $parent_right) {
						    
					$value->lft->set($value->lft->get() + $diff2);		
				}
				if ($value->rgt->get() >= $parent_right) {
						    
					$value->rgt->set($value->rgt->get() + $diff2);		
				}
				if ($value->lft->get() >= $current_left + $diff2 
						&& $value->rgt->get() <= $current_right + $diff2) {
						    
					$value->lft->set($value->lft->get() - $diff3);		
					$value->rgt->set($value->rgt->get() - $diff3);		
				}
				
			}		
		}	  	

		$db->executeUpdate("UPDATE ".$table." SET parent = ".$parent_id."  WHERE id = ".$tree_id);	
		
		if (!empty($tree_instance)) {
		  
			$tree_instance->parent->set($parent_id); 	
			
			echo $tree_instance->name->get().' <br>--||--<br>';

			if (!empty($tree_instance->parents_instance)) {		    
			
			    unset($tree_instance->parents_instance->children[$tree_instance->getId()]);
			}		
			if (!empty($parents_instance)) {			  	
		
				$tree_instance->SetParent($parents_instance);	
			}
		}	  	
	}			
	
	/**
	 * Deletes tree from database. Updates instances map.
	 * @param string $className
	 * @param int|Tree Tree or it's id
	 */
	public static function delete($className, $tree) {
	  
	  	if (is_object($tree)) {
		    			
			$id = $tree->getId();
		} else {
		  
		  	$id = $tree;
		}		
	  
		if (!empty(self::$instances_map[$id])) {
			
			$tree = self::$instances_map[$id];			
			Tree::_delete($className, $tree->lft->get(), $tree->rgt->get());	  
		} else {
		  				
			$tree = ActiveRecord::getInstanceById($className, $id, true);	 	
			Tree::_delete($className, $tree->lft->get(), $tree->rgt->get());

			//gal sitoj vietoj uztektu tiesiog istrinti recordseta ir viskas		
			/*$filter = new ArDeleteFilter();		
			$filter->setCondition(" lft >= ".$tree->lft->get()." AND rgt <= ".$tree->rgt->get());			
			ActiveRecord::deleteRecordSet($className, $filter);*/			
		}  		
	}	
	
	protected static function _delete($className, $lft, $rgt) {

		$filter = new ArDeleteFilter();		
		
		$cond = new EqualsOrMoreCond(new ArFieldHandle($className, "lft"), $lft);
		$cond->addAND(new EqualsOrLessCond(new ArFieldHandle($className, "rgt"), $rgt));
		$filter->setCondition($cond);
		
		ActiveRecord::deleteRecordSet($className, $filter);
		
		foreach (self::$instances_map as $key => $child) {
		
			if ($child->lft->get() >= $lft && $child->rgt->get() <= $rgt) {
			  
			 	unSet(self::$instances_map[$child->getId()]);	  	 
			 	if (!empty($child->parents_instance)) {		    
		
				    unset($child->parents_instance->children[$child->getId()]);
				}
			}
		
		}	  	
	}
		
	/**
	 * Get tree by id.
	 * @param string $className
	 * @param int $id 
	 * @param bool $loadReferencedRecords
	 * @return Tree
	 */
	public static function getTreeInstanceById($className, $id, $loadReferencedRecords = false) {
		
		if (!empty(self::$instances_map[$id])) {
		  	
		  	return self::$instances_map[$id];
		}				
		
		$tree = ActiveRecord::getInstanceById($className, $id, true);	
	
		$filter = new ArSelectFilter();		

		$cond = new EqualsOrMoreCond(new ArFieldHandle($className, "lft"), $tree->lft->get());
		$cond->addAND(new EqualsOrLessCond(new ArFieldHandle($className, "rgt"), $tree->rgt->get()));
		$filter->setCondition($cond);

		$filter->setOrder(new ArFieldHandle($className, "lft"));
		$tree_set = ActiveRecord::getRecordSet($className, $filter, true, $loadReferencedRecords);	  			
							
		foreach ($tree_set as $value) {			
			
			$parent_id = $value->parent->get();
			if (!empty($parent_id) && !empty(self::$instances_map[$parent_id])) {
			  
				$value->setParent(self::$instances_map[$parent_id]);
			}
						
			self::$instances_map[$value->getId()] = $value;		
		}				
		
		return $tree;	
	}
			
	/**
	 * Gets all tree set.
	 * @param string $className
	 * @param bool $loadReferencedRecords
	 * @return Tree with id paramater 0.
	 */	
	public static function getAllTree($className, $loadReferencedRecords = false) {
		
		if (!empty(self::$instances_map[0])) {
		  	
		  	return self::$instances_map[0];
		}
		
		self::$instances_map[0] = ActiveRecord::getInstanceByID($className, 0);		
		
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle($className, "lft"));
		$tree_set = ActiveRecord::getRecordSet($className, $filter, true, $loadReferencedRecords);					
		
		foreach ($tree_set as $value) {
					
			if (!empty(self::$instances_map[$value->getId()])) {
			  
				$value = self::$instances_map[$value->getId()];
			}
						
			$parent_id = $value->parent->get();
			if (!empty($parent_id) && !empty(self::$instances_map[$parent_id])) {
			  
				$value->setParent(self::$instances_map[$parent_id]);
			} else {
							
			  	$value->setParent(self::$instances_map[0]);
			}
						
			self::$instances_map[$value->getId()] = $value;	
		}	
		
		return self::$instances_map[0];
	}
	
	
	/**
	 * Gets parents hierarchy list.
	 * @param string $className
	 * @param int|Tree $tree Tree or it's id. 
	 * @return array
	 */			
	public function getParentsList($className, $tree){
	  
  		if (is_Object($tree)) {

		  	$current_tree = $tree;		  	
		} else {
		  
		  	if (empty(self::$instances_map[$tree])) {
		    		    
				Tree::getAllTree($className);			
			} 
		  
		  	$current_tree = Tree::getTreeInstanceById($className, $tree);		  	
		}

		$list = array();
		while ($current_tree->parent->get() != 0) {
		
			if (empty(self::$instances_map[$current_tree->parent->get()])) {
		    		    
				Tree::getAllTree($className);			
			} 
		  		  
		  	$list[] = Tree::getTreeInstanceById($className, $current_tree->parent->get());	  			  	
		  	$current_tree = Tree::getTreeInstanceById($className, $current_tree->parent->get()); 		  
		}		
		
		return $list;		
	}
		
	/**
	 * Sets parent of tree.
	 * @param $parent Tree Parent tree
	 */				
	protected function setParent($parent) {
	  	  	
		$this->parents_instance = $parent; 				
		$parent->children[$this->getId()] = $this; 				
	}
		
	/** 
	 * Gets count of children.
	 */
	public function getChildrenCount() {
	  
	  	return count($this->children);
	}
	
	/**
	 * Gets children array
	 */
	public function getChildren() {
	  
	  	return $this->children;
	}	
	
	/**
	 * Required definition of interface IteratorAggregate	
	 * @return Iterator
	 */
	public function getIterator() {
	  
		return new ArrayIterator($this->children);
	}
	
	/**
	 *
	 */
	public function getArray(&$array = array(), &$start = 0, $depth = 0) {

		if ($start === 0) {

		  	$array[$start] = $this->toArray();
			$array[$start]['depth'] = $depth;				
			$array[$start]['children_count'] = $this->getChildrenCount();
			$depth ++;
			$start ++;
		}

	  	foreach ($this->getChildren() as $child) {
			
			$array[$start] = $child->toArray();
			$array[$start]['depth'] = $depth;				
			$array[$start]['children_count'] = $child->getChildrenCount();	
			$start ++;	  				
			
			if ($child->getChildrenCount() > 0) {

			  	$child->getArray(&$array, &$start, $depth + 1);
			}
		}	  
		
		if ($depth === 1) {

			return $array;
		}
	}
}

?>