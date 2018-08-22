<?php

/*
 * Copyright (C) 2014-2018 Anders Lövgren (BMC-IT, Uppsala University)
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

// 
// File:    Generator.php
// Created: 2014-10-15 14:40:48
// 
// Author:  Anders Lövgren (BMC-IT, Uppsala University)
// 

namespace UUP\WebService\Wsdl;

use Extern\WsdlGenerator;

/**
 * SOAP service description (WSDL) generator.
 *
 * @author Anders Lövgren (BMC-IT, Uppsala University)
 */
class Generator extends WsdlGenerator
{

        /**
         * Get XML document object.
         * 
         * Returns the DOM tree document describing the SOAP service.
         * @return \DomDocument
         */
        public function getDocument()
        {
                $xmldoc = new \DomDocument("1.0", "utf-8");
                $root = $xmldoc->createElement('wsdl:definitions');
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:tns', $this->ns);
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soap-env', self::SCHEMA_SOAP);
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsdl', self::SCHEMA_WSDL);
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soapenc', self::SOAP_SCHEMA_ENCODING);
                $root->setAttribute('targetNamespace', $this->ns);
                $this->addDocumentation($xmldoc, $root);
                $this->addTypes($xmldoc, $root);
                $this->addMessages($xmldoc, $root);
                $this->addPortType($xmldoc, $root);
                $this->addBinding($xmldoc, $root);
                $this->addService($xmldoc, $root);

                $xmldoc->formatOutput = true;
                $xmldoc->appendChild($root);

                return $xmldoc;
        }

}
