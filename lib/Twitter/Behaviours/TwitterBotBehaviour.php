<?php

namespace Braiba\Twitter\Behaviours;

use Braiba\Twitter\TwitterBot;

/**
 * Description of TwitterBotBehaviour
 *
 * @author Braiba
 */
interface TwitterBotBehaviour
{
    public function behave(TwitterBot $bot);
}
