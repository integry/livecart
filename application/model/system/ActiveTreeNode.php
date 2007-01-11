<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * A node of a hierarchial database record structure (preorder tree traversal implementation)
 *
 * <code>
 * //Defining a new class for some table
 * class Catalog
 * {
 *    public static function defineSchema($className = __CLASS__)
 *    {
 *        // <strog>Note:</strong> The folowing methods must be called in an exact order as shown in example.
 *        // 1. Get a schema instance,
 *        // 2. set a schema name,
 *        // 3. call a parent::defineSchema() to register schema fields needed for a hierarchial data structure
 *        // 4. Add your own fields if needed
 *        $schema = self::getSchemaInstance($className);
 *		  $schema->setName("Catalog");
 *
 *		  parent::defineSchema($className);
 *		  $schema->registerField(new ARField("name", Varchar::instance(40)));
 *		  $schema->registerField(new ARField("description", Varchar::instance(200)));
 *    }
 * }
 *
 * // Retrieving a subtree
 * $catalog = ARTreeNode::getRootNode("Catalog");
 * $catalog->loadChildNodes();
 *
 * // or...
 * $catalog = ARTreeNode::getInstanceByID("Catalog", $catalogNodeID);
 * $catalog->loadChildNodes();
 *
 * $childList = $catalog->getChildNodes();
 *
 * // Inserting a new node
 * $parent = getParentNodeFromSomewhere();
 * $catalogNode = ARTreeNode::getNewInstance("Catalog", $parent);
 * $catalogNode->name->set("This is my new catalog node!");
 * $catalogNode->name->set("This node will be created as child for a gived $parent instance");
 * $catalogNode->save();
 *
 * // Deleting a node and all its childs
 * ARTreeNode::deleteByID("Catalog", $catalogNodeID);
 *
 * </code>
 *
 * @link http://www.sitepoint.com/article/hierarchical-data-database/
 * @author Saulius Rupainis <saulius@integry.net>
 * @package application.model.system
 *
 */
class ActiveTreeNode extends ActiveRecordModel
{
	/**
	 * Table field name for left value container of tree traversal order
	 *
	 */
	const LEFT_NODE_FIELD_NAME = 'lft';

	/**
	 * Table field name for right value container of tree traversal order
	 *
	 */
	const RIGHT_NODE_FIELD_NAME = 'rgt';

	/**
	 * The name of table field that represents a parent node ID
	 *
	 */
	const PARENT_NODE_FIELD_NAME = 'parentNodeID';

	/**
	 * Root node ID
	 *
	 */
	const ROOT_ID = 1;

	/**
	 * Child node container
	 *
	 * @var ARTreeNode[]
	 */
	private $childList = null;

	/**
	 * Indicator wheather child nodes are loaded or not for this node
	 *
	 * @var bool
	 */

	const INCLUDE_ROOT_NODE = true;

	/**
	 * Gets a persisted record object
	 *
	 * @param string $className
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param bool $loadChildRecords
	 * @return ARTreeNode
	 */
	public static function getInstanceByID($className, $recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		$instance = parent::getInstanceByID($className, $recordID, $loadRecordData, $loadReferencedRecords);
		return $instance;
	}

	public static function getNewInstance($className, ActiveTreeNode $parentNode)
	{
		$instance = parent::getNewInstance($className);
		$instance->setParentNode($parentNode);
		return $instance;
	}

	/**
	 * Loads a list of child records for this node
	 *
	 * @param bool $loadReferencedRecords
	 * @param bool $loadOnlyDirectChildren
	 * @return ARSet
	 *
	 * @see self::getDirectChildNodes()
	 */
	public function getChildNodes($loadReferencedRecords = false, $loadOnlyDirectChildren = false)
	{
		$this->load();
		$className = get_class($this);

		$nodeFilter = new ARSelectFilter();
		$cond = new OperatorCond(new ArFieldHandle($className, self::LEFT_NODE_FIELD_NAME), $this->getField(self::LEFT_NODE_FIELD_NAME)->get(), ">");
		$cond->addAND(new OperatorCond(new ArFieldHandle($className, self::RIGHT_NODE_FIELD_NAME), $this->getField(self::RIGHT_NODE_FIELD_NAME)->get(), "<"));

		if ($loadOnlyDirectChildren)
		{
			$cond->addAND(new EqualsCond(new ARFieldHandle($className, self::PARENT_NODE_FIELD_NAME), $this->getID()));
		}

		$nodeFilter->setCondition($cond);
		$nodeFilter->setOrder(new ArFieldHandle($className, self::LEFT_NODE_FIELD_NAME));

		$childList = ActiveRecord::getRecordSet($className, $nodeFilter, $loadReferencedRecords);
		return $childList;
	}

