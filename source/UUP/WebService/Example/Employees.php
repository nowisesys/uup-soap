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

use RuntimeException;
use UUP\WebService\Example\Types\Company;
use UUP\WebService\Example\Types\Employee;
use UUP\WebService\Example\Types\Employees as EmployeeList;
use UUP\WebService\Example\Types\Job;
use UUP\WebService\Soap\SoapHandler;

/**
 * The employees service.
 * 
 * This example demonstrate using objects as input and output. The employees data is
 * taken from admin/employees.json and contains hierarcic data with relations. This
 * class acts as a proxy for the employees list object, another approach would be to 
 * proxied object direct as a web service.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class Employees implements SoapHandler
{

        /**
         * The employees list.
         * @var EmployeeList 
         */
        private $_employees;

        /**
         * Constructor.
         * @param string $root The project root directory.
         */
        public function __construct($root)
        {
                $this->initialize($root);
        }

        public function __destruct()
        {
                $this->shutdown();
        }

        /**
         * Initialize employess object.
         * @param string $root The project root directory.
         */
        private function initialize($root)
        {
                if (file_exists("/tmp/employees.dat")) {
                        $this->_employees = unserialize(
                            file_get_contents("/tmp/employees.dat")
                        );
                } else {
                        $this->_employees = new EmployeeList(
                            json_decode(
                                file_get_contents("$root/admin/employees.json")
                            )
                        );
                }
        }

        /**
         * Save employees object.
         */
        private function shutdown()
        {
                file_put_contents("/tmp/employees.dat", serialize($this->_employees));
        }

        /**
         * Get all employees.
         * @return EmployeeList
         */
        public function dumpEmployees()
        {
                return $this->_employees;
        }

        /**
         * Get all user identities.
         * @return string[]
         */
        public function getIdentities()
        {
                return $this->_employees->getIdentities();
        }

        /**
         * Get employee record.
         * @param string $user The user identity.
         * @return Employee
         */
        public function lookupEmployee($user)
        {
                return $this->_employees->lookup($user);
        }

        /**
         * Get employee records.
         * @param string $name The employee name.
         * @return Employee[]
         */
        public function findEmployee($name)
        {
                return $this->_employees->find($name);
        }

        /**
         * Get company of this employee.
         * @param string $user The user identity.
         * @return Company
         */
        public function getCompany($user)
        {
                if ($this->_employees->exist($user)) {
                        $employee = $this->_employees->lookup($user);
                        return $employee->company;
                }
        }

        /**
         * Get job of this employee.
         * @param string $user The user identity.
         * @return Job
         */
        public function getJob($user)
        {
                if ($this->_employees->exist($user)) {
                        $employee = $this->_employees->lookup($user);
                        return $employee->job;
                }
        }

        /**
         * Get user boss.
         * @param string $user The user identity.
         * @return Employee
         */
        public function getBoss($user)
        {
                if ($this->_employees->exist($user) == false) {
                        return null;
                } else {
                        $data = $this->_employees->lookup($user);
                }

                if ($data->boss == false) {
                        return null;
                } else {
                        $boss = $this->_employees->lookup($data->boss);
                        return $boss;
                }
        }

        /**
         * Add employee record.
         * 
         * @param string $user The user identity.
         * @param Employee $employee The employee record.
         * @throws RuntimeException
         */
        public function addEmployee($user, $employee)
        {
                if ($this->_employees->exist($user)) {
                        throw new RuntimeException("The employee already exist.");
                }

                $this->_employees->add($user, $employee);
        }

        /**
         * Update employee record.
         * 
         * @param string $user The user identity.
         * @param Employee $employee The employee record.
         * @throws RuntimeException
         */
        public function updateEmployee($user, $employee)
        {
                if (!$this->_employees->exist($user)) {
                        throw new RuntimeException("The employee don't exist.");
                }

                $this->_employees->update($user, $employee);
        }

        /**
         * Delete employee record.
         * 
         * @param string $user The user identity.
         * @throws RuntimeException
         */
        public function deleteEmployee($user)
        {
                if (!$this->_employees->exist($user)) {
                        throw new RuntimeException("The employee don't exist.");
                }

                $this->_employees->delete($user);
        }

        /**
         * Check if employee exists.
         * 
         * @param string $user The user identity.
         * @return bool
         */
        public function hasEmployee($user)
        {
                return $this->_employees->exist($user);
        }

}
