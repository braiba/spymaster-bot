<?php

namespace Braiba\Twitter\Behaviours;

use Braiba\Twitter\TwitterBot;

/**
 * Description of ReplyToTweetBehaviour
 *
 * @author Braiba
 */
abstract class ReplyToTweetBehaviour implements TwitterBotBehaviour
{
    /**
     * @return string
     */
    abstract protected function getResponse(TwitterBot $bot, $tweet);
    
    public function behave(TwitterBot $bot)
    {
        $username = $bot->getUsername();
        $client   = $bot->getClient();
        
        $request = [];
        
        $lastSeenTweetId = $client->getLastSeenTweetId();
        if ($lastSeenTweetId !== null) {
            $request['since_id'] = $lastSeenTweetId;
        }
        
        $lastSeenTweetId = null;
        
        $tweets = $client->getMentions($request);
        foreach ($tweets as $tweet) {
            $id   = $tweet->id_str;
            $text = $tweet->text;
            
            if (strpos($text, '@' . $username) === 0) {
                $response = $this->getResponse($bot, $tweet);
                if ($response !== null) {
                    if (is_string($response)) {
                        $response = [
                            'status' => $response,
                        ];
                    }
                    $client->tweet($response);
                }
            }
            
            $lastSeenTweetId = $id;
        }
        
        if ($lastSeenTweetId !== null) {
            $client->setLastSeenTweetId($lastSeenTweetId);
        }
    }
}
