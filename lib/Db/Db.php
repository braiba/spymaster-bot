<?php

namespace Braiba\Db;

use PDO;
use PDOStatement;

/**
 * Description of Db
 *
 * @author Braiba
 */
class Db
{
    /**
     *
     * @var PDO
     */
    protected $pdo;
    
    public function connect($host, $username, $password, $schema)
    {
        $this->pdo = new PDO('mysql:dbname=' . $schema . ';host=' . $host, $username, $password);
    }
    
    /**
     * 
     * @param type $sql
     * @param type $params
     * @return PDOStatement
     */
    public function query($sql, $params = null)
    {
        if ($params === null) {
            return $this->pdo->query($sql);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
