<?php
/**
 * Roster class
 *
 * @author Edward
 */
class Roster {
	// Declare class properties
	private $id = false;
	private $name = false;
	private $type = false;

	/**
	 * Roster constructor
	 * 
	 * @global mysqli $_mysql
	 */
	public function __construct($roster = null) {
		// <editor-fold defaultstate="collapsed" desc="Roster Construct Code">
		global $_mysql;
		
		if ($roster != null) { // If we're trying to get a pre-existing roster
			if (is_numeric($roster)) { // If the roster is numeric, it's probably an ID
				$rosterQuery = $_mysql->query("SELECT * FROM `rosters` WHERE `id` = " . $roster);
			} else { // If the user is a string, it's probably a name
				$rosterQuery = $_mysql->query("SELECT * FROM `rosters` WHERE `name` = '" . $_mysql->real_escape_string($roster) . "'");
			}
		}
		
		if ($roster == null || !@$rosterQuery->num_rows) { // If no roster id was given or if the roster MySQL query failed
			$this->id = false;
			$this->name = false;
			$this->type = "new"; // If we weren't able to find the roster in the database or if null was passed, make this a new roster
		} else {
			$rosterData = $rosterQuery->fetch_assoc();

			$this->id = $rosterData['id'];
			$this->name = $rosterData['name'];
			$this->type = "existing"; // If we were able to get this roster's data this is a pre-existing roster
		}
		// </editor-fold>
	}
	
	/**
	 * Get roster ID
	 * 
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}
	
	/**
	 * Get roster name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Set roster name
	 * 
	 * @global mysqli $_mysql
	 * @param string $name
	 */
	public function setName($name) {
		// <editor-fold defaultstate="collapsed" desc="Set Name">
		global $_mysql;
		
		if ($name != $this->name) { // If the new name isn't the same as the old name
			$this->name = $name;
			$_mysql->query("UPDATE `rosters` SET `name` = '" . $name . "' WHERE `id` = " . $this->id);
		}
		// </editor-fold>
	}
	
	/**
	 * Get roster type
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Get roster members
	 * 
	 * @global mysqli $_mysql
	 */
	public function getMembers() {
		// <editor-fold defaultstate="collapsed" desc="Get Members">
		global $_mysql;
		
		$members = array(); // Array to store the roster's members
		$query = $_mysql->query("SELECT `user`, `order` FROM `roster_members` WHERE `roster` = " . $this->id . " ORDER BY `order` ASC");
		
		if ($query->num_rows) { // If there's at least one member
			while ($member = $query->fetch_assoc()) { // For each member
				$thisMember = new User($member['user']);
				$members[] = array("id"=>$thisMember->userID(), "name"=>$thisMember->name(), "order"=>$member['order']);
			}
		}
		
		return $members;
		// </editor-fold>
	}
	
	/**
	 * Display roster
	 * 
	 * @param boolean $returnAsVar
	 * @return string
	 */
	public function display($returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Display Roster">
		$roster = "";
		
		$roster .= "<div class=\"roster\">\n<table>\n";
	
		$roster .= "<tr><td class=\"title\">" . $this->name . "</td></tr>\n";
		
		foreach ($this->getMembers() as $member) { // For each member
			$member = new User($member['id']);
			$roster .= "<tr><td><a href=\"./?p=Profile&u=" . $member->name() . "\">" . $member->name() . "</a></td></tr>\n";
		}
		
		$roster .= "</table>\n</div>\n";
		
		if ($returnAsVar) {
			return $roster;
		} else {
			print($roster);
		}
		// </editor-fold>
	}
	
	/**
	 * Return an array of all rosters
	 * 
	 * @global mysqli $_mysql
	 * @return array
	 */
	public static function getRosters() {
		// <editor-fold defaultstate="collapsed" desc="Get Rosters">
		global $_mysql;
		
		$rosters = array(); // Instantiate an array to store the poll names
		
		$query = $_mysql->query("SELECT `id`, `name` FROM `rosters`");
		
		if ($query->num_rows) { // If there's at least one poll
			while ($roster = $query->fetch_assoc()) { // For each poll
				$rosters[$roster['id']] = $roster['name'];
			}
		}
		return $rosters;
		// </editor-fold>
	}
}
?>