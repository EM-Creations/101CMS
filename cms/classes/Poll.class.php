<?php
/**
 * Poll class
 *
 * @author Edward McKnight
 */
class Poll {
	// Declare class properties here
	private $id = false;
	private $name = false;
	private $lockType = false;
	private $lock = false;
	private $expires = false;
	private $answers = array();
	private $multipleAnswers = false;
	private $type = false;
	private $enabled = false;

	/**
	 * Create a new Poll Object, by passing either the poll ID or name
	 * 
	 * @param mixed $poll
	 * @global mysqli $_mysql
	 */
	public function __construct($poll = null) {
		// <editor-fold defaultstate="collapsed" desc="Poll Construct Code">
		global $_mysql;
		
		if ($poll != null) { // If we're trying to get a pre-existing poll
			if (is_numeric($poll)) { // If the user is numeric, it's probably an ID
				$pollQuery = $_mysql->query("SELECT * FROM `polls` WHERE `id` = " . $poll);
			} else { // If the user is a string, it's probably a name
				$pollQuery = $_mysql->query("SELECT * FROM `polls` WHERE `name` = '" . $_mysql->real_escape_string($poll) . "'");
			}
		}

		if ($poll == null || !@$pollQuery->num_rows) { // If no poll id was given or if the poll MySQL query failed
			$this->id = false;
			$this->name = false;
			$this->lockType = false;
			$this->lock = false;
			$this->expires = false;
			$this->answers = false;
			$this->multipleAnswers = false;
			$this->type = "new"; // If we weren't able to find the poll in the database or if null was passed, make this a new poll
			$this->enabled = false;
		} else {
			$pollData = $pollQuery->fetch_assoc();

			$this->id = $pollData['id'];
			$this->name = $pollData['name'];
			$this->lockType = $pollData['lock_type'];
			$this->lock = $pollData['lock'];
			$this->type = "existing"; // If we were able to get this poll's data this is a pre-existing poll

			if ($pollData['expires'] == 0) { // If this poll doesn't have an expiry time
				$this->expires = false;
			} else {
				$this->expires = $pollData['expires'];
			}
			
			if ((int) $pollData['multiple_answers'] == 0) {
				$this->multipleAnswers = false;
			} else {
				$this->multipleAnswers = true;
			}
			
			if ((int) $pollData['enabled'] == 0) {
				$this->enabled = false;
			} else {
				$this->enabled = true;
			}

			$this->answers = unserialize($pollData['answers']); // Unserialise the answers
			
			foreach ($this->answers as $key=>$var) { // Ensure there's no hidden characters or whitespace
				if (empty($var)) { // If the answer is empty
					unset($this->answers[$key]); // Get rid of it
				} else { // If the answer is not empty
					preg_replace('/[\x00-\x1F\x80-\xFF]/', "", $this->answers[$key]);
					$this->answers[$key] = trim($this->answers[$key]);
				}
			}
		}
		// </editor-fold>
	}

	/**
	 * Return this poll's name
	 * 
	 * @return string $name
	 */
	public function name() {
		return $this->name;
	}

	/**
	 * Set this poll's name
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

			$nameQuery = $_mysql->query("SELECT `id` FROM `polls` WHERE `name` = '" . $name . "'");

			if (!$nameQuery->num_rows) { // If this poll name isn't in use
				$nameQuery = $_mysql->query("UPDATE `polls` SET `name` = '" . $name . "' WHERE `id` = " . $this->id);
			} else { // If this poll name is in use, return false
				return false;
			}
		}
		// </editor-fold>
	}

	/**
	 * Get when this poll expires
	 * 
	 * @param char $mode
	 * @param string $format Date format
	 * @return mixed $expires
	 * @global Object $_site;
	 */
	public function expires($mode = "r", $format = null) {
		// <editor-fold defaultstate="collapsed" desc="Expires Code">
		global $_site;
		
		if ($this->expires != 0) { // If the expiry isn't set to 0
			if ($mode == "r") { // Raw mode
				return $this->expires;
			} else if ($mode == "d") { // Formatted mode
				if ($format == null) { // If the format hasn't been set, default to using the site's date format
					return date($_site->setting("dateFormat"), $this->expires);
				} else { // if the format has been set, use it
					return date($format, $this->expires);
				}
			}
		} else { // If the expiry is set to 0
			return "Never";
		}
		// </editor-fold>
	}
	
