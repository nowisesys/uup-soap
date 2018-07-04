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
use RuntimeException;
use UUP\WebService\Wsdl\Format\DocumentFormatter;
use UUP\WebService\Wsdl\Generator;
use XSLTProcessor;

/**
 * Format as HTML using XSLT processing and stylesheet.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class XsltStylesheet implements DocumentFormatter
{

        /**
         * Path to XSLT slylesheet.
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
                        $this->_stylesheet = realpath(__DIR__ . "/../../../../../../admin/wsdl-viewer.xsl");
                } else {
                        $this->_stylesheet = $stylesheet;
                }

                if (!isset($this->_stylesheet)) {
                        throw new RuntimeException("Missing stylesheet for XSLT processing");
                }
        }

        /**
         * Send document to stdout.
         * @param Generator $generator The WSDL generator.
         */
        function send($generator)
        {
                echo $this->getContent($generator);
        }

        /**
         * Save document to file.
         * @param Generator $generator The WSDL generator.
         * @param string $filename The filename.
         */
        function save($generator, $filename)
        {
                file_put_contents($filename, $this->getContent($generator));
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
                $document = $generator->getDocument();

                $xsl = new DOMDocument();
                $xsl->load($this->_stylesheet);

                $xml = new DOMDocument();
                $xml->loadXML($document->saveXML());

                $proc = new XSLTProcessor();
                $proc->importStylesheet($xsl);

                return $proc->transformToXml($xml);
        }

}
