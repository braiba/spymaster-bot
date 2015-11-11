<?php

namespace Braiba\Spymaster;

use Braiba\Di;
use Braiba\Twitter\TwitterBot;

/**
 * Description of SpymasterGame
 *
 * @author Braiba
 */
class SpymasterGame
{
    const FILE_WORDLIST    = 'config/wordlist.txt';
    
    const GRID_CELL_WIDTH  = 75;
    const GRID_CELL_HEIGHT = 50; 
    const GRID_FONT_SIZE   = 14; 
    
    const STATE_NONE     = 'NONE';
    const STATE_PENDING  = 'PENDING';
    const STATE_ACTIVE   = 'ACTIVE';
    const STATE_COMPLETE = 'COMPLETE';
   
    protected $id            = null;
    protected $owner         = null;
    protected $words         = [];
    protected $scorableWords = [];
    protected $assassinWords = [];
    protected $clue          = null;
    protected $tweetId       = null;
    protected $finished      = false;
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    
    function getOwner()
    {
        return $this->owner;
    }

    function setOwner($owner)
    {
        $this->owner = $owner;
    }
    
    function getWords()
    {
        return $this->words;
    }

    function getScorableWords()
    {
        return $this->scorableWords;
    }

    function getNonScorableWords()
    {
        return array_diff($this->words, $this->scorableWords);
    }

    function getAssassinWords()
    {
        return $this->assassinWords;
    }

    function setWords($words)
    {
        $this->words = $words;
    }

    function setScorableWords($scorableWords)
    {
        $this->scorableWords = $scorableWords;
    }

    function setAssassinWords($assassinWords)
    {
        $this->assassinWords = $assassinWords;
    }
    
    public function getClue()
    {
        return $this->clue;
    }

    public function setClue($clue)
    {
        $this->clue = $clue;
    }
    
    public function getTweetId()
    {
        return $this->tweetId;
    }

    public function setTweetId($tweetId)
    {
        $this->tweetId = $tweetId;
    }

    public function getFinished()
    {
        return $this->finished;
    }

    public function setFinished($finished)
    {
        $this->finished = $finished;
    }
    
    public function save()
    {
        $sql = <<<'SQL'
INSERT INTO spymaster_game
SET owner = :owner,
words = :words,
scorable_words = :scorable_words,
assassin_words = :assassin_words;
SQL;
        $params = [
            'owner'          => $this->getOwner(),
            'words'          => implode(',', $this->getWords()),
            'scorable_words' => implode(',', $this->getScorableWords()),
            'assassin_words' => implode(',', $this->getAssassinWords()),
        ];
        Di::getDefault()->getDb()->query($sql, $params);
    }
    
    function generateGridImage($coloured = false)
    {
        $words = $this->getWords();
        
        $cellCount  = sizeof($words);
        $gridWidth  = floor(sqrt($cellCount));
        $gridHeight = ceil($cellCount / $gridWidth);
        
        $imageWidth  = $gridWidth * self::GRID_CELL_WIDTH + 1;
        $imageHeight = $gridHeight * self::GRID_CELL_HEIGHT + 1;
        
        $img = imagecreate($imageWidth, $imageHeight);
        
        $black = imagecolorallocate($img, 0, 0, 0);
        $white = imagecolorallocate($img, 255, 255, 255);
        $green = imagecolorallocate($img, 107, 214, 116);
        $bland = imagecolorallocate($img, 255, 226, 158);
        imagefilledrectangle($img, 0, 0, $imageWidth - 1, $imageHeight - 1, $bland);
        
        // Draw grid
        for ($x = 0; $x < $gridWidth; $x++) {
            for ($y = 0; $y < $gridHeight; $y++) {
                $index = $y * $gridWidth + $x;
                $word  = $words[$index];
                
                $textColour = $black;
                $fillColour = null;
                if ($coloured) {
                    if (in_array($word, $this->getAssassinWords())) {
                        $textColour = $white;
                        $fillColour = $black;
                    } elseif (in_array($word, $this->getScorableWords())) {
                        $fillColour = $green;
                    }
                }
                
                if ($fillColour !== null) {
                    imagefilledrectangle(
                        $img, 
                        $x * self::GRID_CELL_WIDTH, 
                        $y * self::GRID_CELL_HEIGHT, 
                        ($x + 1) * self::GRID_CELL_WIDTH, 
                        ($y + 1) * self::GRID_CELL_HEIGHT, 
                        $fillColour
                    );
                }
                
                imagerectangle(
                    $img, 
                    $x * self::GRID_CELL_WIDTH, 
                    $y * self::GRID_CELL_HEIGHT, 
                    ($x + 1) * self::GRID_CELL_WIDTH, 
                    ($y + 1) * self::GRID_CELL_HEIGHT, 
                    $black
                );
                
                $textBox = imageftbbox(13, 0, 'resources/arial.ttf', $word);
                
                imagefttext(
                    $img, 
                    13,
                    0,
                    ($x + 0.5) * self::GRID_CELL_WIDTH - $textBox[6] - ($textBox[4] + $textBox[6]) / 2, 
                    ($y + 0.5) * self::GRID_CELL_HEIGHT - ($textBox[7] / 2),  
                    $textColour,
                    'resources/arial.ttf',
                    $word
                );
            }
        }
        
        $filename = tempnam('tmp/', 'spm');
        
        imagejpeg($img, $filename);
        
        // DEBUG
        $newFilename = str_replace('.tmp', '.jpg', $filename);
        rename($filename, $newFilename);
        $filename = $newFilename;
        // END DEBUG
        
        return $filename;
    }
    