	/**
	 * Set when this poll expires
	 * 
	 * @param int $expires
	 * @global mysqli $_mysql
	 */
	public function setExpires($expires) {
		global $_mysql;
		
		$_mysql->query("UPDATE `polls` SET `expires` = " . (int) $expires . " WHERE `id` = " . $this->id);
		$this->expires = (int) $expires;
	}

	/**
	 * Get whether this poll has expired or not
	 * 
	 * @return boolean $expired
	 */
	public function expired() {
		// <editor-fold defaultstate="collapsed" desc="Expired Code">
		if (time() > $this->expires && $this->expires != 0) { // If the time now is greater than the expiry timestamp
			return true;
		} else {
			return false;
		}
		// </editor-fold>
	}

	/**
	 * Get this poll's ID
	 * 
	 * @return int $id
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Return this poll's answers
	 * 
	 * @param char $mode
	 * @param string $delimiter
	 * @param boolean $returnAsVar
	 * @return mixed $answers
	 */
	public function answers($mode = "r", $delimiter = null, $returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Answers Code">
		if ($mode == "r") {
			$toReturn = array();
		} else {
			$toReturn = "";
		}
		
		if ($mode == "r") { // If we're returning the raw answers
			$toReturn = $this->answers;
		} else if ($mode == "f") { // If we're returning in form output
			// TODO: Poll answers form output
		} else if ($mode == "l") { // Line by line, use delimiter if passed
			if ($delimiter != null) { // If a delimiter has been set
				foreach ($this->answers as $answer) { // For each answer
					$toReturn .= $answer.$delimiter."\n";
				}
			} else { // If a delimiter has not been set
				foreach ($this->answers as $answer) { // For each answer
					$toReturn .= $answer."\n";
				}
			}
		}
		
		if ($returnAsVar) { // If we're returning this as a variable
			return $toReturn;
		} else {
			if ($mode == "r") { // Always return as a variable if outputting as raw
				return $toReturn;
			} else {
				print($toReturn);
			}
		}
		// </editor-fold>
	}
	
	/**
	 * Set this poll's answers
	 * 
	 * @param mixed $answers
	 * @global mysqli $_mysql
	 */
	public function setAnswers($answers) {
		// <editor-fold defaultstate="collapsed" desc="Set Answers Code">
		global $_mysql;
		
		if (is_array($answers)) { // If the answers are in an array format
			$answers = serialize($answers); // Serialise the answers
		}
		
		$answers = $_mysql->real_escape_string($answers);
		
		$_mysql->query("UPDATE `polls` SET `answers` = '" . $answers . "' WHERE `id` = " . $this->id);
		
		$query = $_mysql->query("SELECT `answers` FROM `polls` WHERE `id` = " . $this->id . " LIMIT 1");
		$data = $query->fetch_assoc();
		$this->answers = unserialize($data['answers']);
		// </editor-fold>
	} 
	
	/**
	 * Return whether this is a valid answer or not
	 * 
	 * @param mixed $answer
	 * @return boolean $validAnswer
	 */
	public function isValidAnswer($answer) {
		return in_array($answer, $this->answers);
	}
	
	/**
	 * Return whether this poll allows multiple answers or not
	 * 
	 * @return boolean $multipleAnswersAllowed
	 */
	public function multipleAnswers() {
		return $this->multipleAnswers;
	}
	
	/**
	 * Set whether this poll allows multiple answers
	 * 
	 * @param boolean $multipleAnswers 
	 * @global mysqli $_mysql
	 */
	public function setMultipleAnswers($multipleAnswers) {
		// <editor-fold defaultstate="collapsed" desc="Set Multiple Answers Code">
		global $_mysql;
		
		if ($multipleAnswers) { // If multiple answers is set to true
			$multipleAnswers = 1;
		} else { // If multiple answers is set to false
			$multipleAnswers = 0;
		}
		
		$_mysql->query("UPDATE `polls` SET `multiple_answers` = " . $multipleAnswers . " WHERE `id` = " . $this->id);
		$this->multipleAnswers = ($multipleAnswers ? true : false);
		// </editor-fold>
	}
	
	/**
	 * Return whether this poll is enabled or not
	 * 
	 * @return boolean $enabled
	 */
	public function enabled() {
		return $this->enabled;
	}
	
