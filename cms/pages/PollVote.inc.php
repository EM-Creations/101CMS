<?php
// TODO: Nicer failed and suceeded voting messages

if (isset($_POST['pollVoteButton'])) { // If the poll vote button has been clicked
	if (!isset($_POST['pollID'])) { // If a poll ID has not been set
		die("<strong>Error:</strong> No poll ID provided.");
	} else { // If a poll ID has been set
		if (!Security::checkToken("pollForm", $_POST['101Token'], 900)) { // If the CSRF token is invalid
			die("<strong>Error:</strong> Invalid token.");
		}
		
		$poll = new Poll($_POST['pollID']);
		
		if ($poll->type() == "new") { // If the poll object failed to initialise properly
			die("<strong>Error:</strong> Invalid poll.");
		} else { // If the poll object initialised successfully
			if ($poll->multipleAnswers()) { // If multiple answers are allowed
				// Check $_POST variables that start with pollAnswer_
				$failed = false;
				foreach ($_POST as $key=>$var) {
					if (substr($key, 0, 11) == "pollAnswer_") { // If this post variable is an answer
						$answer = $_mysql->real_escape_string(substr($key, 11));
						if ($poll->isValidAnswer($answer)) { // If this answer is valid
							if (!$poll->addVote($answer)) { // If adding a vote failed
								$failed = true;
								return;
							}
						}
					}
				}
				
				if ($failed) { // If adding a vote failed
					print("<strong>Error:</strong> You've already voted in this poll.");
				} else { // If adding vote(s) succeeded
					print("<strong>Thank you for voting.</strong>");
				}
				
			} else { // If multiple answers are not allowed
				// Check the pollAnswer variable
				$answer = $_POST['pollAnswer'];
				if ($poll->isValidAnswer($answer)) { // If the answer is valid
					if ($poll->addVote($answer)) { // If adding a vote succeeded
						print("<strong>Thank you for voting.</strong>");
					} else { // If adding a vote failed
						print("<strong>Error:</strong> You've already voted in this poll.");
					}
				} else { // If the answer is not valid
					print("<strong>Error:</strong> Invalid answer.");
				}
			}
		}
	}
}
?>