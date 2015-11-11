<?php

namespace Braiba;

use Braiba\Config\Config;
use Braiba\Db\Db;
/**
 * Description of Di
 *
 * @author Braiba
 */
class Di
{
    protected static $defaultInstance;
    
    protected $config;
    
    protected $db;
    
    /**
     * 
     * @return Di
     */
    static public function getDefault()
    {
        if (self::$defaultInstance === null) {
            self::$defaultInstance = new Di();
        }
        return self::$defaultInstance;
    }
    
    /**
     * 
     * @return Config
     */
    public function getConfig()
    {
        if ($this->config === null) {
            $this->config = new Config();
        }
        return $this->config;
    }
    
    /**
     * 
     * @return Db
     */
    public function getDb()
    {
        if ($this->db === null) {
            $this->db = new Db();
            
            $dbConfig = $this->getConfig()->get('db');
            
            $this->db->connect(
                $dbConfig['host'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['schema']
            );
        }
        return $this->db;
    }
    
}
