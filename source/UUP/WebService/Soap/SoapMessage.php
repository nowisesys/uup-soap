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

use DOMDocument;
use DOMElement;
use UUP\WebService\Wsdl\Generator;

/**
 * The SOAP message.
 * 
 * Use this class to construct SOAP message and response. This class is primarly 
 * meant to be used by the WSDL format classes for generating example XML when
 * documenting the SOAP API.
 *
 * The same class can be used for creating standard messages for invoking a SOAP
 * method or creating sample messages for method documentation.
 * 
 * <code>
 * // 
 * // Generate message for invoking SOAP method:
 * // 
 * $object  = new SoapMessage($generator);
 * $message = $object->getMessage("addperson", array(
 *      'user'  => 'anders',
 *      'name'  => 'Anders Lövgren',
 *      'hobby' => 'soap'
 * ));
 * 
 * // 
 * // Generate message for documenting SOAP method:
 * // 
 * $object  = new SoapMessage($generator);
 * $message = $object->getMessage("addperson", array(
 *      array( 
 *              'name' => 'user', 
 *              'type' => 'string'
 *      ),
 *      array( 
 *              'name' => 'name', 
 *              'type' => 'string'
 *      ),
 *      array( 
 *              'name' => 'hobby', 
 *              'type' => 'string'
 *      )
 * ));
 * </code>
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class SoapMessage
{

        /**
         * The WSDL generator.
         * @var Generator 
         */
        private $_generator;

        /**
         * Constructor.
         * @param Generator $generator The WSDL generator.
         */
        public function __construct($generator)
        {
                $this->_generator = $generator;
        }

        /**
         * Get SOAP method message.
         * 
         * @param string $name The method name.
         * @param array $data The method description.
         * @return string
         */
        public function getMessage($name, $data)
        {
                $document = new DOMDocument("1.0", "utf-8");

                $envelop = $document->createElementNS("http://www.w3.org/2003/05/soap-envelope/", "soap:envelope");
                $envelop->setAttribute("soap:encodingStyle", "http://www.w3.org/2003/05/soap-encoding");
                $document->appendChild($envelop);

                $body = $document->createElement("soap:body");
                $body->setAttribute("xmlns", $this->_generator->ns);
                $envelop->appendChild($body);

                $method = $body->appendChild(new DomElement($name));

                foreach ($data as $name => $type) {
                        if (is_string($name)) {
                                $this->addParameter($method, $name, $type);
                        } else {
                                $this->addParameter($method, $type['name'], $type['type']);
                        }
                }
                return $document->saveXML();
        }

        /**
         * Get SOAP method response.
         * 
         * @param string $name The method name.
         * @param array $data The method description.
         * @return string
         */
        public function getResponse($name, $data)
        {
                $document = new DOMDocument("1.0", "utf-8");

                $envelop = $document->createElementNS("http://www.w3.org/2003/05/soap-envelope/", "soap:envelope");
                $envelop->setAttribute("soap:encodingStyle", "http://www.w3.org/2003/05/soap-encoding");
                $document->appendChild($envelop);

                $body = $document->createElement("soap:body");
                $body->setAttribute("xmlns", $this->_generator->ns);
                $envelop->appendChild($body);

                if (count($data) == 0) {
                        $method = $body->appendChild(new DomElement($name));
                        $return = $method->appendChild(new DomElement("return"));
                } else {
                        $return = $body->appendChild(new DomElement($name));
                }

                foreach ($data as $name => $type) {
                        if (is_string($name)) {
                                $this->addParameter($return, $name, $type);
                        } else {
                                $this->addParameter($return, $type['name'], $type['type']);
                        }
                }
                return $document->saveXML();
        }

        /**
         * Get XML for complex type.
         * @param array $data
         * @return string
         */
        public function getComplexType($data)
        {
                $document = new DOMDocument("1.0", "utf-8");

                foreach ($data as $name => $type) {
                        if (is_string($name)) {
                                $this->addParameter($document, $name, $type);
                        } else {
                                $this->addParameter($document, $type['name'], $type['type']);
                        }
                }
                return $document->saveXML();
        }

        /**
         * Append type to DOM node.
         * 
         * @param DomElement $node The DOM element node.
         * @param string $name The parameter name.
         * @param string $type The parameter type.
         */
        private function addParameter($node, $name, $type)
        {
                $generator = $this->_generator;

                if (!isset($generator->complexTypes[$type])) {
                        $child = $node->appendChild(new DOMElement($name, $type));
                } else {
                        $child = $node->appendChild(new DOMElement($name));
                        $cdata = $generator->complexTypes[$type];
                        foreach ($cdata as $d) {
                                $this->addParameter($child, $d['name'], $d['type']);
                        }
                }
        }

}
