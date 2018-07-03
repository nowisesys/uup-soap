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

use UUP\WebService\Wsdl\Format\DocumentFormatter;
use UUP\WebService\Wsdl\Generator;

/**
 * Format as HTML body content.
 *
 * @author Anders Lövgren (Nowise Systems)
 */
class HtmlContent implements DocumentFormatter
{

        /**
         * Send document to stdout.
         * @param Generator $generator The WSDL generator.
         */
        function send($generator)
        {
                
        }

        /**
         * Save document to file.
         * @param Generator $generator The WSDL generator.
         * @param string $filename The filename.
         */
        function save($generator, $filename)
        {
                
        }

}
