<?php

/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("../../vendor/autoload.php");

// 
// The calculator service script:
// 
$script = "calculator3.php";

// 
// SOAP service endpoint helper.
// 
class ServiceEndpoint
{

        private $_location;

        public function __construct($script)
        {
                $this->_location = self::getFullUrl(self::getEndpoint($script));
        }

        public function __toString()
        {
                return $this->_location;
        }

        private static function getEndpoint($script)
        {
                $location = sprintf("/%s/server/%s", basename(realpath("../..")), $script);
                return self::getFullUrl($location);
        }

        private static function getFullUrl($location)
        {
                $http = filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
                $host = filter_input(INPUT_SERVER, 'SERVER_NAME');
                $port = filter_input(INPUT_SERVER, 'SERVER_PORT');

                return sprintf("%s://%s:%d/%s", $http, $host, $port, trim($location, '/'));
        }

}

//
// The SOAP service client.
// 
class CalculatorClient
{

        private $_client;

        public function __construct($endpoint)
        {
                $this->_client = new SoapClient(sprintf("%s?wsdl=1", (string) $endpoint));
        }

        public function add(float $a, float $b)
        {
                $result = $this->_client->add(array(
                        'a' => $a,
                        'b' => $b
                ));
                return $result->return;
        }

        public function substract(float $a, float $b)
        {
                $result = $this->_client->substract(array(
                        'a' => $a,
                        'b' => $b
                ));
                return $result->return;
        }

        public function divide(float $a, float $b)
        {
                $result = $this->_client->divide(array(
                        'a' => $a,
                        'b' => $b
                ));
                return $result->return;
        }

        public function multiply(float $a, float $b)
        {
                $result = $this->_client->multiply(array(
                        'a' => $a,
                        'b' => $b
                ));
                return $result->return;
        }

        public function modulo(float $a, float $b)
        {
                $result = $this->_client->modulo(array(
                        'a' => $a,
                        'b' => $b
                ));
                return $result->return;
        }

}

// 
// Hardcode service endpoint in CLI-mode:
// 
if (filter_has_var(INPUT_SERVER, 'SERVER_ADDR')) {
        $endpoint = new ServiceEndpoint($script);
} else {
        $endpoint = sprintf("http://localhost/uup-soap/server/%s", $script);
}

// 
// Create SOAP client:
// 
$client = new CalculatorClient($endpoint);

// 
// The input data:
// 
$params = (object) array(
            'a' => 5.7,
            'b' => 1.3
);

// 
// Call some methods:
// 
printf("      add(%f, %f) => %f\n", $params->a, $params->b, $client->add($params->a, $params->b));
printf("substract(%f, %f) => %f\n", $params->a, $params->b, $client->substract($params->a, $params->b));
printf("   divide(%f, %f) => %f\n", $params->a, $params->b, $client->divide($params->a, $params->b));
printf(" multiply(%f, %f) => %f\n", $params->a, $params->b, $client->multiply($params->a, $params->b));
printf("   modulo(%f, %f) => %f\n", $params->a, $params->b, $client->modulo($params->a, $params->b));
