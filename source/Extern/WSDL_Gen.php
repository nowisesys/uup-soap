<?php

/**
 * WSDL_Gen: A WSDL Generator for PHP5
 *
 * This class generates WSDL from a PHP5 class.
 * 
 * Downloaded from:
 * 
 * 1. http://web.archive.org/web/* 
 * 2. http://www.schlossnagle.org/~george/php/WSDL_Gen.tgz
 * 3. http://code.google.com/p/php-wsdl-generator/downloads
 * 
 * -----------------------
 * Some modifications done:
 * 
 *   o) Added support for namespace in complex types.
 *   o) Added support for xsd:integer (big int)
 *   o) Use short class name as service port. 
 *   o) Include argument and return type in methods wsdl:documentation.
 *   o) Added support for arrays as parameter and return type.
 *   o) Skip constructor, non-public/abstract methods and static functions.
 *   o) Add support for class paths (namespace for unqualified classes).
 *   o) Create map between XSD complex type and PHP classes.
 *   o) Keep properties private by default.
 * 
 * Anders Lövgren, 2014-10-14
 * 
 * @property-read array $operations The discovered SOAP methods.
 * @property-read string $className The SOAP service handler class.
 * @property-read string $serviceName The SOAP service name (short class name).
 * @property-read string $ns The SOAP service XML namespace.
 * @property-read string $endpoint The SOAP service endpoint (URL).
 * @property-read array $complexTypes Discovered complex types.
 */
class WSDL_Gen
{

        const SOAP_XML_SCHEMA_VERSION = 'http://www.w3.org/2001/XMLSchema';
        const SOAP_XML_SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';
        const SOAP_SCHEMA_ENCODING = 'http://schemas.xmlsoap.org/soap/encoding/';
        const SOAP_ENVELOP = 'http://schemas.xmlsoap.org/soap/envelope/';
        const SCHEMA_SOAP_HTTP = 'http://schemas.xmlsoap.org/soap/http';
        const SCHEMA_SOAP = 'http://schemas.xmlsoap.org/wsdl/soap/';
        const SCHEMA_WSDL = 'http://schemas.xmlsoap.org/wsdl/';

