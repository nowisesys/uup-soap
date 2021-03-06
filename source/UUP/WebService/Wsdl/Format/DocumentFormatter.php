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

namespace UUP\WebService\Wsdl\Format;

/**
 * Interface for WSDL formating.
 * 
 * Classes should implement this interface when providing output formats (i.e. WSDL,
 * XML or HTML) from the supplied description generator.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
interface DocumentFormatter
{

        /**
         * Send document to stdout.
         * @param Generator $generator The WSDL generator.
         */
        function send($generator);

        /**
         * Save document to file.
         * @param Generator $generator The WSDL generator.
         * @param string $filename The filename.
         */
        function save($generator, $filename);

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
        function getContent($generator);

        /**
         * Get complete document.
         * 
         * The method depends on the format implementation. For some formatters, this
         * is equivalent to calling getContent().
         * 
         * @param Generator $generator The WSDL generator.
         * @return string
         */
        function getDocument($generator);
}
