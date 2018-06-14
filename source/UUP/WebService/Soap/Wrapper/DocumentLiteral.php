<?php

/*
 * Copyright (C) 2014-2018 Anders Lövgren (Nowise Systems)
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

// 
// File:    DocumentLiteral.php
// Created: 2014-10-16 03:13:46
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace UUP\WebService\Soap\Wrapper;

use UUP\WebService\Soap\SoapHandler;

/**
 * Document literal wrapper class.
 * 
 * Wrapper for SOAP method call supporting standard access to arguments and
 * normal way of returning method result. 
 * 
 * In non-wrapped mode, the argument is accessed thru a standard PHP object 
 * and return values needs to be set in a returned array:
 * 
 * <code>
 * public function add($num1, $num2)
 * {
 *      $sum = $num1->num1 + $num1->num2;       // All args in $num1
 *      return array('return' => $sum);         // Must use array
 * }
 * </code>
 * 
 * In wrapped mode, the method arguments are accessed as normal parameters 
 * and the return value can be returned as usual:
 * 
 * <code>
 * public function add($num1, $num2)
 * {
 *      $sum = $num1 + $num2;
 *      return $sum;
 * }
 * </code>
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class DocumentLiteral implements SoapHandler
{

        /**
         * The target SOAP service.
         * @var SoapHandler 
         */
        private $_service;

        /**
         * Constructor.
         * @param SoapHandler $service The wrapped SOAP service object.
         */
        public function __construct($service)
        {
                $this->_service = $service;
        }

        public function __call($name, $arguments)
        {
                $response = call_user_func_array(
                    array($this->_service, $name), (array) ($arguments[0])
                );
                return array('return' => $response);
        }

}
