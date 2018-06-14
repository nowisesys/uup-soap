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
 * The job class.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Job
{

        /**
         * The job title.
         * @var string 
         */
        public $title;
        /**
         * The montly salary.
         * @var int 
         */
        public $salary;

        /**
         * Constructor.
         * @param array $data The job data.
         */
        public function __construct($data)
        {
                if (isset($data->title)) {
                        $this->title = $data->title;
                }
                if (isset($data->salary)) {
                        $this->salary = $data->salary;
                }
        }

}
