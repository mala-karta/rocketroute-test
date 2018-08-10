<?php
/**
 * User: irene
 * Date: 10.08.2018
 */

namespace RRTest;

use Internal\Config;

class Processor
{
    const ERR_ICAO_NOT_SET = 'Can not set ICAO';

    /** @var string  */
    protected $_icao = '';

    /** @var null|RRConnector  */
    protected $_connector = null;

    /** @var Config  */
    protected $_config;


    public function __construct(Config $config)
    {
        $this->setConfig($config);
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * @return string , json, format: [status => ok|error, errorMsg => errorMsg]
     */
    public function process()
    {
        $this->_initIcao();
        if (!$this->getIcao()) {
            return json_encode([
                'status'   => 'error',
                'errorMsg' => self::ERR_ICAO_NOT_SET,
            ]);
        }

        $this->_initConnector();

        $notam = $this->_connector->getNotam($this->getIcao());

        $a =2;

        vdie($notam);

        return json_encode([
            'status' => 'ok',

        ]);
    }

    protected function _initConnector()
    {
        $this->_connector = new RRConnector($this->getConfig());
        return $this;
    }

    protected function _initIcao()
    {
        if (!isset($_POST['icao'])) {
            return $this;
        }
        $icao = $_POST['icao'];
        if (1 !== preg_match('/^[A-Z]{4}$/i', $icao)) {
            return $this;
        }
        $this->setIcao($icao);
        return $this;
    }

    /**
     * @return string
     */
    public function getIcao()
    {
        return $this->_icao;
    }

    /**
     * @param string $icao
     */
    public function setIcao($icao)
    {
        $this->_icao = $icao;
    }


}