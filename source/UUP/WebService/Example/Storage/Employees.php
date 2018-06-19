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

namespace UUP\WebService\Example\Storage;

use RuntimeException;
use UUP\WebService\Example\Types\Employee;

/**
 * The employees store.
 * 
 * This class is a simple dropin replacement for an database where we
 * normally choose to store employee records.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Employees
{

        /**
         * The employees list.
         * @var Employee[] 
         */
        private $_employees = array();

        /**
         * Constructor.
         * @param array $employees The employees array.
         */
        public function __construct($employees)
        {
                foreach ($employees as $user => $data) {
                        $this->_employees[$user] = new Employee($data);
                }
        }

        /**
         * Get all employees.
         * @return Employees[]
         */
        public function getAll()
        {
                return $this->_employees;
        }

        /**
         * Get all identities (username).
         * @return array
         */
        public function getIdentities()
        {
                return array_keys($this->_employees);
        }

        /**
         * Get employee object.
         * @param string $user The user identity.
         * @return Employee
         */
        public function lookup($user)
        {
                return $this->_employees[$user];
        }

        /**
         * Get employee object.
         * @param string $name The person name.
         * @return Employee[]
         */
        public function find($name)
        {
                $employees = array();

                foreach ($this->_employees as $employee) {
                        if ($employee->name == $name) {
                                $employees[] = $employee;
                        }
                }

                return $employees;
        }

        /**
         * Check if user identity exists.
         * @param string $user The user identity.
         * @return boolean
         */
        public function exist($user)
        {
                return isset($this->_employees[$user]);
        }

        /**
         * Add employee record.
         * 
         * @param string $user The user identity.
         * @param Employee $employee The employee object.
         * @throws RuntimeException
         */
        public function add($user, $employee)
        {
                $this->_employees[$user] = $employee;
        }

        /**
         * Update employee record.
         * 
         * @param string $user The user identity.
         * @param Employee $employee The employee object.
         * @throws RuntimeException
         */
        public function update($user, $employee)
        {
                $this->_employees[$user] = $employee;
        }

        /**
         * Delete employee record.
         * 
         * @param string $user The user identity.
         * @throws RuntimeException
         */
        public function delete($user)
        {
                unset($this->_employees[$user]);
        }

}
