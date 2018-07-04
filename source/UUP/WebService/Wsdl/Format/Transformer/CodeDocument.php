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

namespace UUP\WebService\Wsdl\Format\Transformer;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use UUP\WebService\Wsdl\Format\DocumentFormatter;
use UUP\WebService\Wsdl\Generator;

/**
 * Format as HTML.
 * 
 * Display request/response XML messages as HTML and formatted PHP code. Provides buttons 
 * for example SOAP message and response (with type documentation) and details.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class CodeDocument implements DocumentFormatter
{

        /**
         * Path to CSS slylesheet.
         * @var string 
         */
        private $_stylesheet;

        /**
         * Constructor.
         * @param string $stylesheet The custom stylesheet.
         */
        public function __construct($stylesheet = null)
        {
                if (!isset($stylesheet)) {
                        $this->_stylesheet = realpath(__DIR__ . "/../../../../../../admin/wsdl-viewer.css");
                } else {
                        $this->_stylesheet = $stylesheet;
                }

                if (!isset($this->_stylesheet)) {
                        throw new RuntimeException("Missing stylesheet for CSS");
                }
        }

        /**
         * Send document to stdout.
         * @param Generator $generator The WSDL generator.
         */
        function send($generator)
        {
                echo $this->getDocument($generator);
        }

        /**
         * Save document to file.
         * @param Generator $generator The WSDL generator.
         * @param string $filename The filename.
         */
        function save($generator, $filename)
        {
                file_put_contents($filename, $this->getDocument($generator));
        }

        /**
         * Get HTML document.
         * @param Generator $generator The WSDL generator.
         * @return string 
         */
        private function getDocument($generator)
        {
                $service = $generator->serviceName;
                $content = $this->getContent($generator);
                $styling = file_get_contents($this->_stylesheet);

                $result = sprintf("<html>"
                    . "<head>"
                    . "<title>SOAP service - %s</title>"
                    . "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\"/>"
                    . "</head>"
                    . "<body>"
                    . "<style>%s</style>"
                    . "<h1>%s SOAP Service</h1>"
                    . "%s"
                    . "</body>"
                    . "</html>", $service, $styling, $service, $content);
                return $result;
        }

        /**
         * Get unprocessed content.
         * 
         * Call this method to access unwrapped content. This could be useful for
         * accessing i.e. HTML content that is output inside document tags by the
         * send() and save() methods.
         * 
         * @param Generator $generator The WSDL generator.
         * @return string
         */
        public function getContent($generator)
        {
                $document = $generator->getDocument();
                $xcontent = $document->saveXML();

                if (!($xcontent = preg_replace("%</(xsd|wsdl|soap.*?):.*>%", "</div>", $xcontent))) {
                        throw new RuntimeException("Failed replace content");
                }
                if (!($xcontent = preg_replace("%<(xsd|wsdl|soap.*?):(.*?)( (.*))?>%", "<div class=\"$1 $1-$2\" $4>", $xcontent))) {
                        throw new RuntimeException("Failed replace content");
                }
                if (!($xcontent = preg_replace("%/>%", "></div>", $xcontent))) {
                        throw new RuntimeException("Failed replace content");
                }

                $doc = new DOMDocument();
                $doc->loadHTML($xcontent);

                $this->addMethods($generator, $doc);
                $this->addTypes($generator, $doc);

                $xcontent = $doc->saveHTML();
                return $xcontent;
        }

        private function addMethods($generator, $doc)
        {
                // 
                // Extract method names from HTML code:
                // 
                $xpath = new DOMXPath($doc);
                $found = $xpath->query("//*[contains(concat(' ', @class, ' '), ' wsdl-operation ')]");

                // 
                // Add header for methods:
                // 
                $first = $found->item(0);
                $first->parentNode->insertBefore(new DomElement("h2", "Methods"), $first);

                foreach ($found as $node) {

                        // 
                        // Insert method name header:
                        // 
                        $nname = $node->attributes->getNamedItem('name')->nodeValue;
                        $cnode = $node->insertBefore(new DOMElement("div"), $node->firstChild);
                        $cnode->appendChild(new DOMElement("h3", "$nname():"));
                        $cnode->setAttribute("class", "wsdl-method");

                        // 
                        // Extract method input/output data:
                        // 
                        $method = $generator->operations[$nname];

                        // 
                        // The SOAP message section:
                        // 
                        $message = $this->getMessage($generator, $nname, $method);

                        $child = $node->appendChild(new DOMElement("div"));
                        $ccode = $child->appendChild(new DOMElement("span", $message));
                        $ccode->setAttribute("class", "w3-code");
                        $ccode->setAttribute("style", "display:none");
                        $ccode->setAttribute("id", "message-$nname");

                        // 
                        // The SOAP response section:
                        // 
                        $message = $this->getResponse($generator, $nname, $method);

                        $child = $node->appendChild(new DOMElement("div"));
                        $ccode = $child->appendChild(new DOMElement("span", $message));
                        $ccode->setAttribute("class", "w3-code");
                        $ccode->setAttribute("style", "display:none");
                        $ccode->setAttribute("id", "response-$nname");

                        // 
                        // The details section (method params and return values):
                        // 

                        $child = $node->appendChild(new DOMElement("div"));
                        $ccode = $child->appendChild(new DOMElement("pre", var_export($method, true)));
                        $ccode->setAttribute("style", "display:none");
                        $ccode->setAttribute("id", "method-$nname");

                        // 
                        // Add button group:
                        // 
                        $cbutt = $cnode->appendChild(new DOMElement("a", "Message"));
                        $cbutt->setAttribute("class", "w3-btn w3-green w3-margin-right");
                        $cbutt->setAttribute("onclick", "document.getElementById('message-$nname').style.display = 'block'");

                        $cbutt = $cnode->appendChild(new DOMElement("a", "Response"));
                        $cbutt->setAttribute("class", "w3-btn w3-green w3-margin-right");
                        $cbutt->setAttribute("onclick", "document.getElementById('response-$nname').style.display = 'block'");

                        $cbutt = $cnode->appendChild(new DOMElement("a", "Details"));
                        $cbutt->setAttribute("class", "w3-btn w3-deep-orange w3-margin-right");
                        $cbutt->setAttribute("onclick", "document.getElementById('method-$nname').style.display = 'block'");
                }
        }

        private function addTypes($generator, $doc)
        {
                // 
                // Append complex types:
                // 
                $doc->appendChild(new DomElement("h2", "Types"));

                foreach ($generator->complexTypes as $name => $type) {
                        $ctype = $doc->appendChild(new DomElement("div"));
                        $ctype->appendChild(new DomElement("h3", $name));
                        $cbutt = $ctype->appendChild(new DOMElement("a", "Details"));
                        $cbutt->setAttribute("class", "w3-btn w3-green");
                        $cbutt->setAttribute("onclick", "document.getElementById('type-$name').style.display = 'block'");
                        $ccode = $ctype->appendChild(new DOMElement("pre", var_export($type, true)));
                        $ccode->setAttribute("style", "display:none");
                        $ccode->setAttribute("id", "type-$name");
                }
        }

        /**
         * Get SOAP method message.
         * 
         * @param Generator $generator The WSDL generator.
         * @param string $name The method name.
         * @param array $data The method description.
         * @return string
         */
        private function getMessage($generator, $name, $data)
        {
                $document = new DOMDocument("1.0", "utf-8");

                $envelop = $document->createElementNS("http://www.w3.org/2003/05/soap-envelope/", "soap:envelope");
                $envelop->setAttribute("soap:encodingStyle", "http://www.w3.org/2003/05/soap-encoding");
                $document->appendChild($envelop);

                $body = $document->createElement("soap:body");
                $body->setAttribute("xmlns", $generator->ns);
                $envelop->appendChild($body);

                $method = $body->appendChild(new DomElement($name));

                foreach ($data['input'] as $type) {
                        $this->addParameter($generator, $method, $type['name'], $type['type']);
                }
                return $document->saveXML();
        }

        /**
         * Get SOAP method response.
         * 
         * @param Generator $generator The WSDL generator.
         * @param string $name The method name.
         * @param array $data The method description.
         * @return string
         */
        private function getResponse($generator, $name, $data)
        {
                $document = new DOMDocument("1.0", "utf-8");

                $envelop = $document->createElementNS("http://www.w3.org/2003/05/soap-envelope/", "soap:envelope");
                $envelop->setAttribute("soap:encodingStyle", "http://www.w3.org/2003/05/soap-encoding");
                $document->appendChild($envelop);

                $body = $document->createElement("soap:body");
                $body->setAttribute("xmlns", $generator->ns);
                $envelop->appendChild($body);

                error_log("NAME: $name");

                if (count($data['output']) == 0) {
                        $method = $body->appendChild(new DomElement($name));
                        $return = $method->appendChild(new DomElement("return"));
                } else {
                        $return = $body->appendChild(new DomElement($name));
                }

                foreach ($data['output'] as $type) {
                        $this->addParameter($generator, $return, $type['name'], $type['type']);
                }
                return $document->saveXML();
        }

        /**
         * Append type to DOM node.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DomElement $node The DOM element node.
         * @param string $name The parameter name.
         * @param string $type The parameter type.
         */
        private function addParameter($generator, $node, $name, $type)
        {
                if (!isset($generator->complexTypes[$type])) {
                        $child = $node->appendChild(new DOMElement($name, $type));
                } else {
                        $child = $node->appendChild(new DOMElement($name));
                        $cdata = $generator->complexTypes[$type];
                        foreach ($cdata as $d) {
                                $this->addParameter($generator, $child, $d['name'], $d['type']);
                        }
                }
        }

}
