<?php

namespace Braiba\Twitter\Behaviours;

use Braiba\Twitter\TwitterBot;

/**
 * Description of ReplyToDmBehaviour
 *
 * @author Braiba
 */
abstract class ReplyToDmBehaviour implements TwitterBotBehaviour
{
    abstract protected function getResponse(TwitterBot $bot, $directMessage);
    
    public function behave(TwitterBot $bot)
    {
        $client = $bot->getClient();
        
        $username = $bot->getUsername();
        
        $request = [];
        
        $lastSeenDmId = $client->getLastSeenDmId();
        if ($lastSeenDmId !== null) {
            $request['since_id'] = $lastSeenDmId;
        }
        
        $lastSeenDmId = null;
        
        $dms = $client->getDms($request);
        
        $dms = array_reverse($dms);
        foreach ($dms as $dm) {
            $response = $this->getResponse($bot, $dm);
            
            if ($response !== null) {
                if (is_array($response)) {
                   $dmRequest = $response;
                } else {
                    $dmRequest = [
                        'text' => $response,
                    ];
                }
                $dmRequest['user_id'] = $dm->sender->id_str;
                $client->dm($dmRequest);
            }
            
            $lastSeenDmId = $dm->id_str;
        }
        
        if ($lastSeenDmId !== null) {
            $client->setLastSeenDmId($lastSeenDmId);
        }
    }
}
