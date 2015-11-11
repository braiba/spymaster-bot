<?php

namespace Braiba\Twitter\Requests;

/**
 * Description of TweetRequest
 *
 * @author Braiba
 */
class TweetRequest extends AbstractRequest
{
    /**
     *
     * @var string 
     */
    protected $status;
    
    protected $inReplyToStatusId = null;
    
    protected $mediaIds = [];
    
    public function __construct($status)
    {
        $this->status = $status;
    }
    
    public function getParams()
    {
        $params = [
            'status' => $this->status,
        ];
        
        if ($this->inReplyToStatusId !== null) {
            $params['in_reply_to_status_id'] = $this->inReplyToStatusId;
        }
        
        if (!empty($this->mediaIds)) {
            $params['media_ids'] = $this->mediaIds;
        }
        
        return $params;
    }
}
