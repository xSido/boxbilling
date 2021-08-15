<?php
/*
 *  Required object values:
 *  data -
 */

class subresPay extends openSRS_base
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
            !isset($this->_dataObject->data->amount) ||
            $this->_dataObject->data->amount == ""
        ) {
            trigger_error(
                "oSRS Error - amount is not defined.",
                E_USER_WARNING
            );
            $allPassed = false;
        }
        if (
            !isset($this->_dataObject->data->username) ||
            $this->_dataObject->data->username == ""
        ) {
            trigger_error(
                "oSRS Error - username is not defined.",
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
            "object" => "SUBRESELLER",
            "action" => "PAY",
            "attributes" => [
                "amount" => $this->_dataObject->data->amount,
                "username" => $this->_dataObject->data->username,
            ],
        ];

        $xmlCMD = $this->_opsHandler->encode($cmd); // Flip Array to XML
        $XMLresult = $this->send_cmd($xmlCMD); // Send XML
        $arrayResult = $this->_opsHandler->decode($XMLresult); // Flip XML to Array

        // Results
        $this->resultFullRaw = $arrayResult;
        if (isset($arrayResult["attributes"])) {
            $this->resultRaw = $arrayResult["attributes"];
        } else {
            $this->resultRaw = $arrayResult;
        }
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