        private static $_baseTypes = array(
                'int'          => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'int'
                ),
                'integer'      => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'int'         // not correct 
                ),
                'float'        => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'float'
                ),
                'double'       => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'double'
                ),
                'string'       => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'string'
                ),
                'boolean'      => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'boolean'
                ),
                'bool'         => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'boolean'
                ),
                'unknown_type' => array(
                        'ns'   => self::SOAP_XML_SCHEMA_VERSION,
                        'name' => 'any'
                )
        );
        private $_types;
        private $_operations = array();
        private $_className;
        private $_serviceName;
        private $_ns;
        private $_endpoint;
        private $_complexTypes;
        private $_mytypes = array();
        private $_style = SOAP_RPC;
        private $_use = SOAP_ENCODED;
        private $_classPath = array();
        private $_classMap = array();

        /** The WSDL_Gen constructor
         * @param string $className The SOAP service handler class.
         * @param string $endpoint  The endpoint for the service.
         * @param string $ns optional The namespace you want for your service.
         */
        function __construct($className, $endpoint, $ns = false)
        {
                $this->_types = self::$_baseTypes;
                $this->_className = $className;
                if (!$ns) {
                        $ns = $endpoint;
                }
                $this->_ns = $ns;
                $this->_endpoint = $endpoint;
                $this->createPHPTypes();
        }

        public function __get($name)
        {
                switch ($name) {
                        case 'operations':
                                return $this->_operations;
                        case 'className':
                                return $this->_className;
                        case 'serviceName':
                                return $this->_serviceName;
                        case 'ns':
                                return $this->_ns;
                        case 'endpoint':
                                return $this->_endpoint;
                        case 'complexTypes':
                                return $this->_complexTypes;
                }
        }

        /**
         * Add namespace for unqualified classes.
         * @param string $path The namespaces.
         */
        public function addClassPath($path)
        {
                $this->_classPath[] = $path;
        }

        /**
         * Set array of namespaces for unqualified classes.
         * @param array $pathes The array of namespaces.
         */
        public function setClassPath($pathes)
        {
                $this->_classPath = $pathes;
        }

        /**
         * Set class map.
         * 
         * The class map is used to map between XSD complex types and PHP. For example, 
         * if an class is aliased, then use the class map for resolving the alias to 
         * real class.
         * 
         * <code>
         * $generator->setClassMap(array(
         *      'EmployeeList' => 'Employees'
         * ));
         * </code>
         * 
         * @param array $map
         */
        public function setClassMap($map)
        {
                $this->_classMap = $map;
        }

        /**
         * Get map between XSD complex types and PHP classes.
         * @return array
         */
        public function getClassMap()
        {
                return $this->_classMap;
        }

        /**
         * Start service discover (reflection).
         */
        public function discover()
        {
                $class = new ReflectionClass($this->_className);
                $this->_serviceName = $class->getShortName();
                $methods = $class->getMethods();
                $this->discoverOperations($methods);
                $this->discoverTypes();
        }

        protected function discoverOperations($methods)
        {
                foreach ($methods as $method) {
                        if ($method->isConstructor()) {
                                continue;       // Skip constructor
                        }
                        if ($method->isDestructor()) {
                                continue;       // Skip destructor                                
                        }
                        if ($method->isPublic() == false) {
                                continue;       // Skip non-public
                        }
                        if ($method->isStatic()) {
                                continue;       // Skip static functions
                        }
                        if ($method->isAbstract()) {
                                continue;       // Skip abstract members
                        }

                        $this->_operations[$method->getName()]['input'] = array();
                        $this->_operations[$method->getName()]['output'] = array();
                        $doc = $method->getDocComment();

                        // 
                        // Extract input params:
                        // 
                        $matches = array();
                        if (preg_match_all('|@param\s+(?:object\s+)?(.*?)?(\[\])*\s+\$(\w+)\s*([\w\. ]*)|', $doc, $matches, PREG_SET_ORDER)) {
                                foreach ($matches as $match) {
                                        $this->_mytypes[$match[1]] = 1;
                                        $this->_operations[$method->getName()]['input'][] = array('name' => $match[3], 'type' => $match[1], 'repeat' => $match[2] == '[]' ? 'unbounded' : '1', 'docs' => $match[4]);
                                }
                        }

                        // 
                        // Extract return types:
                        // 
                        if (preg_match('|@return\s+([^\s(\[\])]+)?(\[\])*\s*(.*)\n|', $doc, $match)) {
                                $this->_mytypes[$match[1]] = 1;
                                $this->_operations[$method->getName()]['output'][] = array('name' => 'return', 'type' => $match[1], 'repeat' => $match[2] == '[]' ? 'unbounded' : '1', 'docs' => $match[3]);
                        }

                        // 
                        // Extract documentation:
                        // 
                        $comment = trim($doc);
                        $commentStart = strpos($comment, '/**') + 3;
                        $comment = trim(substr($comment, $commentStart, strlen($comment) - 5));
                        $description = '';
                        $lines = preg_split("(\\n\\r|\\r\\n\\|\\r|\\n)", $comment);
                        foreach ($lines as $line) {
                                $line = trim($line);
                                $lineStart = strpos($line, '*');
                                if ($lineStart === false) {
                                        $lineStart = -1;
                                }
                                $line = trim(substr($line, $lineStart + 1));
                                if (!isset($line[0]) || $line[0] != "@") {
                                        if (strlen($line) > 0) {
                                                $description .= trim($line);
                                        }
                                }
                        }
                        $this->_operations[$method->getName()]['documentation'] = $description;
                }
        }

        protected function discoverTypes()
        {
                foreach (array_keys($this->_mytypes) as $type) {
                        if (!isset($this->_types[$type])) {
                                $this->addComplexType($type);
                        }
                }
        }

        protected function createPHPTypes()
        {
                $this->_complexTypes['mixed'] = array(
                        array('name' => 'varString',
                                'type' => 'string'),
                        array('name' => 'varInt',
                                'type' => 'int'),
                        array('name' => 'varFloat',
                                'type' => 'float'),
                        array('name' => 'varArray',
                                'type' => 'array'),
                        array('name' => 'varBoolean',
                                'type' => 'boolean')
                );
                $this->_types['mixed'] = array('name' => 'mixed', 'ns' => $this->_ns);
                $this->_types['array'] = array('name' => 'array', 'ns' => $this->_ns);
        }

        private function getReflectionClass($className)
        {
                // 
                // Get aliased class:
                // 
                if (isset($this->_classMap[$className])) {
                        $className = $this->_classMap[$className];
                }

                // 
                // Create reflection on fully qualified class:
                // 
                if (strpos($className, '\\')) {
                        return new ReflectionClass($className);
                }

                // 
                // Use class pathes in reflection:
                // 
                foreach ($this->_classPath as $path) {
                        try {
                                $class = new ReflectionClass($path . '\\' . $className);
                                return $class;
                        } catch (ReflectionException $exception) {
                                // ignore
                        }
                }

                // 
                // Reflection has failed:
                // 
                if ($className == 'type') {
                        throw new ReflectionException("Got class named 'type', please check @params or @return in method documentation.");
                } else {
                        throw new ReflectionException("Failed reflection on class $className (maybe missing in class map or path).");
                }
        }

        protected function addComplexType($className)
        {
                $class = $this->getReflectionClass($className);

                $this->_complexTypes[$className] = array();
                if (($str = strrchr($className, '\\'))) {
                        $typeName = trim($str, '\\');
                } else {
                        $typeName = $className;
                }

                $this->_classMap[$typeName] = $class->getName();
                $this->_types[$className] = array('name' => $typeName, 'ns' => $this->_ns);

                foreach ($class->getProperties() as $prop) {
                        $doc = $prop->getDocComment();
                        if (preg_match('|@var\s+(?:object\s+)?(\w+)|', $doc, $match)) {
                                $type = $match[1];
                                $this->_complexTypes[$className][] = array('name' => $prop->getName(), 'type' => $type);
                                if (!isset($this->_types[$type])) {
                                        $this->addComplexType($type);
                                }
                        }
                }
        }

        protected function addMessages(DomDocument $doc, DomElement $root)
        {
                foreach (array('input' => '', 'output' => 'Response') as $type => $postfix) {
                        foreach ($this->_operations as $name => $params) {
                                $el = $doc->createElementNS(self::SCHEMA_WSDL, 'message');
                                $fullName = "$name" . ucfirst($postfix);
                                $el->setAttribute("name", $fullName);
                                $part = $doc->createElementNS(self::SCHEMA_WSDL, 'part');
                                $part->setAttribute('element', 'tns:' . $fullName);
                                $part->setAttribute('name', 'parameters');
                                $el->appendChild($part);
                                $root->appendChild($el);
                        }
                }
        }

        protected function addPortType(DomDocument $doc, DomElement $root)
        {
                $el = $doc->createElementNS(self::SCHEMA_WSDL, 'portType');
                $el->setAttribute('name', $this->_serviceName . "PortType");
                foreach ($this->_operations as $name => $params) {
                        $op = $doc->createElementNS(self::SCHEMA_WSDL, 'operation');
                        $op->setAttribute('name', $name);
                        $opDocu = $doc->createElementNS(self::SCHEMA_WSDL, 'documentation');
                        $documentation = "\n\t" . $params['documentation'];
                        foreach ($params['input'] as $method) {
                                $documentation .= "\n\t@param " . $this->_types[$method['type']]['name'] . " " . $method['docs'] . " [name=" . $method['name'] . "]";
                        }
                        foreach ($params['output'] as $method) {
                                $documentation .= "\n\t@return " . $this->_types[$method['type']]['name'] . " " . $method['docs'];
                        }
                        $docuText = $doc->createTextNode($documentation);
                        $opDocu->appendChild($docuText);
                        $op->appendChild($opDocu);
                        foreach (array('input' => '', 'output' => 'Response') as $type => $postfix) {
                                $sel = $doc->createElementNS(self::SCHEMA_WSDL, $type);
                                $fullName = "$name" . ucfirst($postfix);
                                $sel->setAttribute('message', 'tns:' . $fullName);
                                $sel->setAttribute('name', $fullName);
                                $op->appendChild($sel);
                        }
                        $el->appendChild($op);
                }
                $root->appendChild($el);
        }

        protected function addBinding(DomDocument $doc, DomElement $root)
        {
                $el = $doc->createElementNS(self::SCHEMA_WSDL, 'binding');
                $el->setAttribute('name', $this->_serviceName . "Binding");
                $el->setAttribute('type', "tns:{$this->_serviceName}PortType");

                $s_binding = $doc->createElementNS(self::SCHEMA_SOAP, 'binding');
                $s_binding->setAttribute('style', 'document');
                $s_binding->setAttribute('transport', self::SCHEMA_SOAP_HTTP);
                $el->appendChild($s_binding);

                foreach ($this->_operations as $name => $params) {
                        $op = $doc->createElementNS(self::SCHEMA_WSDL, 'operation');
                        $op->setAttribute('name', $name);
                        foreach (array('input', 'output') as $type) {
                                $sel = $doc->createElementNS(self::SCHEMA_WSDL, $type);
                                $s_body = $doc->createElementNS(self::SCHEMA_SOAP, 'body');
                                $s_body->setAttribute('use', 'literal');
                                $sel->appendChild($s_body);
                                $op->appendChild($sel);
                        }
                        $el->appendChild($op);
                }
                $root->appendChild($el);
        }

        protected function addService(DomDocument $doc, DomElement $root)
        {
                $el = $doc->createElementNS(self::SCHEMA_WSDL, 'service');
                $el->setAttribute('name', $this->_serviceName . "Service");

                $port = $doc->createElementNS(self::SCHEMA_WSDL, 'port');
                $port->setAttribute('name', $this->_serviceName . "Port");
                $port->setAttribute('binding', "tns:{$this->_serviceName}Binding");

                $addr = $doc->createElementNS(self::SCHEMA_SOAP, 'address');
                $addr->setAttribute('location', $this->_endpoint);

                $port->appendChild($addr);
                $el->appendChild($port);
                $root->appendChild($el);
        }

        protected function addTypes(DomDocument $doc, DomElement $root)
        {
                $types = $doc->createElementNS(self::SCHEMA_WSDL, 'types');
                $root->appendChild($types);
                $el = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'schema');
                $el->setAttribute('attributeFormDefault', 'unqualified');
                $el->setAttribute('elementFormDefault', 'unqualified');
                $el->setAttribute('targetNamespace', $this->_ns);
                $types->appendChild($el);

                foreach ($this->_complexTypes as $name => $data) {
                        if ($name == 'mixed') {
                                continue;
                        }
                        if (($str = strrchr($name, '\\'))) {
                                $name = trim($str, '\\');
                        }
                        $ct = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'complexType');
                        $ct->setAttribute('name', $name);

                        $all = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'all');

                        foreach ($data as $prop) {
                                $p = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'element');
                                $p->setAttribute('name', $prop['name']);
                                $prefix = $root->lookupPrefix($this->_types[$prop['type']]['ns']);
                                $p->setAttribute('type', "$prefix:" . $this->_types[$prop['type']]['name']);
                                $all->appendChild($p);
                        }
                        $ct->appendChild($all);
                        $el->appendChild($ct);
                }

                // 
                // Add message types:
                // 
                foreach ($this->_operations as $name => $params) {
                        if (($str = strrchr($name, '\\'))) {
                                $name = trim($str, '\\');
                        }
                        foreach (array('input' => '', 'output' => 'Response') as $type => $postfix) {
                                $ce = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'element');
                                $fullName = "$name" . ucfirst($postfix);
                                $ce->setAttribute('name', $fullName);
                                $ce->setAttribute('type', 'tns:' . $fullName);
                                $el->appendChild($ce);

                                $ct = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'complexType');
                                $ct->setAttribute('name', $fullName);
                                $ctseq = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'sequence');
                                $ct->appendChild($ctseq);
                                foreach ($params[$type] as $param) {
                                        $pare = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'element');
                                        $pare->setAttribute('name', $param['name']);
                                        $prefix = $root->lookupPrefix($this->_types[$param['type']]['ns']);
                                        $pare->setAttribute('type', "$prefix:" . $this->_types[$param['type']]['name']);
                                        $pare->setAttribute('maxOccurs', $param['repeat']);
                                        $ctseq->appendChild($pare);
                                }
                                $el->appendChild($ct);
                        }
                }
        }

        /**
         * Return an XML representation of the WSDL file
         */
        public function toXML()
        {
                $wsdl = new DomDocument("1.0", "utf-8");
                $root = $wsdl->createElement('wsdl:definitions');
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:tns', $this->_ns);
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soap-env', self::SCHEMA_SOAP);
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsdl', self::SCHEMA_WSDL);
                $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soapenc', self::SOAP_SCHEMA_ENCODING);
                $root->setAttribute('targetNamespace', $this->_ns);
                $this->addTypes($wsdl, $root);
                $this->addMessages($wsdl, $root);
                $this->addPortType($wsdl, $root);
                $this->addBinding($wsdl, $root);
                $this->addService($wsdl, $root);

                $wsdl->formatOutput = true;
                $wsdl->appendChild($root);
                return $wsdl->saveXML();
        }

}

/* vim: set ts=2 sts=2 bs=2 ai expandtab : */
