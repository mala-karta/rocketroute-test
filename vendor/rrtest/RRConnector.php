<?php
/**
 * User: irene
 * Date: 08.08.2018
 */

namespace RRTest;

use Exception;

class RRConnector
{
    //https://apidev.rocketroute.com/wx/v1/service.wsdl

    /**
     * RRConnector constructor.
     */
    public function __construct()
    {
        $this->_initAppKey();
        return $this;
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


        try  {
            $this->_requestAppKey();
        } catch(Exception $e) {

        }


        return $this;
    }

    /**
     * checks if file with the app access key exists and the app access key is not expired yet
     *
     * @return bool
     */
    protected function _isAppKeyAvailable()
    {
        //todo
        return false;
    }

    protected function _requestAppKey()
    {
        //todo
        return $this;
    }

}