    public function startGame(TwitterBot $bot, $clue)
    {
        $this->clue = $clue;
        
        $client = $bot->getClient();

        $filename = $this->generateGridImage();

        $mediaResponse = $client->uploadMedia(
            [
                'media' => $filename,
            ]
        );

        $tweetResponse = $client->tweet(
            [
                'status' => 
                    '--- Incoming Transmission from Spymaster ' . $this->getOwner(). ' ---' . PHP_EOL .
                    '> ' . $this->getClue(),
                'media_ids' => [
                    $mediaResponse->media_id_string,
                ],
            ]
        );
        
        $tweetId = $tweetResponse->id_str;
                
        $sql = <<<'SQL'
UPDATE spymaster_game
SET clue = :clue,
tweet_id = :tweet_id,
start_timestamp = NOW(),
end_timestamp = NOW() + INTERVAL 30 MINUTE
WHERE id = :id
SQL;
        $params = [
            'id'       => $this->id,
            'tweet_id' => $tweetId,
            'clue'     => $clue,
        ];
        Di::getDefault()->getDb()->query($sql, $params);
    }
    
    public function getState()
    {
        if ($this->clue === null) {
            return self::STATE_PENDING;
        } elseif ($this->finished) {
            return self::STATE_COMPLETE;
        } else {
            return self::STATE_ACTIVE;
        }
    }
    
    public function recordGuess($guesser, $words)
    {
        $score = 0;
        
        $scorableWords = $this->getScorableWords();
        $assassinWords = $this->getAssassinWords();
        
        foreach ($words as $word) {
            $word = ucfirst(strtolower($word)); // basic sanitization
            if (in_array($word, $scorableWords)) {
                $score++;
            } elseif (in_array($word, $assassinWords)) {
                $score = null;
                break;
            } else {
                // Innocent bystander
                break;
            }
        }
        
        $sql = <<<'SQL'
INSERT INTO spymaster_game_guess
SET spymaster_game_id = :spymaster_game_id,
guesser = :guesser,
guess = :guess,
score = :score
SQL;
        $params = [
            'spymaster_game_id' => $this->id,
            'guesser' => $guesser,
            'guess' => implode(',', $words),
            'score' => $score,
        ];
        Di::getDefault()->getDb()->query($sql, $params);
    }
    
    public function finish(TwitterBot $bot)
    {
        $db = Di::getDefault()->getDb();
        
        $sql = <<<'SQL'
SELECT
guesser,
score
FROM spymaster_game_guess
WHERE id = :spymaster_game_id
ORDER BY score DESC
LIMIT 5
SQL;
        $params = [
            'spymaster_game_id' => $this->id,
        ];
        $result = $db->query($sql, $params);
        
        $filename = $this->generateGridImage(true);
        
        $mediaResponse = $bot->getClient()->uploadMedia(
            [
                'media' => $filename,
            ]
        );
        
        $lines = [];
        $i     = 1;
        while ($row = $result->fetch()) {
            $lines[] = $i++ . '. @' . $row['guesser'] . ': ' . $row['score'];
        }
        
        $text = implode(PHP_EOL, $lines);
        $bot->getClient()->tweet(
            [
                'status' => $text,
                'in_reply_to_status_id' => $this->tweetId,
                'media_ids' => [
                    $mediaResponse->media_id_string,
                ]
            ]
        );
        
        $sql = <<<'SQL'
UPDATE spymaster_game
SET finished = TRUE
WHERE id = :spymaster_game_id
SQL;
        $params = [
            'spymaster_game_id' => $this->id,
        ];
        $result = $db->query($sql, $params);
    }
    
    static public function getCurrentGame($owner)
    {
        $sql = <<<'SQL'
SELECT
*
FROM spymaster_game
WHERE owner = :owner
AND NOT finished
SQL;
        $params = [
            'owner' => $owner,
        ];
        $result = Di::getDefault()->getDb()->query($sql, $params);
        
        return self::createFromQueryResult($result);
    }
    
    /**
     * 
     * @param string $tweetId
     * 
     * @return SpymasterGame
     */
    static public function getByTweetId($tweetId)
    {
        $sql = <<<'SQL'
SELECT
*
FROM spymaster_game
WHERE tweet_id = :tweet_id
SQL;
        $params = [
            'tweet_id' => $tweetId,
        ];
        $result = Di::getDefault()->getDb()->query($sql, $params);
        
        return self::createFromQueryResult($result);
    }
    
    static public function createFromQueryResult($result)
    {
        $row = $result->fetch();
        if (!$row) {
            return null;
        }
        
        $game = new SpymasterGame();

        $game->setId($row['id']);
        $game->setOwner($row['owner']);
        $game->setTweetId($row['tweet_id']);
        $game->setWords(explode(',', $row['words']));
        $game->setScorableWords(explode(',', $row['scorable_words']));
        $game->setAssassinWords(explode(',', $row['assassin_words']));
        $game->setClue($row['clue']);
        $game->setFinished((boolean) $row['finished']);
        
        return $game;
    }
}