	/**
	 * Set whether this poll is enabled or not
	 * 
	 * @param boolean $enabled
	 * @global mysqli $_mysql
	 */
	public function setEnabled($enabled) {
		// <editor-fold defaultstate="collapsed" desc="Set Enabled Code">
		global $_mysql;
		
		if ($enabled) { // If enabled is set to true
			$enabled = 1;
		} else { // If enabled is set to false
			$enabled = 0;
		}
		
		$_mysql->query("UPDATE `polls` SET `enabled` = " . $enabled . " WHERE `id` = " . $this->id);
		$this->enabled = ($enabled ? true : false);
		// </editor-fold>
	} 
	
	/**
	 * Return this poll's lock type
	 * 
	 * @return string $lockType
	 */
	public function lockType() {
		return $this->lockType;
	}
	
	/**
	 * Set this poll's lock type
	 * 
	 * @param string $lockType
	 * @global mysqli $_mysql
	 */
	public function setLockType($lockType) {
		// <editor-fold defaultstate="collapsed" desc="Set Lock Type Code">
		global $_mysql;
		
		if (!in_array($lockType, array("none", "rank", "permission"))) { // If the lock type is invalid
			return false;
		} else { // If the lock type is valid
			// No need to escape $lockType as if we've got this far we already know that it's safe
			$_mysql->query("UPDATE `polls` SET `lock_type` = '" . $lockType . "' WHERE `id` = " . $this->id);
			$this->lockType = $lockType;
		}
		// </editor-fold>
	}
	
	/**
	 * Return this poll's lock
	 * 
	 * @return string $lock
	 */
	public function lock() {
		return $this->lock;
	}
	
	/**
	 * Set this poll's lock
	 * 
	 * @param string $lock
	 * @global mysqli $_mysql
	 */
	public function setLock($lock) {
		// <editor-fold defaultstate="collapsed" desc="Set Lock Code">
		global $_mysql;
		
		$lock = $_mysql->real_escape_string($lock);
		
		$_mysql->query("UPDATE `polls` SET `lock` = '" . $lock . "' WHERE `id` = " . $this->id);
		$this->lock = $lock;
		// </editor-fold>
	}
	
	/**
	 * Return this poll's type
	 * 
	 * @return string $type
	 */
	public function type() {
		return $this->type;
	}
	
