<?php
/*
 *  Required object values:
 *  data -
 */

class subuserGetInfo extends openSRS_base
{
    private $_dataObject;
    private $_formatHolder = "";
    public $resultFullRaw;
    public $resultRaw;
    public $resultFullFormated;
    public $resultFormated;

    public function __construct($formatString, $dataObject)
    {
        parent::__construct();
        $this->_dataObject = $dataObject;
        $this->_formatHolder = $formatString;
        $this->_validateObject();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    // Validate the object
    private function _validateObject()
    {
        $allPassed = true;

        // Command required values
        if (
            (!isset($this->_dataObject->data->cookie) ||
                $this->_dataObject->data->cookie == "") &&
            (!isset($this->_dataObject->data->bypass) ||
                $this->_dataObject->data->bypass == "")
        ) {
            trigger_error(
                "oSRS Error - cookie / bypass is not defined.",
                E_USER_WARNING
            );
            $allPassed = false;
        }
        if (
            $this->_dataObject->data->cookie != "" &&
            $this->_dataObject->data->bypass != ""
        ) {
            trigger_error(
                "oSRS Error - Both cookie and bypass cannot be set in one call.",
                E_USER_WARNING
            );
            $allPassed = false;
        }

        // Run the command
        if ($allPassed) {
            // Execute the command
            $this->_processRequest();
        } else {
            trigger_error("oSRS Error - Incorrect call.", E_USER_WARNING);
        }
    }

    // Post validation functions
    private function _processRequest()
    {
        $cmd = [
            "protocol" => "XCP",
            "action" => "GET",
            "object" => "USERINFO",
            //			'registrant_ip' => '216.40.46.115',
            //			'cookie' => $this->_dataObject->data->cookie
        ];

        // Cookie / bypass
        if (
            isset($this->_dataObject->data->cookie) &&
            $this->_dataObject->data->cookie != ""
        ) {
            $cmd["cookie"] = $this->_dataObject->data->cookie;
        }
        if (
            isset($this->_dataObject->data->bypass) &&
            $this->_dataObject->data->bypass != ""
        ) {
            $cmd["domain"] = $this->_dataObject->data->bypass;
        }

        $xmlCMD = $this->_opsHandler->encode($cmd); // Flip Array to XML
        $XMLresult = $this->send_cmd($xmlCMD); // Send XML
        $arrayResult = $this->_opsHandler->decode($XMLresult); // Flip XML to Array

        // Results
        $this->resultFullRaw = $arrayResult;
        $this->resultRaw = $arrayResult;
        $this->resultFullFormated = convertArray2Formated(
            $this->_formatHolder,
            $this->resultFullRaw
        );
        $this->resultFormated = convertArray2Formated(
            $this->_formatHolder,
            $this->resultRaw
        );
    }
}