	/**
	 * Gets a list of direct child nodes
	 *
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public function getDirectChildNodes($loadReferencedRecords = false)
	{
		return $this->getChildNodes($loadReferencedRecords, false);
	}

	/**
	 * Loads and builds a hierarchial subtree for this node
	 * (loads a list of child records and then builds a hierarchial structure)
	 *
	 * @param bool $loadReferencedRecords
	 */
	public function loadSubTree($loadReferencedRecords = false)
	{
		$childList = $this->getChildNodes($loadReferencedRecords);
		$indexedNodeList = array();
		$indexedNodeList[$this->getID()] = $this;

		foreach ($childList as $child)
		{
			$nodeId = $child->getID();
			$indexedNodeList[$nodeId] = $child;
		}
		foreach ($childList as $child)
		{
			$parentId = $child->getParentNode()->getID();
			$indexedNodeList[$parentId]->registerChildNode($child);
		}
	}

	/**
	 * Loads from database and builds a hierarchial record tree
	 *
	 * @param string $className
	 * @param bool $loadReferencedRecords
	 */
	public static function loadTree($className, $loadReferencedRecords = false)
	{
		$root = self::getRootNode($className);
		$root->loadSubTree($loadReferencedRecords);
	}


	public function save()
	{
		if (!$this->hasID())
		{
			ActiveRecordModel::beginTransaction();
			try
			{
				// Inserting new node
				$parentNode = $this->getField(self::PARENT_NODE_FIELD_NAME)->get();
				$parentNode->load();
				$parentRightValue = $parentNode->getFieldValue(self::RIGHT_NODE_FIELD_NAME);
				$nodeLeftValue = $parentRightValue;
				$nodeRightValue = $nodeLeftValue + 1;

				$tableName = self::getSchemaInstance(get_class($this))->getName();
				$db = self::getDBConnection();

				$rightUpdateQuery = "UPDATE " . $tableName . " SET " . self::RIGHT_NODE_FIELD_NAME . " = "  . self::RIGHT_NODE_FIELD_NAME . " + 2 WHERE "  . self::RIGHT_NODE_FIELD_NAME . ">=" . $parentRightValue;
				$leftUpdateQuery = "UPDATE " . $tableName . " SET " . self::LEFT_NODE_FIELD_NAME . " = "  . self::LEFT_NODE_FIELD_NAME . " + 2 WHERE "  . self::LEFT_NODE_FIELD_NAME . ">=" . $parentRightValue;

				self::getLogger()->logQuery($rightUpdateQuery);
				$db->executeUpdate($rightUpdateQuery);

				self::getLogger()->logQuery($leftUpdateQuery);
				$db->executeUpdate($leftUpdateQuery);

				$this->getField(self::RIGHT_NODE_FIELD_NAME)->set($nodeRightValue);
				$this->getField(self::LEFT_NODE_FIELD_NAME)->set($nodeLeftValue);

				ActiveRecordModel::commit();
			}
			catch (Exception $e)
			{
				ActiveRecordModel::rollback();
				throw $e;
			}
		}
		parent::save();
	}

