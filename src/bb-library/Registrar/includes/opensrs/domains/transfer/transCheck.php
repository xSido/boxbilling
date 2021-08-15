<?php
/*
 *  Required object values:
 *  data -
 */

class transCheck extends openSRS_base
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
            !isset($this->_dataObject->data->domain) ||
            $this->_dataObject->data->domain == ""
        ) {
            trigger_error(
                "oSRS Error - domain is not defined.",
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
            "action" => "CHECK_TRANSFER",
            "object" => "DOMAIN",
            "attributes" => [
                "domain" => $this->_dataObject->data->domain,
            ],
        ];

        // Command optional values
        if (
            isset($this->_dataObject->data->check_status) &&
            $this->_dataObject->data->check_status != ""
        ) {
            $cmd["attributes"]["check_status"] =
                $this->_dataObject->data->check_status;
        }
        if (
            isset($this->_dataObject->data->get_request_address) &&
            $this->_dataObject->data->get_request_address != ""
        ) {
            $cmd["attributes"]["get_request_address"] =
                $this->_dataObject->data->get_request_address;
        }

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
