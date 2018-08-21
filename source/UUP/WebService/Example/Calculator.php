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

namespace UUP\WebService\Example;

use InvalidArgumentException;
use UUP\WebService\Soap\SoapHandler;

/**
 * The calculator SOAP service.
 * 
 * It's almost mandatory to provide a calculator web service for testing,
 * so here we go.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Calculator implements SoapHandler
{

        /**
         * Add two numbers.
         * 
         * @param float $a The first number.
         * @param float $b The second number.
         * @return float
         */
        public function add($a, $b)
        {
                return $a + $b;
        }

        /**
         * Substract two numbers.
         * 
         * @param float $a The first number.
         * @param float $b The second number.
         * @return float
         */
        public function substract($a, $b)
        {
                return $a - $b;
        }

        /**
         * Divide two numbers.
         * 
         * @param float $a The first number.
         * @param float $b The second number.
         * @return float
         */
        public function divide($a, $b)
        {
                if ($b == 0) {
                        throw new InvalidArgumentException("Division by zero.");
                } else {
                        return $a / $b;
                }
        }

        /**
         * Multiply two numbers.
         * 
         * @param float $a The first number.
         * @param float $b The second number.
         * @return float
         */
        public function multiply($a, $b)
        {
                return $a * $b;
        }

        /**
         * Calculate reminder.
         * 
         * @param float $a The first number.
         * @param float $b The second number.
         * @return float
         */
        public function modulo($a, $b)
        {
                return fmod($a, $b);
        }

}
