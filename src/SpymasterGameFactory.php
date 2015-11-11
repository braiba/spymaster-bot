<?php

namespace Braiba\Spymaster;

/**
 * Description of SpymasterGame
 *
 * @author Braiba
 */
class SpymasterGameFactory
{
    protected $wordsFilename     = 'config/wordlist.txt';
    protected $wordsGameTotal    = 16;
    protected $wordsGameScorable = 8;
    protected $wordsGameAssassin = 1;
    
    protected $wordList = null;
    
    protected function getWordlist()
    {
        if ($this->wordList === null) {
            $this->wordList = explode("\r\n", file_get_contents($this->wordsFilename));
            
            array_walk($this->wordList, function (&$value, $key) { $value = ucfirst($value);});
        }
        return $this->wordList;
    }
    
    /**
     * 
     * 
     * @return SpymasterGame
     */
    public function createGame($owner)
    {
        $game = new SpymasterGame();

        $wordlist = $this->getWordlist();
        
        $gameWords     = $this->pickRandomValues($wordlist, $this->wordsGameTotal);
        shuffle($gameWords);
        
        $specialWords  = $this->pickRandomValues($gameWords, $this->wordsGameScorable + $this->wordsGameAssassin);
        $assassinWords = $this->pickRandomValues($specialWords, $this->wordsGameAssassin);
        $scorableWords = array_diff($specialWords, $assassinWords);
        
        $game->setOwner($owner);
        $game->setWords($gameWords);
        $game->setScorableWords($scorableWords);
        $game->setAssassinWords($assassinWords);
       
        $game->save();
           
        return $game;
    }
    
    protected function pickRandomValues($array, $count)
    {
        $randomKeys = array_rand($array, $count);
        
        if ($count === 1) {
            $randomKeys = [$randomKeys];
        }
        
        return array_map(
            function ($key) use ($array) {
                return $array[$key];
            },
            $randomKeys
        );
    }
}
