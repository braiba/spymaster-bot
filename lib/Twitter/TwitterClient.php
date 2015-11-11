<?php

namespace Braiba\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Braiba\Config\Config;
use Braiba\Di;

/**
 * Description of TwitterClient
 *
 * @author Braiba
 */
class TwitterClient 
{    
    /**
     *
     * @var TwitterOAuth
     */
    protected $connection = null;
    
    protected $username;
    
    /**
     * @return TwitterOAuth
     */
    protected function getConnection()
    {
        if ($this->connection === null) {
            $config = Di::getDefault()->getConfig()->get('twitter');
            $this->connection = new TwitterOAuth(
                $config['apiKey'], 
                $config['apiSecret'],
                $config['accessToken'],
                $config['accessTokenSecret']
            );
            $content = $this->connection->get("account/verify_credentials");
            if (isset($content->errors)) {
                var_dump($content->errors);
                die();
            }
        }
        
        return $this->connection;
    }
    
    public function getUsername()
    {
        if ($this->username === null) {
            $this->username = Di::getDefault()->getConfig()->get('twitter')['username'];
        }
        return $this->username;
    }
    
    public function getLastSeenTweetId()
    {
        $db = Di::getDefault()->getDb();
        
        $sql = <<<'SQL'
SELECT
last_seen_tweet_id
FROM twitter_client
WHERE username = :username
SQL;
        $params = [
            'username' => Di::getDefault()->getConfig()->get('twitter')['username'],
        ];
        $result = $db->query($sql, $params);
        $row = $result->fetch();
        return $row['last_seen_tweet_id'];
    }
    
    public function getLastSeenDmId()
    {
        $db = Di::getDefault()->getDb();
        
        $sql = <<<'SQL'
SELECT
last_seen_dm_id
FROM twitter_client
WHERE username = :username
SQL;
        $params = [
            'username' => Di::getDefault()->getConfig()->get('twitter')['username'],
        ];
        $result = $db->query($sql, $params);
        $row = $result->fetch();
        return $row['last_seen_dm_id'];
    }
    
    public function setLastSeenTweetId($tweetId)
    {
        $db = Di::getDefault()->getDb();
        
        $sql = <<<'SQL'
UPDATE twitter_client
SET last_seen_tweet_id = :last_seen_tweet_id
WHERE username = :username
SQL;
        $params = [
            'last_seen_tweet_id' => $tweetId,
            'username'           => $this->getUsername(),
        ];
        $result = $db->query($sql, $params);
        $row = $result->fetch();
        return $row['last_seen_tweet_id'];
    }
    
    public function setLastSeenDmId($dmId)
    {
        $db = Di::getDefault()->getDb();
        
        $sql = <<<'SQL'
UPDATE twitter_client
SET last_seen_dm_id = :last_seen_dm_id
WHERE username = :username
SQL;
        $params = [
            'last_seen_dm_id' => $dmId,
            'username'        => $this->getUsername(),
        ];
        $result = $db->query($sql, $params);
        $row = $result->fetch();
        return $row['last_seen_dm_id'];
    }
    
	public function tweet(array $request)
	{
        return $this->getConnection()->post('statuses/update', $request);
	}
    
	public function uploadMedia(array $request)
	{
        return $this->getConnection()->upload('media/upload', $request);
	}
    
    public function getMentions(array $request)
    {
        return $this->getConnection()->get('statuses/mentions_timeline', $request);   
    }
    
	public function dm(array $request)
	{
        return $this->getConnection()->post('direct_messages/new', $request);     
	}
    
    public function getDms(array $request)
    {
        return $this->getConnection()->get('direct_messages', $request);   
    }
    
	public function getFollowers(array $request)
	{
        return $this->getConnection()->get('followers/list', $request);  
	}
    
	public function getFriends(array $request)
	{
        return $this->getConnection()->get('friends/list', $request);    
	}
    
	public function follow(array $request)
	{
        return $this->getConnection()->post('friendships/create', $request);  
	}
    
	public function unfollow(array $request)
	{
        return $this->getConnection()->post('friendships/destroy', $request);  
	}
}
