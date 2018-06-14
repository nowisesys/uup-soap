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
// File:    ServiceDescription.php
// Created: 2014-10-10 03:38:57
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace UUP\WebService\Wsdl;

/**
 * SOAP service description (WSDL).
 * @author Anders Lövgren (Nowise Systems)
 */
class ServiceDescription
{

        /**
         * Format output as HTML.
         */
        const FORMAT_HTML = 'html';
        /**
         * Format output as XML.
         */
        const FORMAT_XML = 'xml';

        /**
         * The SOAP service class.
         * @var string 
         */
        private $_class;
        /**
         * The SOAP service location (URL).
         * @var string 
         */
        private $_location;
        /**
         * The SOAP service document (file or URL).
         * @var string 
         */
        private $_document;
        /**
         * The SOAP service namespace.
         * @var string 
         */
        private $_namespace;
        /**
         * The WSDL generator.
         * @var Generator 
         */
        private $_generator;
        /**
         * The class path for complex types.
         * @var array 
         */
        private $_classpath = array();

        /**
         * Constructor.
         * @param string $class The SOAP service class.
         * @param string $location Service location (endpoint).
         * @param string $namespace Service namespace.
         */
        public function __construct($class, $location = null, $namespace = null)
        {
                $this->_class = $class;
                $this->_location = $location;
                $this->_namespace = $namespace;
        }

        /**
         * Append namespace to class path.
         * 
         * The class path is an array of namespaces used when loading classes that
         * is mapped to complex types that is used ether as method arguments or as
         * return type.
         * 
         * @param string $namespace The namesspace string.
         */
        public function addClassPath($namespace)
        {
                $this->_classpath[] = $namespace;
        }

        /**
         * Set namespaces for class path.
         * 
         * The class path is an array of namespaces used when loading classes that
         * is mapped to complex types that is used ether as method arguments or as
         * return type.
         * 
         * @param array $namespaces The namesspaces array.
         */
        public function setClassPath($namespaces)
        {
                $this->_classpath = $namespaces;
        }

        /**
         * Ser SOAP service document (file or URL).
         * @param string $location The service document.
         */
        public function setServiceDocument($location)
        {
                $this->_document = $location;
        }

        /**
         * Get SOAP service document (file or URL).
         * @return string
         */
        public function getServiceDocument()
        {
                return $this->_document;
        }

        /**
         * Set SOAP service location (endpoint).
         * @param string $location
         */
        public function setServiceLocation($location)
        {
                $this->_location = $location;
        }

        /**
         * Get SOAP service location (endpoint)
         * @return string
         */
        public function getServiceLocation()
        {
                return $this->_location;
        }

        /**
         * Set SOAP service namespace.
         * @param string $namespace
         */
        public function setNamespace($namespace)
        {
                $this->_namespace = $namespace;
        }

        /**
         * Get service description generator.
         * @return Generator
         */
        public function getGenerator()
        {
                if (!isset($this->_generator)) {
                        $this->_generator = new Generator($this->_class, $this->_location, $this->_namespace);
                        $this->_generator->setClassPath($this->_classpath);
                        $this->_generator->discover();
                }
                return $this->_generator;
        }

        /**
         * Get service description (WSDL).
         * @param string $format The output format (html or xml).
         * @return string
         */
        private function getDescription($format)
        {
                $generator = $this->getGenerator();
                $document = $generator->getDocument();

                switch ($format) {
                        case self::FORMAT_HTML:
                                return $document->saveHTML();
                        case self::FORMAT_XML:
                                return $document->saveXML();
                }
        }

        /**
         * Get service description (WSDL).
         * @param string $format The output format (html or xml).
         * @return string
         */
        public function dump($format = self::FORMAT_XML)
        {
                return $this->getDescription($format);
        }

        /**
         * Send service description (WSDL) to stdout.
         * @param string $format The output format (html or xml).
         */
        public function send($format = self::FORMAT_HTML)
        {
                echo $this->getDescription($format);
        }

        /**
         * Save service description (WSDL) to file.
         * @param string $filename The destination file.
         * @param string $format The output format (html or xml).
         */
        public function save($filename, $format = self::FORMAT_XML)
        {
                file_put_contents($filename, $this->getDescription($format));
        }

}
