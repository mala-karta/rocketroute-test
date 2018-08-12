<?php
/**
 * User: irene
 * Date: 08.08.2018
 */

namespace Internal;

//todo: move from this class
define('PRODUCTION_MODE', 'prod');
define('DEVELOPER_MODE', 'dev');
define('APP_MODE', DEVELOPER_MODE);

class Config
{
    /** @var string  */
    protected $_rocketLogin = '';

    /** @var string  */
    protected $_rocketPassword = '';

    /** @var string  */
    protected $_rocketAppMd5 = '';

    /** @var string  */
    protected $_rocketAccessKeyUrl = '';

    /** @var null|string  */
    protected $_rocketAppKey = null;

    /** @var null|DateTime */
    protected $_rocketAppKeyExpDate = null;

    /** @var string  */
    protected $_rocketWSDL = '';

    /** @var string  */
    protected $_googleApiKey = '';

    /**
     * @return string
     */
    public function getGoogleApiKey()
    {
        return $this->_googleApiKey;
    }

    const DEVELOPER_MODE_CNF_FILE = '.cnf-dev';
    const PRODUCTION_MODE_CNF_FILE = '.cnf-prod';

    /** @var array - fields that MUST be read from config file */
    protected $_requiredCnfFields = [
        'rocketLogin', 'rocketPassword', 'rocketAppMd5', 'rocketAccessKeyUrl', 'rocketWSDL',
        'googleApiKey'
    ];

    /**
     * Config constructor. Init data from config file
     */
    public function __construct()
    {
        if (PRODUCTION_MODE == APP_MODE) {
            $fileName = self::PRODUCTION_MODE_CNF_FILE;
        } else {
            $fileName = self::DEVELOPER_MODE_CNF_FILE;
        }

        $fileName = __DIR__ . '/' . $fileName;

        $this->_initFromFile($fileName);
        return $this;
    }

    /**
     * Read and set data from the given file. If one of required field is not set - throws Exception.
     *
     * @param $fileName
     * @return $this
     * @throws Exception
     */
    protected function _initFromFile($fileName)
    {
        $string = file_get_contents($fileName);
        if (!$string) {
            throw new Exception('Can not read config file ' . $fileName);
        }

        $data = json_decode($string, true);

        foreach ($this->_requiredCnfFields as $fieldName) {
            if (empty($data[$fieldName])) {
                throw new Exception($fieldName . ' is not found in the config file ' . $fileName);
            }
            $classFieldName = '_' . $fieldName;
            $this->$classFieldName = $data[$fieldName];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRocketLogin()
    {
        return $this->_rocketLogin;
    }

    /**
     * @return string
     */
    public function getRocketPassword()
    {
        return $this->_rocketPassword;
    }

    /**
     * @return string
     */
    public function getRocketAppMd5()
    {
        return $this->_rocketAppMd5;
    }

    /**
     * @return string
     */
    public function getRocketAccessKeyUrl()
    {
        return $this->_rocketAccessKeyUrl;
    }

    /**
     * @return string
     */
    public function getRocketWSDL()
    {
        return $this->_rocketWSDL;
    }

}

$Config = new Config();
