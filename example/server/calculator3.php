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

use UUP\WebService\Example\Calculator;
use UUP\WebService\Soap\SoapRequest;
use UUP\WebService\Soap\SoapService;

// 
// Helps detect SOAP request:
// 
$request = new SoapRequest();

// 
// Create the SOAP service:
// 
$service = new SoapService(Calculator::class);
$service->useWrapper();
$service->setName("My Calculator");

// 
// Handle the SOAP request:
// 
switch ($request->target) {
        case SoapRequest::TARGET_DOCS:
                $service->sendDocumentation();
                break;
        case SoapRequest::TARGET_WSDL:
                $service->sendDescription();
                break;
        case SoapRequest::TARGET_SOAP:
                $service->handleRequest();
                break;
}
