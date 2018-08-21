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

use UUP\WebService\Example\Employees;
use UUP\WebService\Soap\SoapRequest;
use UUP\WebService\Soap\SoapService;
use UUP\WebService\Soap\Wrapper\DocumentLiteral;

// 
// Create the SOAP service:
// 
$handler = new Employees("../..");
$wrapper = new DocumentLiteral($handler);
$service = new SoapService(Employees::class);
$service->setHandler($wrapper);

// 
// Add custom class path and map:
// 
$description = $service->getServiceDescription();
$description->addClassPath("UUP\WebService\Example\Types");
$description->addClassMap('EmployeeList', 'Employees');         // Class alias example

// 
// Handle the SOAP request:
// 
$request = new SoapRequest();
$request->process($service);