	public static function deleteByID($className, $recordID)
	{
		$node = self::getInstanceByID($className, $recordID, self::LOAD_DATA);
		$nodeRightValue = $node->getFieldValue(self::RIGHT_NODE_FIELD_NAME);
		$nodeLeftValue = $node->getFieldValue(self::LEFT_NODE_FIELD_NAME);
		$tableName = self::getSchemaInstance($className)->getName();

		ActiveRecordModel::beginTransaction();
		try
		{
			$result = parent::deleteByID($className, $recordID);
			$treeFixQuery = "UPDATE " . $tableName . " SET " . self::RIGHT_NODE_FIELD_NAME . " = "  . self::RIGHT_NODE_FIELD_NAME . " - 2  WHERE "  . self::RIGHT_NODE_FIELD_NAME . ">=" . $nodeRightValue;
			$treeFixQuery = "UPDATE " . $tableName . " SET " . self::LEFT_NODE_FIELD_NAME . " = "  . self::LEFT_NODE_FIELD_NAME . " - 2 WHERE "  . self::LEFT_NODE_FIELD_NAME . ">=" . $nodeLeftValue;

			self::getLogger()->logQuery($treeFixQuery);
			self::getDBConnection()->executeUpdate($treeFixQuery);

			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
		return $result;
	}

	/**
	 * Adds (registers) a child node to this node
	 *
	 * @param ARTreeNode $childNode
	 */
	public function registerChildNode(ActiveTreeNode $childNode)
	{
		if ($this->childList == null)
		{
			$this->childList = new ARSet(null);
		}
		$this->childList->add($childNode);
	}

	/**
	 * Sets a parent node
	 *
	 * @param ARTreeNode $parentNode
	 */
	public function setParentNode(ActiveTreeNode $parentNode)
	{
		$this->getField(self::PARENT_NODE_FIELD_NAME)->set($parentNode);
	}

	/**
	 * Gets a parent node
	 *
	 * @return ARSet
	 */
	public function getParentNode()
	{
		return $this->getField(self::PARENT_NODE_FIELD_NAME)->get();
	}

	/**
	 * Gets a tree root node
	 *
	 * @param string $className
	 * @param bool $loadChildRecords
	 * @return ARTreeNode
	 */
	public static function getRootNode($className)
	{
		return self::getInstanceByID($className, self::ROOT_ID, false, false);
	}


	/*
	public function getPathNodes($includeRootNode = false, $loadReferencedRecords = false)
	{
		$recordSet = self::getRecordSet($className, $filter, $loadReferencedRecords);
		return $recordSet;
	}
	*/

	/**
	 * Gets a hierarchial path to a given tree node
	 *
	 * The result is a sequence of record starting from a root node
	 * E.x. Consider a tree branch: Electronics -> Computers -> Laptops
	 * The path of "Laptops" will be a record set (ARSet) with a following order of records:
	 * 1. Electronics
	 * 2. Computers
	 *
	 * @param bool $includeRootNode
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 * @see ARSet
	 */
	public function getPathNodeSet($includeRootNode = false, $loadReferencedRecords = false)
	{
		$className = get_class($this);
		return ActiveTreeNode::getRecordSet($className, $this->getPathNodeFilter($includeRootNode), $loadReferencedRecords);
	}

	/**
	 * Gets a hierarchial path to a given tree node
	 *
	 * The result is a sequence of record starting from a root node
	 * E.x. Consider a tree branch: Electronics -> Computers -> Laptops
	 * The path of "Laptops" will be a record set (ARSet) with a following order of records:
	 * 1. Electronics
	 * 2. Computers
	 *
	 * @param bool $includeRootNode
	 * @param bool $loadReferencedRecords
	 * @return array
	 */
	public function getPathNodeArray($includeRootNode = false, $loadReferencedRecords = false)
	{
		$className = get_class($this);
		return ActiveTreeNode::getRecordSetArray($className, $this->getPathNodeFilter($includeRootNode), $loadReferencedRecords);
	}

	private function getPathNodeFilter($includeRootNode)
	{
		$className = get_class($this);
		$this->load();
		$leftValue = $this->getFieldValue(self::LEFT_NODE_FIELD_NAME);
		$rightValue = $this->getFieldValue(self::RIGHT_NODE_FIELD_NAME);

		$filter = new ARSelectFilter();
		$cond = new OperatorCond(new ARFieldHandle($className, self::LEFT_NODE_FIELD_NAME), $leftValue, "<");
		$cond->addAND(new OperatorCond(new ARFieldHandle($className, self::RIGHT_NODE_FIELD_NAME), $rightValue, ">"));

		if (!$includeRootNode)
		{
			$cond->addAND(new OperatorCond(new ARFieldHandle($className, "ID"), self::ROOT_ID, "<>"));
		}

		$filter->setCondition($cond);
		$filter->setOrder(new ARFieldHandle($className, self::LEFT_NODE_FIELD_NAME), ARSelectFilter::ORDER_ASC);

		return $filter;
	}

	/**
	 * @todo Implementation
	 *
	 * @param ActiveTreeNode $parentNode
	 * @return bool True on success
	 */
	public function moveTo(ActiveTreeNode $parentNode)
	{
		$this->load();
		$previousParent = $this->getParentNode();
		$previousParent->load();
		$parentNode->load();

		$this->setParentNode($parentNode);
		$subtreeNodeCount = ($this->rgt->get() - $this->lft->get() - 1) / 2 + 1;
		$lftDiff = $parentNode->lft->get() - $this->lft->get();

		$tableName = self::getSchemaInstance(get_class($this))->getName();
		$db = ActiveRecord::getDBConnection();
		ActiveRecord::beginTransaction();
		try
		{
			/*
			$leftShiftFilter = new ARUpdateFilter();
			$leftShiftFilter->addModifier("lft", "lft + " . ($subtreeNodeCount * 2 + 1));
			$leftShiftFilter->addModifier("rgt", "rgt + " . ($subtreeNodeCount * 2 + 1));
			$leftShiftFilter->setCondition(new OperatorCond(new ARFieldHandle("Category", "lft"), $parentNode->lft->get(), ">"));
			ActiveRecord::updateRecordSet("Category", $leftShiftFilter);
			*/
			$leftShitDiff = ($subtreeNodeCount * 2 + 1);
			$leftShiftUpdateQuery = "UPDATE " . $tableName . " SET " . self::LEFT_NODE_FIELD_NAME  . " = " . self::LEFT_NODE_FIELD_NAME  . " + " . $leftShitDiff . "," .
																	   self::RIGHT_NODE_FIELD_NAME  . " = " . self::RIGHT_NODE_FIELD_NAME . " + " . $leftShitDiff .
														     " WHERE " . self::LEFT_NODE_FIELD_NAME . " > " . $parentNode->lft->get();

			self::getLogger()->logQuery($leftShiftUpdateQuery);
			$db->executeUpdate($leftShiftUpdateQuery);

			/*
			$subtreeFixFilter = new ARUpdateFilter();
			$subtreeFixFilter->addModifier("lft", "lft + " . ($lftDiff + 1));
			$subtreeFixFilter->addModifier("rgt", "rgt + " . ($lftDiff + 1));
			$lftFixCond = new OperatorCond(new ARFieldHandle("Category", "lft"), $this->lft->get(), ">=");
			$rgtFixCont = new OperatorCond(new ARFieldHandle("Category", "rgt"), $this->rgt->get(), "<=");
			$lftFixCond->addAND($rgtFixCont);
			$subtreeFixFilter->setCondition($lftFixCond);
			ActiveRecord::updateRecordSet("Category", $subtreeFixFilter);
			*/
			$subtreeFixQuery = "UPDATE " . $tableName . " SET " . self::LEFT_NODE_FIELD_NAME  . " = " . self::LEFT_NODE_FIELD_NAME . " + " . ($lftDiff + 1) . ", " .
																  self::RIGHT_NODE_FIELD_NAME  . " = " . self::RIGHT_NODE_FIELD_NAME . " + " . ($lftDiff + 1) .
												    " WHERE " . self::LEFT_NODE_FIELD_NAME  . " >= " . $this->lft->get() . " AND " . self::RIGHT_NODE_FIELD_NAME  . " <= " . $this->rgt->get();
			self::getLogger()->logQuery($subtreeFixQuery);
			$db->executeUpdate($subtreeFixQuery);

			/*
			$treeFixFilter = new ARUpdateFilter();
			$treeFixFilter->addModifier("rgt", "rgt - " . ($subtreeNodeCount * 2));
			$treeFixFilter->addModifier("lft", "lft - " . ($subtreeNodeCount * 2));
			$treeFixFilter->setCondition(new OperatorCond(new ARFieldHandle("Category", "rgt"), $this->rgt->get(), ">="));
			ActiveRecord::updateRecordSet("Category", $treeFixFilter);
			*/

			$treeFixQuery = "UPDATE " . $tableName . " SET " . self::RIGHT_NODE_FIELD_NAME . " = " . self::RIGHT_NODE_FIELD_NAME . " - " . ($subtreeNodeCount * 2) . ", " .
															   self::LEFT_NODE_FIELD_NAME . " = " . self::LEFT_NODE_FIELD_NAME . " - " . ($subtreeNodeCount * 2) .
													 " WHERE " . self::RIGHT_NODE_FIELD_NAME . " >= " . $previousParent->rgt->get();
			self::getLogger()->logQuery($treeFixQuery);
			$db->executeUpdate($treeFixQuery);

			ActiveRecord::commit();
			return true;
		}
		catch (Exception $e)
		{
			ActiveRecord::rollback();
			return false;
		}
	}

	public static function reindex($className)
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle($className, self::PARENT_NODE_FIELD_NAME));

