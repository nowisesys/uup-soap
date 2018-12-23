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

/**
 * WSDL document (HTML) with syntax highlight.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class SyntaxDocument implements \UUP\WebService\Wsdl\Format\DocumentFormatter
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
                $xcontent = $document->saveXML();

                return $xcontent;
        }

        /**
         * Get HTML document.
         * @param Generator $generator The WSDL generator.
         * @return string 
         */
        public function getDocument($generator): string
        {
                $service = $generator->serviceName;
                $content = $this->getContent($generator);

                $result = sprintf("<html>"
                    . "<head>"
                    . "<title>SOAP service - %s</title>"
                    . "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\"/>"
                    . "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/styles/solarized-dark.min.css\">"
                    . "</head>"
                    . "<body>"
                    . "<style>"
                    . "body { background-color: white }"
                    . "</style>"
                    . "<h1 class=\"w3-center\">%s SOAP Service</h1>"
                    . "<div><pre><code class=\"xml\">%s</code></pre></div>"
                    . "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js\"></script>"
                    . "<script>hljs.initHighlightingOnLoad()</script>"
                    . "</body>"
                    . "</html>"
                    . "", $service, $service, htmlentities($content));
                return $result;
        }

}
