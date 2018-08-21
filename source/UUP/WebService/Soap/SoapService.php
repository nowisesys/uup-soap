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
// File:    SoapService.php
// Created: 2014-08-21 01:05:58
// 
// Author:  Anders Lövgren (Nowise Systems)
// 

namespace UUP\WebService\Soap;

use Exception;
use SoapFault;
use SoapServer;
use UUP\WebService\Wsdl\ServiceDescription;

/**
 * The SOAP service.
 * 
 * Use this class to publish an SOAP service. The class is initialized with the class
 * name or an object that will be handling the request:
 * 
 * <code>
 * $service = new SoapService(Calculator::class);
 * $service = new SoapService(new Calculator());
 * </code>
 * 
 * Unless each service have their own domain name (DNS), you need to specify the service 
 * location. Using namespace is optional but recommended:
 * 
 * <code>
 * $service->setLocation("http://localhost/uup-soap/calculator");
 * $service->setNamespace("http://company.com/soap/calculator");
 * </code>
 * 
 * The schema mode enable use of local WSDL file (for customization) and also defaults to 
 * system soap.wsdl_cache_enabled (disabled in non-schema mode). Once the service has been 
 * stabilized, it can be switched over to schema directory mode:
 * 
 * <code>
 * $service->setSchemaDirectory("/var/www/schemas/soap");
 * </code>
 * 
 * Call sendDescription() or sendDocumentation() to send service description (WSDL or API
 * HTML) to peer:
 * 
 * <code>
 * public function response($request, $service)
 * {
 *      switch ($request->target) {
 *              case 'wsdl':
 *                      $service->sendDescription();      // Send WSDL
 *                      break;
 *              case 'docs':
 *                      $service->sendDocumentation();    // Send API doc
 *                      break;
 *              case 'soap':
 *                      $service->handleRequest();        // Handle SOAP request
 *                      break;
 *      }
 * }
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapService
{

        /**
         * The SOAP service class.
         * @var string 
         */
        protected $_class;
        /**
         * The SOAP service instance.
         * @var SoapHandler
         */
        protected $_handler;
        /**
         * The WSDL schema directory.
         * @var string  
         */
        protected $_schemas;
        /**
         * The service description.
         * @var ServiceDescription 
         */
        protected $_description;
        /**
         * Use document literal wrapper.
         * @var bool 
         */
        protected $_wrapper = false;

        /**
         * Constructor.
         * @param string $class The SOAP service class.
         * @param string $location The service location.
         * @param string $namespace The service namespace.
         */
        public function __construct($class, $location = null, $namespace = null)
        {
                if (!extension_loaded('soap')) {
                        throw new SoapFault("Receiver", "The SOAP extension is not loaded.");
                }

                $this->setServiceClass($class);
                $this->setServiceDescription($location, $namespace);
        }

        /**
         * Set document literal wrapper mode.
         * @param bool $enable Use wrapped mode.
         */
        public function useWrapper($enable = true)
        {
                $this->_wrapper = $enable;
        }

        /**
         * Set the SOAP service location.
         * @param string $location
         */
        public function setLocation($location)
        {
                $description = $this->_description;
                $description->setServiceLocation($location);
        }

        /**
         * Set the SOAP service namespace.
         * @param string $namespace
         */
        public function setNamespace($namespace)
        {
                $description = $this->_description;
                $description->setNamespace($namespace);
        }

        /**
         * Set the SOAP service name.
         * @param string $name The service name.
         */
        public function setName($name)
        {
                $description = $this->_description;
                $description->setServiceName($name);
        }

        /**
         * Set SOAP handler.
         * 
         * Notice that the actual SOAP handler can be different from the object/class
         * passed to constructor, i.e. a wrapper.
         * 
         * @param SoapHandler $handler The SOAP handler.
         */
        public function setHandler($handler)
        {
                $this->_handler = $handler;
        }

        /**
         * Get SOAP handler.
         * @return SoapHandler
         */
        public function getHandler()
        {
                return $this->_handler;
        }

        /**
         * Set the WSDL schema directory.
         * @param string $schemas
         */
        public function setSchemaDirectory($schemas)
        {
                $this->_schemas = $schemas;
        }

        /**
         * Send WSDL for the SOAP service.
         */
        public function sendDescription()
        {
                $description = $this->_description;
                $description->send(ServiceDescription::FORMAT_WSDL);
        }

        /**
         * Send HTML documentation for the SOAP service.
         * @param string $format The documentation format (one of the ServiceDescription::FORMAT_XXX constants).
         */
        public function sendDocumentation($format = ServiceDescription::FORMAT_HTML)
        {
                $description = $this->_description;
                $description->send($format);
        }

        /**
         * Set service class and handler.
         * @param string|object $class The service class.
         */
        private function setServiceClass($class)
        {
                if (is_object($class)) {
                        $this->_handler = $class;
                        $this->_class = get_class($this->_handler);
                } else {
                        $this->_handler = new $class();
                        $this->_class = $class;
                }
        }

        /**
         * Set service description.
         * 
         * @param string $location The service location.
         * @param string $namespace The service namespace.
         */
        private function setServiceDescription($location, $namespace)
        {
                if (!isset($location) || !isset($namespace)) {
                        $action = new SoapAction();
                }

                $description = new ServiceDescription($this->_class);

                if (isset($location)) {
                        $description->setServiceLocation($location);
                } else {
                        $description->setServiceLocation($action);
                }

                if (isset($namespace)) {
                        $description->setNamespace($namespace);
                } else {
                        $description->setNamespace($action);
                }

                $this->_description = $description;
        }

        /**
         * Get service description object.
         * @return ServiceDescription
         */
        public function getServiceDescription()
        {
                return $this->_description;
        }

        /**
         * Get service description file.
         * 
         * Returns the path to the local customized WSDL file. This file 
         * contains a local customized version of the service description.
         * 
         * It depends on if setSchemaDirectory() has been called. Unless the
         * schema directory has been set, this function will always return null
         * to indicate that the SOAP server should always get the service 
         * description from an URL in WSDL mode.
         * 
         * @return string
         */
        private function getDescriptionFilename()
        {
                if (!isset($this->_schemas)) {
                        return null;
                }
                $name = strtolower(trim(strrchr($this->_class, '\\'), '\\'));
                $path = sprintf("%s/%s.wsdl", $this->_schemas, $name);

                return $path;
        }

        /**
         * Handle the SOAP request.
         */
        public function handleRequest()
        {
                $description = $this->_description;
                $generator = $description->getGenerator();

                // 
                // Create cached service description:
                // 
                if (($filename = $this->getDescriptionFilename()) != null) {
                        if (!file_exists($filename)) {
                                $description->save($filename);
                        }
                }

                // 
                // Set URI of service description:
                // 
                if ($filename != null && file_exists($filename)) {
                        $description->setServiceDocument($filename);
                } else {
                        $filename = $description->getServiceLocation() . '?wsdl';
                        $description->setServiceDocument($filename);
                }

                // 
                // Turn off WSDL cache when not using schema directory:
                // 
                if (!isset($this->_schemas)) {
                        ini_set("soap.wsdl_cache_enabled", "0");
                }

                // 
                // Use SOAP document/literal mode:
                // 
                $options = array(
                        'uri'      => $description->getServiceDocument(),
                        'location' => $description->getServiceLocation(),
                        'style'    => SOAP_DOCUMENT,
                        'use'      => SOAP_LITERAL,
                        'classmap' => $generator->getClassMap()
                );

                // 
                // Wrap handler if requested:
                // 
                if ($this->_wrapper) {
                        $this->_handler = new Wrapper\DocumentLiteral($this->_handler);
                }

                // 
                // Create SOAP server using WSDL mode:
                // 
                $server = new SoapServer($description->getServiceDocument(), $options);

                // 
                // Handle request using handler object:
                // 
                $server->setObject($this->_handler);

                // 
                // This is where we actually handle the request. If a called
                // method throws, then convert the exception to SOAP fault
                // object that is propagated to SOAP client.
                // 
                // See http://www.w3.org/TR/soap12-part1/#faultcodes
                //
                try {
                        $server->handle();
                } catch (Exception $exception) {
                        $server->fault("Receiver", $exception->getMessage());
                        throw $exception;       // Handle exception upstream
                }
        }

}
