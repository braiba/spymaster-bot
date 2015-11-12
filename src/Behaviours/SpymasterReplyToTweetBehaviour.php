<?php

namespace Braiba\Spymaster\Behaviours;

use Braiba\Spymaster\SpymasterGame;
use Braiba\Twitter\Behaviours\ReplyToTweetBehaviour;
use Braiba\Twitter\TwitterBot;

/**
 * Description of SpymasterReplyToTweetBehaviour
 *
 * @author Braiba
 */
class SpymasterReplyToTweetBehaviour extends ReplyToTweetBehaviour
{
    protected function getResponse(TwitterBot $bot, $tweet)
    {
        $id       = $tweet->id_str;
        $sender   = $tweet->user->screen_name;
        $targetId = $tweet->in_reply_to_status_id;
        $text     = $tweet->text;
        
        $username = $bot->getUsername();
        if (strpos($text, '@' . $username) !== 0) {
            return null; // not a direct mention
        }
        
        $game = SpymasterGame::getByTweetId($targetId);
        if ($game === null) {
            return null; // Not in response to a game
        }
        
        if ($game->getFinished()) {
            return 'Sorry, this game has already finished. DM me "start" to start a new game of your own';
        }
        
        $text  = substr($text, strlen($username) + 2);
        $text  = preg_replace('/[^A-Za-z]+/', ' ', $text);
        $words = explode(' ', trim($text));
        
        // TODO: validate this shit
        
        $game->recordGuess($sender, $words);
    }
}
