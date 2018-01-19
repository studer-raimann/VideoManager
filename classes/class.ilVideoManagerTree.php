<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/VideoManager/classes/class.ilVideoManagerObject.php');

/**
 * Class ilObjVideoManagerTree
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilVideoManagerTree extends ilTree {

	/**
	 * @var ilVideoManagerTree
	 */
	protected static $instance;
	/**
	 * @var ilDB
	 */
	protected $db;


	/**
	 * Constructor
	 *
	 * @param int $tree_id
	 */
	function __construct($tree_id) {
		parent::__construct($tree_id);
		global $DIC;
		$this->db = $DIC->database();
		$this->setTableNames('vidm_tree', 'vidm_data');
		$this->setObjectTablePK('id');
		$this->setTreeTablePK('tree');
		$this->setRootId(ilVideoManagerObject::__getRootFolder()->getId());
	}

	/**
	 * Get hidden folders recursively
	 */
	function getHiddenNodes($node_id = 0) {
		if(!$node_id) {
			$node_id = $this->getRootId();
		}

		$hidden_folders = array();
		if ($childs = $this->getChildIds($node_id)) {
			foreach($childs as $id => $child) {
				$folder = ilVideoManagerFolder::findOrGetInstance($child);
				if (1 == 2 && $folder->getType() == ilVideoManagerObject::TYPE_FLD) {
					if ($folder->getHidden()) {
						$hidden_folders[] = $child;
						$hidden_folders = array_merge($hidden_folders, $this->getSubTreeIds($child));
					} else {
						$hidden_folders = array_merge($hidden_folders, $this->getHiddenNodes($child));
					}
				}
			}
		}
		return $hidden_folders;
	}

	/**
	 * Get node child ids
	 * @global type $ilDB
	 * @param type $a_node
	 * @return type
	 */
	public function getChildIds($a_node)
	{
		$query = 'SELECT * FROM '.$this->getTreeTable() .
			' WHERE parent = '.$this->db->quote($a_node,'integer').' '.
			'AND tree > '.$this->db->quote(0,'integer');
		$res = $this->db->query($query);

		$childs = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$childs[] = $row->child;
		}
		return $childs;
	}
}