<?php
/*
 *  Required object values:
 *  - none -
 *
 *  Optional Data:
 *  data - owner_email, admin_email, billing_email, tech_email, del_from, del_to, exp_from, exp_to, page, limit
 */

class lookupGetNotes extends openSRS_base
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

        if (!isset($this->_dataObject->data->domain)) {
            trigger_error(
                "oSRS Error - domain is not defined.",
                E_USER_WARNING
            );
            $allPassed = false;
        }

        if (!isset($this->_dataObject->data->type)) {
            trigger_error("oSRS Error - type is not defined.", E_USER_WARNING);
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
            "action" => "GET_NOTES",
            "object" => "DOMAIN",
            "attributes" => [
                "domain" => $this->_dataObject->data->domain,
                "type" => $this->_dataObject->data->type,
            ],
        ];

        // Command optional values
        if (
            isset($this->_dataObject->data->page) &&
            $this->_dataObject->data->page != ""
        ) {
            $cmd["attributes"]["page"] = $this->_dataObject->data->page;
        }
        if (
            isset($this->_dataObject->data->limit) &&
            $this->_dataObject->data->limit != ""
        ) {
            $cmd["attributes"]["limit"] = $this->_dataObject->data->limit;
        }
        if (
            isset($this->_dataObject->data->order_id) &&
            $this->_dataObject->data->order_id != ""
        ) {
            $cmd["attributes"]["order_id"] = $this->_dataObject->data->order_id;
        }
        if (
            isset($this->_dataObject->data->transfer_id) &&
            $this->_dataObject->data->transfer_id != ""
        ) {
            $cmd["attributes"]["transfer_id"] =
                $this->_dataObject->data->transfer_id;
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
