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

namespace UUP\WebService\Soap;

/**
 * The SOAP action.
 * 
 * @property-read string $scheme The HTTP scheme.
 * @property-read string $host The server host.
 * @property-read int $port The server port.
 * @property-read string $name The service name.
 *
 * @see https://www.w3.org/TR/2000/NOTE-SOAP-20000508/#_Toc478383528
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapAction
{

        /**
         * The HTTP scheme.
         * @var string 
         */
        private $_scheme;
        /**
         * The server host.
         * @var string 
         */
        private $_host;
        /**
         * The server port.
         * @var int 
         */
        private $_port;
        /**
         * The service name.
         * @var string 
         */
        private $_name;

        /**
         * Constructor.
         * 
         * @param string $scheme The HTTP scheme.
         * @param string $host The server host.
         * @param int $port The server port.
         * @param string $name The service name.
         */
        public function __construct($scheme = null, $host = null, $port = false, $name = null)
        {
                if (isset($scheme)) {
                        $this->_scheme = $scheme;
                } elseif (filter_has_var(INPUT_SERVER, 'REQUEST_SCHEME')) {
                        $this->_scheme = filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
                } else {
                        $this->_scheme = "http";
                }

                if (isset($host)) {
                        $this->_host = $host;
                } elseif (filter_has_var(INPUT_SERVER, 'SERVER_NAME')) {
                        $this->_host = filter_input(INPUT_SERVER, 'SERVER_NAME');
                } else {
                        $this->_host = "localhost";
                }

                if ($port) {
                        $this->_port = $port;
                } elseif (filter_has_var(INPUT_SERVER, 'SERVER_PORT')) {
                        $this->_port = filter_input(INPUT_SERVER, 'SERVER_PORT');
                } else {
                        $this->_port = 80;
                }

                if (isset($name)) {
                        $this->_name = $name;
                } elseif (filter_has_var(INPUT_SERVER, 'SCRIPT_NAME')) {
                        $this->_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
                } elseif (filter_has_var(INPUT_SERVER, 'PHP_SELF')) {
                        $this->_name = filter_input(INPUT_SERVER, 'PHP_SELF');
                }

                if ($this->_name[0] == '/') {
                        $this->_name = ltrim($this->_name, '/');
                }
        }

        public function __get($name)
        {
                switch ($name) {
                        case 'scheme':
                                return $this->_scheme;
                        case 'host':
                                return $this->_host;
                        case 'port':
                                return $this->_port;
                        case 'name':
                                return $this->_name;
                }
        }

        public function __toString()
        {
                return $this->getURL();
        }

        /**
         * Set HTTP scheme.
         * @param string $scheme The HTTP scheme.
         */
        public function setScheme($scheme)
        {
                $this->_scheme = $scheme;
        }

        /**
         * Set server host.
         * @param string $host The server host.
         */
        public function setHost($host)
        {
                $this->_host = $host;
        }

        /**
         * Set server port.
         * @param int $port The server port.
         */
        public function setPort($port)
        {
                $this->_port = $port;
        }

        /**
         * Set service name.
         * @param string $name The service name.
         */
        public function setName($name)
        {
                $this->_name = $name;
        }

        /**
         * Get action URL.
         * @return string
         */
        public function getURL()
        {
                if ($this->_port == 80 || $this->_port == 443) {
                        return sprintf("%s://%s/%s", $this->_scheme, $this->_host, $this->_name);
                } else {
                        return sprintf("%s://%s:%d/%s", $this->_scheme, $this->_host, $this->_port, $this->_name);
                }
        }

}
