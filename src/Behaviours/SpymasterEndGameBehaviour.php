<?php

namespace Braiba\Spymaster\Behaviours;

use Braiba\Di;
use Braiba\Spymaster\SpymasterGame;
use Braiba\Twitter\Behaviours\TwitterBotBehaviour;
use Braiba\Twitter\TwitterBot;

/**
 * Description of ReplyToDmBehaviour
 *
 * @author Braiba
 */
class SpymasterEndGameBehaviour implements TwitterBotBehaviour
{
    public function behave(TwitterBot $bot)
    {
        $sql = <<<'SQL'
SELECT
*
FROM spymaster_game
WHERE finished = FALSE
AND end_timestamp <= NOW()
SQL;
        $result = Di::getDefault()->getDb()->query($sql);
        
        while ($game = SpymasterGame::createFromQueryResult($result)) {
            $game->finish($bot);
        }
    }
}