		$nodeSet = ActiveTreeNode::getRecordSet($className, $filter);

		echo "<pre>"; print_r($nodeSet->toArray()); echo "</pre>";
	}

	/**
	 * Creates an array representation of this node
	 *
	 * @return unknown
	 */
	public function toArray()
	{
		$data = array();
		foreach ($this->data as $name => $field)
		{
			if ($name == self::PARENT_NODE_FIELD_NAME && $field->get() != null)
			{
				$data['parent'] = $field->get()->getID();
			}
			else
			{
				$data[$name] = $field->get();
			}
		}
		$childArray = array();

		if ($this->childList != null)
		{
			foreach ($this->childList as $child)
			{
				$childArray[] = $child->toArray();
			}
			$data['children'] = $childArray;
		}
		return $data;
	}

	/**
	 * Partial schema definition for a hierarchial data storage in a database
	 *
	 * @param string $className
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$tableName = $schema->getName();
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField(self::PARENT_NODE_FIELD_NAME, $tableName, "ID",$className, ARInteger::instance()));
		$schema->registerField(new ARField(self::LEFT_NODE_FIELD_NAME, ARInteger::instance()));
		$schema->registerField(new ARField(self::RIGHT_NODE_FIELD_NAME, ARInteger::instance()));
	}
}

?>