<?php
/**
 * Rank class
 * @author Edward
 */
class Rank {

	// Declare class variables here
	private $rankID = false;
	private $name = false;
	private $level = false;
	private $permissions = array();

	/**
	 * Create a new rank object
	 * @param int $rankID The ID of the rank to create an object of
	 * @global mysqli $_mysql
	 * @author Edward
	 */
	public function __construct($rankID = null) {
		// <editor-fold defaultstate="collapsed" desc="Rank Construct Code">
		global $_mysql; 
		
		// If the rank id is null this is a guest account

		if ($rankID != null) {
			$rankQuery = $_mysql->query("SELECT * FROM `ranks` WHERE `id` = " . $rankID);
		}

		if ($rankID == null || !@$rankQuery->num_rows) { // If this is a guest user or if we tried to find the user and they weren't found
			$this->rankID = false;
			$this->name = false;
			$this->level = false;
		} else {
			$rankData = $rankQuery->fetch_assoc();

			$this->rankID = $rankID;
			$this->name = $rankData['name'];
			$this->level = $rankData['level'];

			if (isset($rankData['permissions']) && !empty($rankData['permissions']) && $rankData['permissions'] != '') {
				$this->permissions = unserialize($rankData['permissions']);
			} else {
				$this->permissions = array();
			}
		}
		// </editor-fold>
	}

	/**
	 * Get this rank's ID
	 * 
	 * @return int $rankID
	 */
	public function rankID() {
		return $this->rankID;
	}

	/**
	 * Get this rank's name
	 * 
	 * @return string $name
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Set this rank's name
	 * 
	 * @param string $name
	 * @global mysqli $_mysql
	 */
	public function setName($name) {
		// <editor-fold defaultstate="collapsed" desc="Set Name Code">
		global $_mysql;
		
		$name = $_mysql->real_escape_string($name);

		if ($name != $this->name) {
			$this->name = $name;

			$rankNameQuery = $_mysql->query("SELECT `id` FROM `ranks` WHERE `name` = '" . $name . "'");

			if (!$rankNameQuery->num_rows) { // If this user name isn't in use
				$_mysql->query("UPDATE `ranks` SET `name` = '" . $name . "' WHERE `id` = " . $this->rankID . "");
			} else { // If this user name is in use, return false
				return false;
			}
		}
		// </editor-fold>
	}

	/**
	 * Get this rank's level
	 * 
	 * @return int $level
	 */
	public function level() {
		return $this->level;
	}

	/**
	 * Set this rank's level
	 * 
	 * @param int $level
	 * @global mysqli $_mysql
	 */
	public function setLevel($level) {
		// <editor-fold defaultstate="collapsed" desc="Set Level Code">
		global $_mysql;
		
		$level = $_mysql->real_escape_string($level);

		if ($level != $this->level) {
			$this->level = $level;

			$rankLevelQuery = $_mysql->query("SELECT `id` FROM `ranks` WHERE `level` = " . $level . "");

			if (!$rankLevelQuery->num_rows) { // If this user name isn't in use
				$_mysql->query("UPDATE `ranks` SET `level` = " . $level . " WHERE `id` = " . $this->rankID . "");
			} else { // If this user name is in use, return false
				return false;
			}
		}
		// </editor-fold>
	}

	/**
	 * Return this rank's permissions
	 * 
	 * @return array $permissions
	 */
	public function permissions() {
		return $this->permissions;
	}

	/**
	 * Check a specific permission
	 * 
	 * @param string $permission The permission to check for
	 * @return boolean
	 */
	public function checkPermission($permission) {
		// <editor-fold defaultstate="collapsed" desc="Check Permission Code">
		$permission = str_replace(" ", "_", $permission); // Replace any spaces with under scores
		// Check in the rank's permissions
		if (array_key_exists($permission, $this->permissions)) {
			return $this->permissions[$permission];
		}

		return false;
		// </editor-fold>
	}

	/**
	 * Save this rank's permissions
	 * 
	 * @param array $permissions
	 * @global mysqli $_mysql
	 */
	public function savePermissions($permissions) {
		// <editor-fold defaultstate="collapsed" desc="Save Permissions Code">
		global $_mysql;
		
		$this->permissions = $permissions;
		$permissions = serialize($permissions);

		$_mysql->query("UPDATE `ranks` SET `permissions` = '" . $permissions . "' WHERE `id` = " . $this->rankID);
		// </editor-fold>
	}
}
?>