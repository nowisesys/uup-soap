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
use ReflectionMethod;
use RuntimeException;
use UUP\WebService\Soap\SoapMessage;
use UUP\WebService\Wsdl\Format\DocumentFormatter;
use UUP\WebService\Wsdl\Generator;

/**
 * Format as HTML.
 * 
 * Display request/response XML messages as HTML and formatted PHP code. Provides buttons for 
 * example SOAP message and response (with type documentation) and details. Uses reflection
 * on SOAP class for extracting method signatures.
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

        /**
         * Add methods to DOM.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMDocument $doc The DOM document.
         */
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

                // 
                // Add methods to node:
                // 
                foreach ($found as $node) {
                        $this->addMethod($generator, $node);
                }
        }

        /**
         * Add single method.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DomNode $node The DOM node.
         */
        private function addMethod($generator, $node)
        {
                // 
                // Place content in container (add space on left/right):
                // 
                $node->setAttribute("class", "wsdl-method w3-container");

                // 
                // Hide SOAP method bindings:
                // 
                if (strstr($node->parentNode->getAttribute("class"), "wsdl-binding")) {
                        $node->setAttribute("style", "display:none");
                        return;
                }

                // 
                // Create SOAP message generator:
                // 
                $soap = new SoapMessage($generator);

                // 
                // Insert method name header:
                // 
                $nname = $node->attributes->getNamedItem('name')->nodeValue;
                $this->addMethodHeader($node, $nname);

                // 
                // Add button group:
                // 
                $this->addMethodButtons($node, $nname);

                // 
                // Extract method input/output data:
                // 
                $method = $generator->operations[$nname];

                // 
                // The SOAP message section:
                // 
                $message = $soap->getMessage($nname, $method['input']);
                $this->addMethodMessageSection($node, $nname, $message);

                // 
                // The SOAP response section:
                // 
                $message = $soap->getResponse($nname, $method['output']);
                $this->addMethodResponseSection($node, $nname, $message);

                // 
                // The source code section:
                // 
                $source = ReflectionMethod::export($generator->className, $nname, true);
                $this->addMethodSourceSection($node, $nname, $source);

                // 
                // The details section (method params and return values):
                // 
                $this->addMethodDetailsSection($node, $nname, $method);
        }

        /**
         * Add header for SOAP method.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         */
        private function addMethodHeader($node, $name)
        {
                $cnode = $node->insertBefore(new DOMElement("div"), $node->firstChild);
                $cnode->appendChild(new DOMElement("h3", "$name():"));
                $cnode->setAttribute("class", "w3-margin-bottom");
        }

        /**
         * Add message section for SOAP method.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         * @param string $message The method message (XML).
         */
        private function addMethodMessageSection($node, $name, $message)
        {
                $child = $node->appendChild(new DOMElement("div"));
                $child->appendChild(new DOMElement("span", $message));
                $child->setAttribute("class", "w3-code code-info-section");
                $child->setAttribute("id", "message-$name");
        }

        /**
         * Add response section for SOAP method.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         * @param string $response The method response (XML).
         */
        private function addMethodResponseSection($node, $name, $response)
        {
                $child = $node->appendChild(new DOMElement("div"));
                $child->appendChild(new DOMElement("span", $response));
                $child->setAttribute("class", "w3-code code-info-section");
                $child->setAttribute("id", "response-$name");
        }

        /**
         * Add source section for SOAP method.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         * @param string $source The method source (from reflection).
         */
        private function addMethodSourceSection($node, $name, $source)
        {
                $child = $node->appendChild(new DOMElement("div"));
                $child->appendChild(new DOMElement("pre", $source));
                $child->setAttribute("class", "w3-code code-info-section");
                $child->setAttribute("id", "source-$name");
        }

        /**
         * Add source section for SOAP method.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         * @param array $method The method data (input/output).
         */
        private function addMethodDetailsSection($node, $name, $method)
        {
                $child = $node->appendChild(new DOMElement("div"));
                $child->appendChild(new DOMElement("pre", var_export($method, true)));
                $child->setAttribute("class", "w3-code code-info-section");
                $child->setAttribute("id", "method-$name");
        }

        /**
         * Add buttons for method section.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         */
        private function addMethodButtons($node, $name)
        {
                $cbutt = $node->appendChild(new DOMElement("a", "Message"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-green code-info-button");
                $cbutt->setAttribute("onclick", "document.getElementById('message-$name').style.display = 'block'");

                $cbutt = $node->appendChild(new DOMElement("a", "Response"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-green code-info-button");
                $cbutt->setAttribute("onclick", "document.getElementById('response-$name').style.display = 'block'");

                $cbutt = $node->appendChild(new DOMElement("a", "Source"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-deep-purple code-info-button");
                $cbutt->setAttribute("onclick", "document.getElementById('source-$name').style.display = 'block'");

                $cbutt = $node->appendChild(new DOMElement("a", "Details"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-deep-orange code-info-button");
                $cbutt->setAttribute("onclick", "document.getElementById('method-$name').style.display = 'block'");
        }

        /**
         * Add complex types to DOM.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMDocument $doc The DOM document.
         */
        private function addTypes($generator, $doc)
        {
                // 
                // Append complex types:
                // 
                $node = $doc->appendChild(new DomElement("div"));
                $node->appendChild(new DomElement("h2", "Types"));
                $node->setAttribute("class", "wsdl wsdl-types");

                // 
                // Add complex type sections:
                // 
                foreach ($generator->complexTypes as $name => $type) {
                        $this->addType($generator, $node, $name, $type);
                }
        }

        /**
         * Add complex type.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DomNode $node The DOM node.
         * @param string $name The type name.
         * @param array $type The type data.
         */
        private function addType($generator, $node, $name, $type)
        {
                // 
                // The mixed complex type is for internal use:
                // 
                if ($name == 'mixed') {
                        return;
                }

                // 
                // Create SOAP message generator:
                // 
                $soap = new SoapMessage($generator);

                // 
                // Insert complex type header:
                // 
                $this->addTypeHeader($node, $name);

                // 
                // Add button group:
                // 
                $this->addTypeButtons($node, $name);

                // 
                // Add serialized type section:
                // 
                $stype = $soap->getComplexType($type);
                $this->addTypeSerializedSection($node, $name, $stype);

                // 
                // Add details section:
                // 
                $this->addTypeDetailsSection($node, $name, $type);
        }

        /**
         * Add complex type header.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The type name.
         */
        private function addTypeHeader(&$node, $name)
        {
                $node = $node->appendChild(new DomElement("div"));
                $node->appendChild(new DomElement("h3", $name));
                $node->setAttribute("class", "soap-type w3-container");
        }

        /**
         * Add complex type buttons.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The type name.
         */
        private function addTypeButtons($node, $name)
        {
                $cbutt = $node->appendChild(new DOMElement("a", "Serialized"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-green code-info-button");
                $cbutt->setAttribute("onclick", "document.getElementById('type-serialized-$name').style.display = 'block'");

                $cbutt = $node->appendChild(new DOMElement("a", "Details"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-deep-orange code-info-button");
                $cbutt->setAttribute("onclick", "document.getElementById('type-details-$name').style.display = 'block'");
        }

        /**
         * Add section for serialized complex type.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The type name.
         * @param string $type The XML string.
         */
        private function addTypeSerializedSection($node, $name, $type)
        {
                $ccode = $node->appendChild(new DOMElement("span", $type));
                $ccode->setAttribute("class", "w3-code code-info-section");
                $ccode->setAttribute("id", "type-serialized-$name");
        }

        /**
         * Add complex type details section.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The type name.
         * @param array $type The type data.
         */
        private function addTypeDetailsSection($node, $name, $type)
        {
                $ccode = $node->appendChild(new DOMElement("pre", var_export($type, true)));
                $ccode->setAttribute("class", "w3-code code-info-section");
                $ccode->setAttribute("id", "type-details-$name");
        }

}
