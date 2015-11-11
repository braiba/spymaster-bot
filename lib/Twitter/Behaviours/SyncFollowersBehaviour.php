<?php

namespace Braiba\Twitter\Behaviours;

use Braiba\Twitter\TwitterBot;
use Braiba\Twitter\TwitterClient;

/**
 * Description of SyncFollowersBehaviour
 *
 * @author Braiba
 */
class SyncFollowersBehaviour implements TwitterBotBehaviour
{
    public function behave(TwitterBot $bot)
    {
        $client = $bot->getClient();
        
        $friends   = $this->getFriendsUsernames($bot);
        $followers = $this->getFollowersUsernames($bot);
        
        $toFollow   = array_diff($followers, $friends);
        foreach ($toFollow as $username) {
            $client->follow(['screen_name' => $username]);
        }
        
        $toUnfollow = array_diff($friends, $followers);
        foreach ($toUnfollow as $username) {
            var_dump($client->unfollow(['screen_name' => $username]));
        }
    }
    
    public function getFollowersUsernames(TwitterBot $bot)
    {
        $response = $bot->getClient()->getFollowers(
            [
                'screen_name' => $bot->getUsername(),
            ]
        );
        
        return array_map(
            function($friendData) {
                return $friendData->screen_name;
            },
            $response->users
        );
    }
    
    public function getFriendsUsernames(TwitterBot $bot)
    {
        $response = $bot->getClient()->getFriends(
            [
                'screen_name' => $bot->getUsername(),
            ]
        );
        
        return array_map(
            function($friendData) {
                return $friendData->screen_name;
            },
            $response->users
        );
    }
    
}
