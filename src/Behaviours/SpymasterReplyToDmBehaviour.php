<?php

namespace Braiba\Spymaster\Behaviours;

use Braiba\Spymaster\SpymasterGame;
use Braiba\Spymaster\SpymasterGameFactory;
use Braiba\Twitter\Behaviours\ReplyToDmBehaviour;
use Braiba\Twitter\TwitterBot;

/**
 * Description of SpymasterReplyToDmBehaviour
 *
 * @author Braiba
 */
class SpymasterReplyToDmBehaviour extends ReplyToDmBehaviour
{   
    protected function getResponse(TwitterBot $bot, $dm)
    {
        $id     = $dm->id_str;
        $sender = $dm->sender->screen_name;
        $text   = $dm->text;
        
        $game = SpymasterGame::getCurrentGame($sender);
        
        $gameState = ($game === null ? SpymasterGame::STATE_NONE : $game->getState());
        
        switch ($gameState) {
            case SpymasterGame::STATE_NONE:
                if ($text !== 'start') {
                    return 'Message "start" to start a new game';
                } else {
                    $factory = new SpymasterGameFactory();
                    $game = $factory->createGame($sender);
                    return $this->buildGameDm($game);
                }
                break;
                
            case SpymasterGame::STATE_PENDING:
                $game->startGame($bot, $text);
                return 'The game has begun!';
                
            case SpymasterGame::STATE_ACTIVE:
                return 'A game is currently in progress';
        }
    }
    
    protected function buildGameDm(SpymasterGame $game)
    {
        $text = '--- Target words ---' . PHP_EOL . implode(PHP_EOL, $game->getScorableWords());
        $text .= PHP_EOL . PHP_EOL;
        $text .= '--- Other words ---' . PHP_EOL . implode(PHP_EOL, $game->getNonScorableWords());
        $text .= PHP_EOL . PHP_EOL;
        $text .= '--- Assassin ---' . PHP_EOL . implode(PHP_EOL, $game->getAssassinWords());

        return $text;
    }
}
