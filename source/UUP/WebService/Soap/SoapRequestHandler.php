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

use Exception;
use SoapServer;
use UUP\WebService\Soap\Wrapper\DocumentLiteral;
use UUP\WebService\Wsdl\ServiceDescription;

/**
 * The SOAP request handler.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapRequestHandler
{

        /**
         * The SOAP service instance.
         * @var SoapHandler
         */
        protected $_handler;
        /**
         * The service description.
         * @var ServiceDescription 
         */
        protected $_svcdesc;
        /**
         * Cached service description.
         * @var string
         */
        protected $_filename = false;
        /**
         * Use document literal wrapper.
         * @var bool 
         */
        protected $_wrapper = false;

        /**
         * Constructor.
         * 
         * @param SoapHandler $handler The SOAP handler.
         * @param ServiceDescription $description The service description.
         */
        public function __construct($handler, $description)
        {
                $this->_handler = $handler;
                $this->_svcdesc = $description;
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
         * Set schema cache filename.
         * @param string $filename The filename.
         */
        public function setDescriptionFilename($filename)
        {
                $this->_filename = $filename;
        }

        /**
         * Create cached service description.
         * 
         * @param string $filename The cache filename.
         * @param ServiceDescription $description The service description.
         */
        private function setSchemaCache($filename, $description)
        {
                if (!$filename) {
                        ini_set("soap.wsdl_cache_enabled", "0");
                        return;
                }

                if (!file_exists($filename)) {
                        $description->save($filename);
                }
        }

        /**
         * Set schema location.
         * 
         * @param string $filename The cache filename.
         * @param ServiceDescription $description The service description.
         */
        private function setSchemaDocument($filename, $description)
        {
                if (file_exists($filename)) {
                        $description->setServiceDocument($filename);
                } else {
                        $filename = $description->getServiceLocation() . '?wsdl=1';
                        $description->setServiceDocument($filename);
                }
        }

        /**
         * Get SOAP request handler.
         * @return SoapHandler
         */
        private function getHandler()
        {
                if ($this->_wrapper) {
                        return new DocumentLiteral($this->_handler);
                } else {
                        return $this->_handler;
                }
        }

        /**
         * Handle the SOAP request.
         * @throws Exception
         */
        public function process()
        {
                $description = $this->_svcdesc;
                $generator = $description->getGenerator();

                // 
                // Setup schema handling:
                // 
                $this->setSchemaCache($this->_filename, $description);
                $this->setSchemaDocument($this->_filename, $description);

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
                // Create SOAP server using WSDL mode:
                // 
                $server = new SoapServer($description->getServiceDocument(), $options);

                // 
                // Handle request using handler object:
                // 
                $server->setObject($this->getHandler());

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
