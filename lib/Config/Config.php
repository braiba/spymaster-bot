<?php

namespace Braiba\Config;

/**
 * Description of Config
 *
 * @author Braiba
 */
class Config
{
    const CONFIG_FILE = 'config/config.php';
        
    protected $config;
    
    protected function getConfig()
    {
        if ($this->config === null) {
            $this->config = require self::CONFIG_FILE;
        }
        return $this->config;
    }
    
    public function get($key, $default = null)
    {
        $config = $this->getConfig();
        return (isset($config[$key]) ? $config[$key] : $default);
    }
}
