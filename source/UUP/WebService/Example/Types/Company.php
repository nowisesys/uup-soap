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

namespace UUP\WebService\Example\Types;

/**
 * The company class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Company
{

        /**
         * The company name.
         * @var string 
         */
        public $name;
        /**
         * The company address.
         * @var string 
         */
        public $address;

        /**
         * Constructor.
         * @param array $data The company data.
         */
        public function __construct($data)
        {
                if (isset($data->name)) {
                        $this->name = $data->name;
                }
                if ($data->address) {
                        $this->address = $data->address;
                }
        }

}
