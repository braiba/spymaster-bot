<?php

namespace Braiba\Twitter\Behaviours;

use Braiba\Twitter\TwitterBot;

/**
 * Description of ReplyToDmBehaviour
 *
 * @author Braiba
 */
abstract class TimedBehaviour implements TwitterBotBehaviour
{
    abstract protected function isTimeToAct();
    
    abstract protected function act($bot);
    
    public function behave(TwitterBot $bot)
    {
        if ($this->isTimeToAct()) {
            $this->act($bot);
        }
    }
}
