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

namespace UUP\WebService\Wsdl\Format\Generator;

use DOMDocument;
use DOMElement;
use DOMNode;
use ReflectionMethod;
use UUP\WebService\Soap\SoapMessage;
use UUP\WebService\Wsdl\Format\DocumentFormatter;
use UUP\WebService\Wsdl\Generator;
use UUP\WebService\Wsdl\Parser\Comment;

/**
 * Format as HTML.
 * 
 * This class works by looping thru the collected SOAP operations and complex types hold
 * by the WSDL generator. A DOM document is populated with information from the generator
 * and saved as HTML.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class HtmlDocument implements DocumentFormatter
{

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
        public function getDocument($generator)
        {
                $service = $generator->serviceName;
                $comment = $generator->getClassDocumentation();
                $content = $this->getContent($generator);

                $result = sprintf("<html>"
                    . "<head>"
                    . "<title>SOAP service - %s</title>"
                    . "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\"/>"
                    . "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.2.0/css/solid.css\" integrity=\"sha384-wnAC7ln+XN0UKdcPvJvtqIH3jOjs9pnKnq9qX68ImXvOGz2JuFoEiCjT8jyZQX2z\" crossorigin=\"anonymous\">"
                    . "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.2.0/css/fontawesome.css\" integrity=\"sha384-HbmWTHay9psM8qyzEKPc8odH4DsOuzdejtnr+OFtDmOcIVnhgReQ4GZBH7uwcjf6\" crossorigin=\"anonymous\">"
                    . "</head>"
                    . "<body>"
                    . "<style>"
                    . ".code-info-button { margin: 5px 0 5px 0; min-width: 110px; }"
                    . ".code-info-section { display: none }"
                    . "#comment { display: none }"
                    . ".method-icon { min-width: 15px }"
                    . ".method-description { display: none }"
                    . ".method-description-show:hover { cursor: pointer }"
                    . "</style>"
                    . "<div class=\"w3-container w3-right w3-padding w3-margin-right\">"
                    . "  <a href=\"?docs=syntax\" class=\"w3-btn w3-blue-grey code-info-button\">WSDL</a>"
                    . "  <a href=\"#\" onclick=\"toggle_display('comment')\" class=\"w3-btn w3-blue-grey code-info-button\">Comment</a>"
                    . "</div>"
                    . "<h1 class=\"w3-center\">%s SOAP Service</h1>"
                    . "<div class=\"w3-code\" id=\"comment\"><pre>%s</pre></div>"
                    . "%s"
                    . "<script>"
                    . "function toggle_display(id) { "
                    . "    var elem = document.getElementById(id);"
                    . "    if (elem.style.display == '') {"
                    . "        elem.style.display = 'block';"
                    . "    } else {"
                    . "        elem.style.display = '';"
                    . "    }"
                    . "}"
                    . "</script>"
                    . "</body>"
                    . "</html>", $service, $service, $comment, $content);

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
        public function getContent($generator): string
        {
                $doc = new DOMDocument();

                $node = $doc->appendChild(new DOMElement("h2", "Methods"));
                $node = $doc->appendChild(new DOMElement("div"));
                $this->addMethods($generator, $doc);

                $node = $doc->appendChild(new DOMElement("h2", "Types"));
                $node = $doc->appendChild(new DOMElement("div"));
                $this->addTypes($generator, $doc);

                return $doc->saveHTML();
        }

        /**
         * Add methods to DOM.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         */
        private function addMethods($generator, $node)
        {
                foreach ($generator->operations as $name => $type) {
                        $this->addMethod($generator, $node, $name, $type);
                }
        }

        /**
         * Add single method.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param string $name The method name.
         * @param array $type The input/output parameters.
         */
        private function addMethod($generator, $node, $name, $type)
        {
                $child = $node->appendChild(new DomElement("div"));
                $child->setAttribute("class", "w3-panel w3-padding w3-border w3-border-blue w3-round soap-method");

                $this->addMethodButtons($child, $name);
                $child->appendChild(new DomElement("h4", "Method:"));

                $anode = $child->appendChild(new DomElement("a"));
                $anode->setAttribute("name", "method-$name");

                $this->addMethodDeclaration($generator, $child, $name, $type);
        }

        /**
         * Add method declaration.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param string $name The method name.
         * @param array $type The input/output parameters.
         */
        private function addMethodDeclaration($generator, $node, $name, $type)
        {
                $cnode = $node->appendChild(new DOMElement("div"));
                $cnode->setAttribute("class", "method-declaration");

                $child = $cnode->appendChild(new DOMElement("span"));
                $child->setAttribute("class", "method-icon fas fa-circle w3-margin-right w3-text-pink");

                $this->addMethodReturn($generator, $cnode, $type['output']);
                $this->addMethodSignature($generator, $cnode, $name, $type['input']);
                $this->addMethodDescription($node, $name, $type);
                $this->addMethodSections($generator, $node, $name, $type);
        }

        /**
         * Add method return.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param array $data The return data.
         */
        private function addMethodReturn($generator, $node, $data)
        {
                // 
                // Fix for missing return value (void).
                // 
                if (count($data) != 0) {
                        $data = $data[0];
                }

                // 
                // The return type is either void, standard or complex:
                // 
                if (!isset($data['type'])) {
                        $child = $node->appendChild(new DomElement("span", "void"));
                        $child->setAttribute("class", "method-return");
                } elseif (isset($generator->complexTypes[$data['type']])) {
                        $child = $node->appendChild(new DomElement("a", $data['type']));
                        $child->setAttribute("href", "#type-" . $data['type']);
                        $child->setAttribute("class", "method-return");
                } else {
                        $child = $node->appendChild(new DomElement("span", $data['type']));
                        $child->setAttribute("class", "method-return");
                }

                // 
                // Add [] for array types:
                // 
                if (!isset($data['repeat'])) {
                        return;
                }
                if ($data['repeat'] == 'unbounded') {
                        $child->nodeValue .= "[]";
                }
        }

        /**
         * Add method signature.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param array $name The method name.
         * @param array $data The method input.
         */
        private function addMethodSignature($generator, $node, $name, $data)
        {
                $this->addMethodName($node, $name);
                $this->addMethodParams($generator, $node, $data);
        }

        /**
         * Add method name.
         * 
         * @param DOMNode $node The DOM node.
         * @param array $name The method name.
         */
        private function addMethodName($node, $name)
        {
                $child = $node->appendChild(new DomElement("span", $name));
                $child->setAttribute("style", "margin-left: 10px");
        }

        /**
         * Add method parameters.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param array $data The method input.
         */
        private function addMethodParams($generator, $node, $data)
        {
                // 
                // Fix for missing parameters (void).
                // 
                if (count($data) == 0) {
                        $data = array(array('name' => null, 'type' => 'void'));
                }

                // 
                // Add left side paranthes:
                // 
                $child = $node->appendChild(new DomElement("span", "("));
                $child->setAttribute("class", "method-params");

                // 
                // Add method parameters:
                // 
                foreach ($data as $index => $param) {
                        $this->addMethodParam($generator, $node, $param, $index);
                }

                // 
                // Add right side paranthes:
                // 
                $child = $node->appendChild(new DomElement("span", ")"));
                $child->setAttribute("class", "method-params");
        }

        /**
         * Add method parameter.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param array $data The parameter data.
         * @param int $index The parameter index.
         */
        private function addMethodParam($generator, $node, $data, $index)
        {
                // 
                // Add parameter separator:
                // 
                if ($index != 0) {
                        $child = $node->appendChild(new DomElement("span", ", "));
                }

                // 
                // The return type is either void, standard or complex:
                // 
                if ($data['type'] == "void") {
                        $child = $node->appendChild(new DomElement("span", "void"));
                        $child->setAttribute("class", "method-param-type");
                } elseif (isset($generator->complexTypes[$data['type']])) {
                        $child = $node->appendChild(new DomElement("a", $data['type']));
                        $child->setAttribute("href", "#type-" . $data['type']);
                        $child->setAttribute("class", "method-param-type");

                        $child = $node->appendChild(new DomElement("span", $data['name']));
                        $child->setAttribute("class", "method-param-name");
                        $child->setAttribute("style", "margin-left: 5px");
                } else {
                        $child = $node->appendChild(new DomElement("span", $data['type']));
                        $child->setAttribute("class", "method-param-type");

                        $child = $node->appendChild(new DomElement("span", $data['name']));
                        $child->setAttribute("class", "method-param-name");
                        $child->setAttribute("style", "margin-left: 5px");
                }

                // 
                // Add [] for array types:
                // 
                if (!isset($data['repeat'])) {
                        return;
                }
                if ($data['repeat'] == 'unbounded') {
                        $child->nodeValue .= "[]";
                }
        }

        /**
         * Add method description.
         * 
         * @param DOMNode $node The DOM node.
         * @param string $name The method name.
         * @param array $data The input/output parameters.
         */
        private function addMethodDescription($node, $name, $data)
        {
                if (isset($data['documentation'])) {
                        $this->addMethodDocumentation($node, $name, $data['documentation']);
                }
                if (count($data['input'])) {
                        $this->addMethodParamsInfo($node, $data['input']);
                }
                if (count($data['output']) > 0 && !empty($data['output'][0]['docs'])) {
                        $this->addMethodReturnInfo($node, $data['output'][0]['docs']);
                }
        }

        /**
         * Add method info sections.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DOMNode $node The DOM node.
         * @param string $name The method name.
         * @param array $type The input/output parameters.
         */
        private function addMethodSections($generator, $node, $name, $type)
        {
                $soap = new SoapMessage($generator);

                $child = $node->appendChild(new DOMElement("div"));
                $child->setAttribute("class", "code-info-sections");

                $message = $soap->getMessage($name, $type['input']);
                $this->addMethodMessageSection($child, $name, $message);

                $message = $soap->getResponse($name, $type['output']);
                $this->addMethodResponseSection($child, $name, $message);

                $source = ReflectionMethod::export($generator->className, $name, true);
                $this->addMethodSourceSection($child, $name, $source);

                $this->addMethodDetailsSection($child, $name, $type);
        }

        /**
         * Add method documentation.
         * 
         * @param DOMNode $node The DOM node.
         * @param string $name The method name.
         * @param string $message The method description message.
         */
        private function addMethodDocumentation($node, $name, $message)
        {
                $block = new Comment($message);
                $idtag = sprintf("method-description-%s", $name);

                $cnode = $node->appendChild(new \DOMElement("div"));
                $cnode->setAttribute("class", "method-documentation");

                $this->addMethodDocumentationSummary($cnode, $idtag, $block);
                $this->addMethodDocumentationDescription($cnode, $idtag, $block);
        }

        /**
         * Add summary to method documentation.
         * 
         * @param DOMNode $node The DOM node.
         * @param string $idtag The description section ID.
         * @param Comment $block The docblock comment object.
         */
        private function addMethodDocumentationSummary($node, $idtag, $block)
        {
                if ($block->hasDescription()) {
                        $child = $node->appendChild(new DOMElement("span"));
                        $child->setAttribute("class", "method-icon fas fa-chevron-circle-down w3-margin-right w3-text-deep-orange method-description-show");
                        $child->setAttribute("onclick", sprintf("toggle_display('%s')", $idtag));
                } else {
                        $child = $node->appendChild(new DOMElement("span"));
                        $child->setAttribute("class", "method-icon fas fa-circle w3-margin-right w3-text-deep-orange");
                }

                $child = $node->appendChild(new DOMElement("span", $block->getSummary()));
                $child->setAttribute("class", "method-summary");
        }

        /**
         * Add description to method documentation.
         * 
         * @param DOMNode $node The DOM node.
         * @param string $idtag The description section ID.
         * @param Comment $block The docblock comment object.
         */
        private function addMethodDocumentationDescription($node, $idtag, $block)
        {
                if ($block->hasDescription()) {
                        $child = $node->appendChild(new DOMElement("div", $block->getDescription(" ", true)));
                        $child->setAttribute("class", "method-description w3-code w3-border-deep-orange");
                        $child->setAttribute("id", $idtag);
                }
        }

        /**
         * Add method parameter info.
         * 
         * @param DOMNode $node The DOM node.
         * @param array $params The method parameters.
         */
        private function addMethodParamsInfo($node, $params)
        {
                $node->appendChild(new DOMElement("h4", "Parameters:"));

                $child = $node->appendChild(new DOMElement("div"));
                $child->setAttribute("class", "method-params");

                $plist = $child->appendChild(new DOMElement("dl"));
                foreach ($params as $param) {
                        $pnode = $plist->appendChild(new DOMElement("dt"));

                        $cnode = $pnode->appendChild(new DOMElement("span"));
                        $cnode->setAttribute("class", "method-icon fas fa-circle w3-margin-right w3-text-indigo");
                        $cnode = $pnode->appendChild(new DOMElement("span", $param['name']));

                        $pnode = $plist->appendChild(new DOMElement("dd", $param['docs']));
                }
        }

        /**
         * Add method return info.
         * 
         * @param DOMNode $node The DOM node.
         * @param string $message The method return message.
         */
        private function addMethodReturnInfo($node, $message)
        {
                $node->appendChild(new DOMElement("h4", "Return:"));
                $node->appendChild(new DOMElement("p", $message));
        }

        /**
         * Add buttons for method section.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The method name.
         */
        private function addMethodButtons($node, $name)
        {
                $node = $node->appendChild(new DomElement("div"));
                $node->setAttribute("class", "w3-right");

                $cbutt = $node->appendChild(new DOMElement("a", "Message"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-green code-info-button");
                $cbutt->setAttribute("onclick", "toggle_display('message-$name')");

                $cbutt = $node->appendChild(new DOMElement("a", "Response"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-green code-info-button");
                $cbutt->setAttribute("onclick", "toggle_display('response-$name')");

                $cbutt = $node->appendChild(new DOMElement("a", "Source"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-deep-purple code-info-button");
                $cbutt->setAttribute("onclick", "toggle_display('source-$name')");

                $cbutt = $node->appendChild(new DOMElement("a", "Details"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-deep-orange code-info-button");
                $cbutt->setAttribute("onclick", "toggle_display('method-$name')");
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
                $ccode = $child->appendChild(new DOMElement("pre", $source));
                $ccode->setAttribute("class", "w3-code code-info-section w3-border-deep-purple");
                $ccode->setAttribute("id", "source-$name");
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
                $ccode = $child->appendChild(new DOMElement("pre", var_export($method, true)));
                $ccode->setAttribute("class", "w3-code code-info-section w3-border-deep-orange");
                $ccode->setAttribute("id", "method-$name");
        }

        /**
         * Add complex types to DOM.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DomNode $node The DOM node.
         */
        private function addTypes($generator, $node)
        {
                foreach ($generator->complexTypes as $name => $type) {
                        if ($name != 'mixed') {
                                $this->addType($generator, $node, $name, $type);
                        }
                }
        }

        /**
         * Add complex type.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DomNode $node The DOM node.
         * @param string $name The complex type name.
         * @param array $type The complex type data.
         */
        private function addType($generator, $node, $name, $type)
        {
                $child = $node->appendChild(new DomElement("div"));
                $child->setAttribute("class", "w3-panel w3-padding w3-border w3-border-blue w3-round soap-type");

                $this->addTypeButtons($child, $name);
                $child->appendChild(new DomElement("h4", $name));

                $this->addTypeProperties($child, $type);

                $anchor = $child->appendChild(new DomElement("a"));
                $anchor->setAttribute("name", "type-$name");

                $this->addTypeSections($generator, $child, $name, $type);
        }

        /**
         * Add complex type properties.
         * @param DomNode $node The DOM node.
         * @param array $type The complex type data.
         */
        private function addTypeProperties($node, $type)
        {
                $child = $node->appendChild(new DOMElement("div"));
                $child->setAttribute("class", "type-properties");

                $plist = $child->appendChild(new DOMElement("dl"));

                foreach ($type as $prop) {
                        $this->addTypeProperty($plist, $prop);
                }
        }

        /**
         * Add complex type property.
         * @param DomNode $node The DOM node.
         * @param array $prop The complex type property data.
         */
        private function addTypeProperty($node, $prop)
        {
                $pnode = $node->appendChild(new DOMElement("dt"));

                $cnode = $pnode->appendChild(new DOMElement("span"));
                $cnode->setAttribute("class", "method-icon fas fa-circle w3-margin-right w3-text-indigo");
                $cnode = $pnode->appendChild(new DOMElement("span", sprintf("%s %s", $prop['type'], $prop['name'])));

                $pnode = $node->appendChild(new DOMElement("dd", $prop['docs']));
        }

        /**
         * Add complex type buttons.
         * 
         * @param DomNode $node The DOM node.
         * @param string $name The type name.
         */
        private function addTypeButtons($node, $name)
        {
                $node = $node->appendChild(new DomElement("div"));
                $node->setAttribute("class", "w3-right");

                $cbutt = $node->appendChild(new DOMElement("a", "Serialized"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-green code-info-button");
                $cbutt->setAttribute("onclick", "toggle_display('type-serialized-$name')");

                $cbutt = $node->appendChild(new DOMElement("a", "Details"));
                $cbutt->setAttribute("class", "w3-btn w3-margin-right w3-deep-orange code-info-button");
                $cbutt->setAttribute("onclick", "toggle_display('type-details-$name')");
        }

        /**
         * Add complex type info sections.
         * 
         * @param Generator $generator The WSDL generator.
         * @param DomNode $node The DOM node.
         * @param string $name The complex type name.
         * @param array $type The complex type data.
         */
        private function addTypeSections($generator, $node, $name, $type)
        {
                $soap = new SoapMessage($generator);
                $styp = $soap->getComplexType($type, strtolower($name));

                $this->addTypeSerializedSection($node, $name, $styp);
                $this->addTypeDetailsSection($node, $name, $type);
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
                $ccode->setAttribute("class", "w3-code code-info-section w3-border-deep-orange");
                $ccode->setAttribute("id", "type-details-$name");
        }

}