	/**
	 * Create a new poll
	 * 
	 * @param string $name
	 * @param string $lockType
	 * @param string $lock
	 * @param int $expires
	 * @param mixed $answers
	 * @param boolean $multipleAnswers
	 * @param boolean $enabled
	 * @global mysqli $_mysql
	 */
	public function create($name, $lockType, $lock, $expires, $answers, $multipleAnswers, $enabled) {
		// <editor-fold defaultstate="collapsed" desc="Create Code">
		global $_mysql;
		
		if ($this->type == "new") { // Make sure we're not calling the create method on top of a poll which already exists
			if (is_array($answers)) { // If the answers are in the form of an array
				$answers = serialize($answers); // Serialise the array
			}
			
			// Sanitise variables
			$name = $_mysql->real_escape_string(strip_tags($name));
			$lockType = $_mysql->real_escape_string(strip_tags($lockType));
			$lock = $_mysql->real_escape_string(strip_tags($lock));
			$answers = $_mysql->real_escape_string($answers);

			// Insert the new poll into the database
			$_mysql->query("INSERT INTO `polls` (`name`, `lock_type`, `lock`, `expires`, `answers`, `multiple_answers`, `enabled`, `stamp`) 
				VALUES (
				'" . $name . "', 
				'" . $lockType . "',
				'" . $lock . "', 
				" . (int) $expires . ", 
				'" . $answers . "', 
				" . (int) $multipleAnswers . ", 
				" . (int) $enabled . ",
				" . time() . "
				)");
			
			// Set up this object's properties
			$this->id = $_mysql->insert_id;
			$this->name = $name;
			$this->lockType = $lockType;
			$this->lock = $lock;
			$this->type = "existing";

			if ((int) $expires == 0) { // If this poll doesn't have an expiry time
				$this->expires = false;
			} else {
				$this->expires = $expires;
			}
			
			if ((int) $multipleAnswers == 0) {
				$this->multipleAnswers = false;
			} else {
				$this->multipleAnswers = true;
			}
			
			if ((int) $enabled == 0) {
				$this->enabled = false;
			} else {
				$this->enabled = true;
			}

			$query = $_mysql->query("SELECT `answers` FROM `polls` WHERE `id` = " . $this->id . " LIMIT 1");
			$data = $query->fetch_assoc();
			$this->answers = unserialize($data['answers']); // Unserialise the answers
		}
		// </editor-fold>
	}
	
	/**
	 * Add a vote to this poll
	 * 
	 * @param string $answer
	 * @param int $userID
	 * @global User $_currUser
	 * @global mysqli $_mysql
	 * @return boolean $success
	 */
	public function addVote($answer, $userID = false) {
		// <editor-fold defaultstate="collapsed" desc="Add Vote Code">
		global $_currUser, $_mysql;

		if (!$userID) { // If a user ID has not been specified, use the $_currUser object
			$userID = $_currUser->userID();
		}
		
		if ($this->isValidAnswer($answer)) { // If the answer is valid
			$user = new User($userID);
			
			if ($this->lockType() != "none") { // If this poll has a lock
				if ($this->lockType() == "rank") { // If this poll has a rank level requirement
					$rank = new Rank($this->lock());
					$userRank = new Rank($user->rankID());
					
					if ($rank->level() > $userRank->level()) { // If the required rank level is greater than the user's rank level
						return false;
					}
				} else if ($this->lockType() == "permission") { // If this poll has a permission requirement
					if (!$user->checkPermission($this->lock())) { // If the user doesn't have the required permission
						return false;
					}
				}
			}
			
			// If we've got this far into the method then the user has got past the lock
			if ($this->multipleAnswers) { // If multiple answers are allowed for this poll
				// Query to database to see if this user has already voted for this answer
				$query = $_mysql->query("SELECT `id` FROM `polls_votes` WHERE `poll` = " . $this->id . " AND `user` = " . (int) $userID . " AND `answer` = '" . $_mysql->real_escape_string($answer) . "'");
				
				if ($query->num_rows) { // If this user has already voted for this answer
					return false;
				} else { // If this user hasn't already voted for this answer
					$_mysql->query("INSERT INTO `polls_votes` (`poll`, `user`, `answer`, `stamp`) VALUES (" . $this->id . ", " . (int) $userID . ", '" . $_mysql->real_escape_string($answer) . "', " . time() . ")");
					return true;
				}
				
			} else { // If multiple answers are not allowed for this poll
				$query = $_mysql->query("SELECT `id` FROM `polls_votes` WHERE `poll` = " . $this->id . " AND `user` = " . (int) $userID);
				
				if ($query->num_rows) { // If this user has already voted
					return false;
				} else { // If this user hasn't already voted
					$_mysql->query("INSERT INTO `polls_votes` (`poll`, `user`, `answer`, `stamp`) VALUES (" . $this->id . ", " . (int) $userID . ", '" . $_mysql->real_escape_string($answer) . "', " . time() . ")");
					return true;
				}
			}
		}
		// </editor-fold>
	}
	
	/**
	 * Has this user voted on this poll
	 * 
	 * @global User $_currUser
	 * @global mysqli $_mysql
	 * @param int $userID
	 * @return boolean
	 */
	public function hasVoted($userID = false) {
		// <editor-fold defaultstate="collapsed" desc="Determine whether a user has voted or not">
		global $_currUser, $_mysql;
		
		if (!$userID) { // If a userID has not been specified
			$userID = $_currUser->userID();
		}
		
		$query = $_mysql->query("SELECT `id` FROM `polls_votes` WHERE `poll` = " . $this->id . " AND `user` = " . (int) $userID);

		if ($query->num_rows) { // If this user has already voted
			return true;
		} else { // If this user hasn't already voted
			return false;
		}
		// </editor-fold>
	}
	
	/**
	 * Return the number of votes for an answer
	 * 
	 * @param string $answer
	 * @global mysqli $_mysql
	 * @return mixed $votes
	 */
	public function getVotes($answer) {
		// <editor-fold defaultstate="collapsed" desc="Get Votes Code">
		global $_mysql;
		
		if ($this->isValidAnswer($answer)) { // If the answer is valid
			$query = $_mysql->query("SELECT `id` FROM `polls_votes` WHERE `poll` = " . $this->id . " AND `answer` = '" . $_mysql->real_escape_string($answer) . "'");
			
			return $query->num_rows;
		} else { // If the answer is invalid
			return false;
		}
		// </editor-fold>
	}
	
	/**
	 * Return the number of votes for all answers
	 * 
	 * @global mysqli $_mysql
	 * @return int $votes
	 */
	public function getTotalVotes() {
		// <editor-fold defaultstate="collapsed" desc="Get Total Votes Code">
		global $_mysql; 
		
		// Make sure we're only searching for valid answers
		$whereIn = "";
		foreach ($this->answers as $key=>$answer) { // For each answer
			if (!empty($answer)) { // If the answer isn't empty
				if (count($this->answers) == ($key + 1)) { // If this is the last answer
					$whereIn .= "'" . $answer . "'";
				} else { // If this is not the last answer
					$whereIn .= "'" . $answer . "',";
				}
			}
		}
		
		$query = $_mysql->query("SELECT `id` FROM `polls_votes` WHERE `poll` = " . $this->id . " AND `answer` IN (" . $whereIn . ")");
		
		return $query->num_rows;
		// </editor-fold>
	}
	
	/**
	 * Return the results of a poll
	 * 
	 * @param char $mode
	 * @param boolean $returnAsVar
	 * @return mixed $results
	 */
	public function results($mode = "r", $sort = "desc", $returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Results Code">
		$rawResults = array();
		$winner = "";
		$totalVotes = $this->getTotalVotes(); // Get the total number of votes

		foreach ($this->answers() as $answer) { // For each answer
			$votes = $this->getVotes($answer); // Get the votes for this answer
			$percentage = ($votes / $totalVotes) * 100; // Work out the percentage of votes for this answer
			
			$rawResults[$answer] = array("answer"=>$answer, "votes"=>$votes, "percentage"=>$percentage);
			
			if (empty($winner)) { // If the winner string hasn't been set yet, this is the first answer
				$winner = $answer; // Set the winner to this answer
			} else { // If the winner string has been set, check to see whether this answer has more votes
				if ($rawResults[$answer]['votes'] > $rawResults[$winner]['votes']) { // If this answer has more votes
					$winner = $answer; // Set the winner to be this answer
				}
			}
		}
		// Set the winning answer element, so we can easily access it
		$rawResults['WINNING_ANSWER'] = array("answer"=>$winner, "votes"=>$rawResults[$winner]['votes'], "percentage"=>$rawResults[$winner]['percentage']);
	
		if ($mode == "r") { // If we're using raw mode, return the raw results now
			return $rawResults;
		} else { // If we're not using raw mode, continue
			if ($mode == "d") { // If we're using display mode
				$results = "<div class=\"pollResults\">\n";
				$results .= "<div class=\"pollTitle\">" . $this->name . "</div>\n";
				$results .= "<div class=\"pollSub\">" . (($this->expired()) ? "Final Results" : "Current Results") . "</div>\n";
				$results .= "<div class=\"pollAnswers\">\n";

				if ($sort == "desc") { // If we're sorting by votes descending
					uasort($rawResults, "self::pollResultsSortDesc");
				} else if ($sort == "asc") { // If we're sorting by votes ascending
					uasort($rawResults, "self::pollResultsSortAsc");
				} else if ($sort == "name") { // If we're sorting by name (alphabetically)
					uasort($rawResults, "self::pollResultsSortName");
				}

				$results .= "<table>\n";
				foreach ($rawResults as $key=>$result) { // For each result
					if ($key == "WINNING_ANSWER") { // If this is the winning answer element, skip it
						continue;
					} else {
						$results .= "<tr>\n";
						$results .= "<td>" . $result['answer'] . "</td>";
						$results .= "<td>" . $result['votes'] . " (" . $result['percentage'] . "&#37;)</td>";
						// TODO: Progressive image based on percentage of votes display
						//$results .= "";
						$results .= "</tr>\n";
					}
				}
				$results .= "</table>\n";
				
				$results .= "</div>\n"; // End of the poll answers div
				$results .= "</div>\n"; // End of the pollResults div
				
				if ($returnAsVar) { // If we're returing the results as a variable
					return $results;
				} else { // If we're outputting the results
					print($results);
				}
			}
		}
		// </editor-fold>
	}
	
	/**
	 * Output the poll form
	 * 
	 * @param boolean $returnAsVar
	 * @return string $pollForm
	 */
	public function display($returnAsVar = false) {
		// <editor-fold defaultstate="collapsed" desc="Display Code">
		$poll = "";
		
		if ($this->enabled) { // If this poll is enabled
			if (!$this->expired()) { // If this poll hasn't expired yet
				if (!$this->hasVoted()) { // If this user hasn't voted on the poll yet
					$poll = "<div class=\"pollDisplay\">\n";
					$poll .= "<div class=\"pollTitle\">" . $this->name . "</div>\n";
					$poll .= "<div class=\"pollSub\">Poll ends " . strtolower($this->expires("d")) . "</div>\n";
					$poll .= "<form id=\"pollForm\" action=\"./?p=PollVote\" method=\"post\">\n";
					$poll .= "<input type=\"hidden\" id=\"pollID\" name=\"pollID\" value=\"" . (int) $this->id . "\" />\n";
					$poll .= Security::generateToken("pollForm", true); // Generate a CSRF token
					$poll .= "<div class=\"pollAnswers\">\n";

					$poll .= "<table>\n";
					foreach ($this->answers as $answer) { // For each answer
						if (!empty($answer)) { // If the answer is not empty
							$poll .= "<tr>\n";
							$poll .= "<td>" . $answer . "</td>";
							$poll .= "<td>" . (($this->multipleAnswers) ? "<input type=\"checkbox\" id=\"pollAnswer_" . $answer . "\" name=\"pollAnswer_" . $answer . "\" value=\"on\" />" : "<input type=\"radio\" id=\"pollAnswer_" . $answer . "\" name=\"pollAnswer\" value=\"" . $answer . "\" />") . "</td>";
							$poll .= "</tr>\n";
						}
					}
					$poll .= "</table>\n";

					$poll .= "</div>\n"; // End of the poll answers div
					$poll .= "<button type=\"submit\" id=\"pollVoteButton\" name=\"pollVoteButton\">Vote</button>\n";
					$poll .= "</form>\n"; // End of the poll form
					$poll .= "</div>\n"; // End of the pollResults div
				} else { // If this user has voted on the poll
					$poll = $this->results("d", "desc", true); // Temporary for now, we need a new setting to whether results are public until it's expired or not
				}
			} else { // If this poll has expired
				$poll = $this->results("d", "desc", true);
			}
		}
		
		if ($returnAsVar) { // If we're returning the poll display as a variable
			return $poll;
		} else { // If we're outputting the poll
			print($poll);
		}
		// </editor-fold>
	}
	
	// <editor-fold defaultstate="collapsed" desc="Sorting Methods">	
	/**
	 * Sort the poll results by lowest votes first
	 * 
	 * @param array $a
	 * @param array $b
	 */
	public static function pollResultsSortAsc($a, $b) {
		// <editor-fold defaultstate="collapsed" desc="Poll Results Sort Descending">
		if ((int) $a['votes'] > (int) $b['votes']) { // If a's votes are higher than b's votes
			return 1;
		} else if ((int) $b['votes'] > (int) $a['votes']) { // If b's votes are higher than a's votes
			return -1;
		} else { // If there votes are equal
			return 0;
		}
		// </editor-fold>
	}
	
	/**
	 * Sort the poll results by highest votes first
	 * 
	 * @param array $a
	 * @param array $b
	 */
	public static function pollResultsSortDesc($a, $b) {
		// <editor-fold defaultstate="collapsed" desc="Poll Results Sort Ascending">
		if ($b['votes'] > $a['votes']) { // If b's votes are higher than a's votes
			return 1;
		} else if ($a['votes'] > $b['votes']) { // If a's votes are higher than b's votes
			return -1;
		} else { // If there votes are equal
			return 0;
		}
		// </editor-fold>
	}
	
	/**
	 * Sort the poll results alphabetically
	 * 
	 * @param array $a
	 * @param array $b
	 */
	public static function pollResultsSortName($a, $b) {
		// <editor-fold defaultstate="collapsed" desc="Poll Results Sort Name">
		$cmp = strcmp($a['answer'], $b['answer']);
		if ($cmp > 0) { // If a comes before b
			return 1;
		} else if ($cmp < 0) { // If be comes before a
			return -1;
		} else { // If they're equal
			return 0;
		}
		// </editor-fold>
	}
	// </editor-fold>
	
	/**
	 * Return an array of all polls
	 * 
	 * @global mysqli $_mysql
	 * @return array
	 */
	public static function getPolls() {
		// <editor-fold defaultstate="collapsed" desc="Get Polls">
		global $_mysql;
		
		$polls = array(); // Instantiate an array to store the poll names
		
		$query = $_mysql->query("SELECT `id`, `name` FROM `polls`");
		
		if ($query->num_rows) { // If there's at least one poll
			while ($poll = $query->fetch_assoc()) { // For each poll
				$polls[$poll['id']] = $poll['name'];
			}
		}
		return $polls;
		// </editor-fold>
	}
}
?>