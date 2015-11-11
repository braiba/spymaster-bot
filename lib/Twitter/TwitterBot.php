<?php

namespace Braiba\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Braiba\Config\Config;
use Braiba\Twitter\Behaviours\TwitterBotBehaviour;

/**
 * Description of TwitterBot
 *
 * @author Braiba
 */
abstract class TwitterBot 
{	
    /**
     *
     * @var string
     */
    protected $username;
    
    /**
     *
     * @var TwitterClient
     */
    protected $client = null;
    
    /**
     *
     * @var TwitterBotBehaviour
     */
    protected $behaviours = [];
    
    /**
     * 
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }
    
    /**
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * @return TwitterClient
     */
    public function getClient()
    {
        if ($this->client === null) {
            $this->client = new TwitterClient();
        }
        return $this->client;
    }
    
	public function tick()
	{
		foreach ($this->behaviours as $behaviour) {
            $behaviour->behave($this);
        }
	}
}
