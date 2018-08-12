<?php
/**
 * User: irene
 * Date: 10.08.2018
 */

namespace RRTest;

use Internal\Config;
use Exception;

class Processor
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';

    const ERROR_ICAO_NOT_SET = 'Can not set ICAO';
    const ERROR_CONNECTOR_ERROR = 'Connection ito RocketRoute is not set';

    const ERROR_NO_NOTAM_INFO = 'Sorry, there is no NOTAM for such ICAO';

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
     * method gets NOTAM info from RocketRoute SOAP by given airport ICAO code
     *
     * @return string , json, format: [status => ok|error, errorMsg => errorMsg]
     */
    public function process()
    {
        $this->_initIcao();
        if (!$this->getIcao()) {
            return json_encode([
                'status'   => 'error',
                'errorMsg' => self::ERROR_ICAO_NOT_SET,
            ]);
        }

        try {
            $this->_initConnector();
            $notam = $this->_connector->getNotam($this->getIcao());
        } catch (Exception $e) {
            ExceptionHandler::printJsonError($e->getMessage());
            die();
        }

        if (empty($notam)) {
            ExceptionHandler::printJsonError(self::ERROR_NO_NOTAM_INFO);
            die();
        }

        //we have notam info(s) for appropriate ICAO.
        //we have gps coordinates for each location and we need to calculate coordinates in Projected Coordinate System
        $this->_prepareNotam($notam);

        echo json_encode([
            'status' => 'ok',
            'notam'  => $notam,
        ]);
        die();
    }

    /**
     * add lat, lng for each notam item (lat, lng are needed for google maps)
     *
     * @param array $notam
     * @return $this
     */
    protected function _prepareNotam(&$notam)
    {
        $parsed = [];
        foreach ($notam as &$item) {
            $gps = $item['gps'];

            if (!isset($parsed[$gps])) {

                $this->_parseNotamItemQ($item['gps']);
                $coord = $this->_getProjectedCoord($item['gps']);

                $item['lat'] = $coord['lat'];
                $item['lng'] = $coord['lng'];

                $parsed[$gps] = [
                    'msg' => $item['msg'],
                    'lat' => $item['lat'],
                    'lng' => $item['lng'],
                ];
            } else {
                //if notam with the same coordinates exists
                $parsed[$gps]['msg'] .= '<br><br>' . $item['msg'];
            }
        }

        $parsed = array_values($parsed);

        $notam = $parsed;
        return $this;
    }

    /**
     * parse NOTAM ItemQ to gps latitude and longitude,
     * from $itemQ = '5129N00128E' we'll get:
     * $itemQ = [
     *      'lat' => [
     *          's' => 1,  // sign, 1 for north an west
     *          'd' => 51, // degrees
     *          'm' => 29, // minutes
     *      ],
     *      'lng' => [
     *          's' => -1, // sign, -1 for south and east
     *          'd' => 1,  // degrees
     *          'm' => 28  // minutes
     *      ]
     * ]
     *
     * @param string $itemQ
     * @return $this
     */
    protected function _parseNotamItemQ(&$itemQ)
    {
        $matches = [];
        preg_match_all('/^(\d{2})(\d{2})([N|S])(\d{3})(\d{2})([W|E])/', $itemQ, $matches);
        $res = [];

        $res['lat']['d'] = (int)$matches[1][0];
        $res['lat']['m'] = (int)$matches[2][0];
        if ('S' == $matches[3][0]) {
            $res['lat']['s'] = -1;
        } else {
            $res['lat']['s'] = 1;
        }

        $res['lng']['d'] = (int)$matches[4][0];
        $res['lng']['m'] = (int)$matches[5][0];
        if ('E' == $matches[6][0]) {
            $res['lng']['s'] = 1;
        } else {
            $res['lng']['s'] = -1;
        }


        $itemQ = $res;
        return $this;
    }

    /**
     * @param array $geographicCoord in format:
     *      [
     *      'lat' => [
     *          's' => 1|-1  // sign, -1 for south and east
     *          'd' => unsigned int,   // degrees for latitude
     *          'm' => unsigned int, // minutes for latitude
     *      ],
     *      'lng' => [
     *          's' => 1|-1
     *          'd' => unsigned int,   // degrees for longitude
     *          'm' => unsigned int  // minutes for longitude
     *      ]
     * @return array in format:
     * [
     *      'lat' => 'xx.xxx', where x - one digit
     *      'lng' => 'xxx.xxx', where x - one digit
     * ]
     */
    protected function _getProjectedCoord($geographicCoord)
    {
        $lat = $geographicCoord['lat']['d'] + $geographicCoord['lat']['m'] / 60;
        $lng = $geographicCoord['lng']['d'] + $geographicCoord['lng']['m'] / 60;
        $res = [];

        $lat *= $geographicCoord['lat']['s'];
        $lng *= $geographicCoord['lng']['s'];


        $res = [
            'lat' => $lat,
            'lng' => $lng,
        ];
        return $res;
    }

    /**
     * @param string $message
     * @return string
     */
    protected function _formJsonErrorMsg($message)
    {
        return json_encode([
            'status'  => self::STATUS_ERROR,
            'message' => $message
        ]);
    }

    /**
     * @param string $status  - [ok|error]
     * @param string $message
     * @return string
     */
    protected function _formJsonResponse($status, $message)
    {
        return json_encode([
            'status'  => $status,
            'message' => $message
        ]);
    }

    /**
     * @return $this
     */
    protected function _initConnector()
    {
        $this->_connector = new RRConnector($this->getConfig());
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initIcao()
    {
        //todo: return error
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