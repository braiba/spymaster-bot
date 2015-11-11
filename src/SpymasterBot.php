<?php

namespace Braiba\Spymaster;

use Braiba\Spymaster\Behaviours\SpymasterEndGameBehaviour;
use Braiba\Spymaster\Behaviours\SpymasterReplyToDmBehaviour;
use Braiba\Spymaster\Behaviours\SpymasterReplyToTweetBehaviour;
use Braiba\Twitter\Behaviours\SyncFollowersBehaviour;
use Braiba\Twitter\TwitterBot;

/**
 * Description of SpymasterBot
 *
 * @author Braiba
 */
class SpymasterBot extends TwitterBot
{   
    public function __construct($username)
    {
        parent::__construct($username);
        $this->behaviours[] = new SyncFollowersBehaviour();
        $this->behaviours[] = new SpymasterReplyToDmBehaviour();
        $this->behaviours[] = new SpymasterReplyToTweetBehaviour();
        $this->behaviours[] = new SpymasterEndGameBehaviour();
    }
    
	    # Detect follows
        # Follow back

    # Detect unfollows
        # Unfollow back

# Detect DMs
  # "start" => start game
  # "<word> <number>" => tweet game
  # "end" => end game
  
# Detect replies
  # "<list of words>" => score  

# End game
  # Tweet winner (s)
}
