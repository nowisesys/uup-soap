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

use RuntimeException;
use UUP\WebService\Wsdl\ServiceDescription;

/**
 * Detect and handle SOAP service request.
 * 
 * Using this class is fully optional. The benefit is 
 * 
 * This is a helper class for handling WSDL and API document request in addition to
 * ordinary SOAP request. The default target is to send the service API documentation, 
 * but can be overridden by contructor argument.
 * 
 * This is the mapping of request to target action:
 * 
 * <code>
 * wsdl=* and target=wsdl       (GET, POST)     Get WSDL document.
 * docs=* and target=docs       (GET, POST)     Get API documantation.
 * soap=* and target=soap       (GET, POST)     Handle SOAP request.
 * </code>
 * 
 * In addition, a SOAP request is also detected from content type in the HTTP header. The official 
 * MIME type seems to be application/soap+xml, with optional character encoding.
 * 
 * <code>
 * $handler = new Calculator();
 * $service = new SoapService($handler, "http://company.com/soap/calculator");
 * 
 * $request = new SoapRequest();
 * $request->process($service);
 * </code>
 * 
 * @property-read string $target The request target.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapRequest
{

        /**
         * Target is the API documentation.
         */
        const TARGET_DOCS = 'docs';
        /**
         * Target is the SOAP handler.
         */
        const TARGET_SOAP = 'soap';
        /**
         * Target is the WSDL document.
         */
        const TARGET_WSDL = 'wsdl';
        /**
         * The MIME (content type) for SOAP.
         */
        const SOAP_MIME_TYPE = 'application/soap+xml';

        /**
         * The request target
         * @var string 
         */
        private $_target = self::TARGET_SOAP;
        /**
         * The documentation format.
         * @var string 
         */
        private $_format;

        /**
         * Constructor.
         * @param string $default The default target.
         */
        public function __construct($default = null)
        {
                if (isset($default)) {
                        $this->_target = $default;
                }
                if (filter_has_var(INPUT_GET, self::TARGET_WSDL) ||
                    filter_has_var(INPUT_POST, self::TARGET_WSDL)) {
                        $this->_target = self::TARGET_WSDL;
                }
                if (filter_has_var(INPUT_GET, self::TARGET_DOCS) ||
                    filter_has_var(INPUT_POST, self::TARGET_DOCS)) {
                        $this->_target = self::TARGET_DOCS;
                        $this->_format = filter_input(INPUT_GET, self::TARGET_DOCS);
                }
                if (filter_has_var(INPUT_GET, self::TARGET_SOAP) ||
                    filter_has_var(INPUT_POST, self::TARGET_SOAP)) {
                        $this->_target = self::TARGET_SOAP;
                }

                if (filter_has_var(INPUT_GET, 'target')) {
                        $this->_target = filter_input(INPUT_GET, 'target');
                }
                if (filter_has_var(INPUT_POST, 'target')) {
                        $this->_target = filter_input(INPUT_POST, 'target');
                }

                if (filter_has_var(INPUT_SERVER, 'CONTENT_TYPE')) {
                        $content = filter_input(INPUT_SERVER, 'CONTENT_TYPE');
                        if (preg_match('|^application/soap+xml|', $content)) {
                                $this->_target = self::TARGET_SOAP;
                        }
                }

                if (!isset($this->_format)) {
                        $this->_format = ServiceDescription::FORMAT_HTML;
                }
        }

        public function __get($name)
        {
                if ($name == 'target') {
                        return $this->_target;
                }
        }

        /**
         * Process SOAP request.
         * @param SoapService $service The SOAP service.
         * @param SoapHandler $handler The SOAP handler.
         */
        public function process($service, $handler = null)
        {
                if (!isset($handler)) {
                        $handler = $service->getHandler();
                }

                if (!isset($handler)) {
                        throw new RuntimeException("The SOAP service handler is unset.");
                } else {
                        $service->setHandler($handler);
                }

                switch ($this->_target) {
                        case self::TARGET_DOCS:
                                $service->sendDocumentation($this->_format);
                                break;
                        case self::TARGET_WSDL:
                                $service->sendDescription();
                                break;
                        case self::TARGET_SOAP:
                                $service->handleRequest();
                                break;
                }
        }

}
