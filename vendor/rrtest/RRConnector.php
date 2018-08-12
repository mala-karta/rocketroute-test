<?php
/**
 * User: irene
 * Date: 08.08.2018
 */

namespace RRTest;

use Exception;
use GuzzleHttp\Client as GuzzleHttpClient;
use SimpleXMLElement;
use Internal\Config;
use DateTime;
use SoapClient;

class RRConnector
{
    const SUCCESS_RESPONSE_MSG = 'SUCCESS';
    const APP_KEY_LIFETIME_HOURS = '5';

    const ERROR_OBTAINING_APP_KEY = 'Error obtaining application access key';
    const ERROR_RESPONSE_APP_KEY_EMPTY = 'Application access key is empty';
    const ERROR_OBTAINING_RESPONSE = 'Can not obtain response from Rocket Route for NOTAM info';

    /**
     * @var string - file for storing app key and its expiration date
     *               file format - two lines, 1st line - app key, 2nd line - ap key expiration date
     */
    protected $_appKeyFileName = 'appkey';

    /** @var string - directory for storing app key ($this->_appKeyFileName), {BASE_PATH}/{$this->_appKeyDir} */
    protected $_appKeyDir = 'tmp';

    /** @var Config  */
    protected $_config;

    /**
     * RRConnector constructor.
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
        $this->_initAppKey();
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
     * Checks if app key exists and is not expired - if not - initial its obtaining
     *
     * @return $this
     */
    protected function _initAppKey()
    {
        if ($this->_isAppKeyAvailable()) {
            return $this;
        }
        $this->_requestAppKey();
        return $this;
    }

    /**
     * checks if file with the app access key exists and the app access key is not expired yet
     *
     * @return bool
     */
    protected function _isAppKeyAvailable()
    {
        $filePath = $this->_getAppKeyFilePath();
        if (!file_exists($filePath)) {
           return false;
        }

        $fileContent = file_get_contents($filePath);
        list($appKey, $expDate) = explode(PHP_EOL, $fileContent);
        if (!$appKey || !$expDate) {
            return false;
        }
        if (strtotime($expDate) <= strtotime(date('Y-m-d H:i:s'))) {
            return false;
        }

        return true;
    }

    /**
     * @param $data
     * @param SimpleXMLElement $xml
     * @return $this
     */
    public function arrayToXml($data, SimpleXMLElement &$xml)
    {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild("$key",htmlspecialchars("$value"));
            }
        }
        return $this;
    }

    /**
     * generates and stores deviceId for desktop
     *
     * @return string
     */
    protected function _getDeviceId()
    {
        if (!empty($_COOKIE['forDeviceId'])) {
            $forDeviceId = $_COOKIE['forDeviceId'];
        } else {
            $forDeviceId = random_int(PHP_INT_MIN, PHP_INT_MAX);
            setcookie('forDeviceId', $forDeviceId);
        }
        $a = md5($forDeviceId . $_SERVER['HTTP_USER_AGENT']);
        return md5($forDeviceId . $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Sends request to obtain App key, then stores it and its exp date to file
     *
     * @return $this
     * @throws Exception
     */
    protected function _requestAppKey()
    {
        $client = new GuzzleHttpClient();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><AUTH></AUTH>');

        $params = [
            'USR'       => $this->getConfig()->getRocketLogin(),
            'PASSWD'    => $this->getConfig()->getRocketPassword(),
            'DEVICEID'  => $this->_getDeviceId(),
            'PCATEGORY' => 'RocketRoute',
            'APPMD5'    => $this->getConfig()->getRocketAppMd5(),

        ];
        $this->arrayToXml($params, $xml);

        $options = [
            'form_params' => ['req' => $xml->asXML()],
        ];
        $response = $client->request('POST', $this->getConfig()->getRocketAccessKeyUrl(), $options );


        $xmlTextResponse = $response->getBody()->getContents();

        //check if success
        $simpleXmlResponse = new SimpleXMLElement($xmlTextResponse);

        if (!isset($simpleXmlResponse->RESULT[0])
            || self::SUCCESS_RESPONSE_MSG != (string)$simpleXmlResponse->RESULT[0]) {
            $msg = isset ($simpleXmlResponse->MESSAGES[0]->MSG[0])
                 ? (string) $simpleXmlResponse->MESSAGES[0]->MSG[0]
                 : self::ERROR_OBTAINING_APP_KEY;
            throw new Exception($msg);
        }

        $appKey = isset($simpleXmlResponse->KEY[0])
                ? (string)$simpleXmlResponse->KEY[0]
                : null;

        if (!$appKey) {
            throw new Exception(self::ERROR_RESPONSE_APP_KEY_EMPTY);
        }

        $date = new DateTime();
        $date->modify('+' . self::APP_KEY_LIFETIME_HOURS . ' hours');
        $appKeyExpDate = $date->format('Y-m-d H:i:s');

        $this->_saveAppKeyToFile($appKey, $appKeyExpDate);

        return $this;
    }

    /**
     * @param string $appKey
     * @param string $appKeyExpDate (in format Y-m-d H:i:s)
     * @return $this
     */
    protected function _saveAppKeyToFile($appKey, $appKeyExpDate)
    {
        $fh = fopen($this->_getAppKeyFilePath(), 'w');
        fwrite($fh, $appKey . PHP_EOL);
        fwrite($fh, $appKeyExpDate);
        fclose($fh);
        return $this;
    }

    /**
     * @return string
     */
    protected function _getAppKeyFilePath()
    {
        return BASE_PATH . '/' . $this->_appKeyDir . '/' . $this->_appKeyFileName;
    }

    /**
     * get NOTAM from SOAP request by known ICAO
     *
     * @param $icao
     * @return array
     * @throws Exception
     */
    public function getNotam($icao)
    {
        $client = new SoapClient($this->getConfig()->getRocketWSDL());

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><REQWX></REQWX>');

        $params = [
            'USR'    => $this->getConfig()->getRocketLogin(),
            'PASSWD' => $this->getConfig()->getRocketPassword(),
            'ICAO'   => $icao,

        ];
        $this->arrayToXml($params, $xml);

        $request = $xml->asXML();

        $response = $client->getNotam($request);
        if (!$response) {
            throw new Exception(self::ERROR_OBTAINING_RESPONSE);
        }
        $simpleXmlResponse = new SimpleXMLElement($response);

        //checking if response has NOTAMSET for continue work
        if (!$simpleXmlResponse->NOTAMSET) {
            throw new Exception(self::ERROR_OBTAINING_RESPONSE);
        }

        $notam = [];

        foreach ($simpleXmlResponse->NOTAMSET->NOTAM as $notamNode) {
            $itemQ = (string) $notamNode->ItemQ;
            $itemQ = explode('/', $itemQ);
            // gps coordinates are at 8th position (official NOTAM say that my be 9th position - with the radius)
            $itemQ = isset($itemQ[7]) ? $itemQ[7] : null;
            $item = [
                'gps' => $itemQ,
                'msg' => (string) $notamNode->ItemE,
            ];
            $notam[] = $item;
        }

        return $notam;
    }
}