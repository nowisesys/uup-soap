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

namespace UUP\WebService\Wsdl\Parser;

/**
 * Document comment parser.
 * 
 * @method array getAuthor() Get author annotations.
 * @method array getParam() Get param annotations.
 * 
 * @method bool hasAuthor() Has author annotations.
 * @method bool hasParam() Has param annotations.
 * 
 * @author Anders Lövgren (Nowise Systems)
 */
class Comment
{

        /**
         * The summary text.
         * @var string 
         */
        private $_summary;
        /**
         * The description text.
         * @var array 
         */
        private $_description = array();
        /**
         * Discovered annotations.
         * @var array 
         */
        private $_annotations = array();

        /**
         * Constructor.
         * @param string $text The docblock text.
         */
        public function __construct($text)
        {
                $this->setCommentData($text);
        }

        public function __call($name, $arguments)
        {
                // 
                // Detect get or has:
                // 
                $func = substr($name, 0, 3);

                // 
                // The targetted annotation collection:
                // 
                $type = strtolower(substr($name, 3));

                switch ($func) {
                        case 'has':
                                return $this->hasAnnotation($type);
                        case 'get':
                                return $this->getAnnotation($type);
                }
        }

        public function __toString()
        {
                return $this->getDocument();
        }

        /**
         * Get text representation of this object.
         * 
         * @param string $join The join string for description.
         * @return string
         */
        public function getDocument($join = "\n")
        {
                $output = $this->getSummary();

                if ($this->hasDescription()) {
                        $output .= sprintf("\n\n%s", $this->getDescription($join));
                }
                if ($this->hasAnnotations()) {
                        $output .= "\n";
                }
                foreach ($this->getAnnotations() as $data) {
                        $output .= sprintf("\n" . implode("\n", $data));
                }

                return $output;
        }

        /**
         * Get summary text.
         * @return string
         */
        public function getSummary()
        {
                return $this->_summary;
        }

        /**
         * Get description text.
         * 
         * <code>
         * $result = $comment->getDescription();        // array
         * $result = $comment->getDescription("\n");    // string
         * </code>
         * 
         * @param bool|string $join The join string.
         * @return array|string
         */
        public function getDescription($join = false)
        {
                if ($join) {
                        return implode($join, $this->_description);
                } else {
                        return $this->_description;
                }
        }

        /**
         * Check if descriptions exist.
         * @return bool
         */
        public function hasDescription()
        {
                return count($this->_description) != 0;
        }

        /**
         * Get return annotation.
         * @return string
         */
        public function getReturn()
        {
                return $this->getAnnotation('return')[0];
        }

        /**
         * Check if return annotation exist.
         * @return bool
         */
        public function hasReturn()
        {
                return $this->hasAnnotation('return');
        }

        /**
         * Check if annotations exists.
         * @return bool
         */
        public function hasAnnotations()
        {
                return count($this->_annotations) != 0;
        }

        /**
         * Get all annotations.
         * @return array
         */
        public function getAnnotations()
        {
                return $this->_annotations;
        }

        /**
         * Get annotations of type.
         * 
         * <code>
         * $result = $comment->getAnnotation("return"); // Get return annotations.
         * $result = $comment->getAnnotation("params"); // Get method params.
         * $result = $comment->getAnnotation("author"); // Get all authors.
         * </code>
         * 
         * @param string $type The annotation name.
         * @return array
         */
        public function getAnnotation($type)
        {
                if (isset($this->_annotations[$type])) {
                        return $this->_annotations[$type];
                }
        }

        /**
         * Check if annotation exist.
         * 
         * @param string $type The annotation name.
         * @return array
         */
        public function hasAnnotation($type)
        {
                return isset($this->_annotations[$type]);
        }

        /**
         * Explore input test.
         * 
         * @param string $text The text to explore.
         */
        private function setCommentData($text)
        {
                $parts = $this->getInputArray($text);

                foreach ($parts as $index => $part) {
                        $this->addCommentText(trim($part), $index);
                }

                $this->cleanupDescription();
        }

        /**
         * Remove empty leading and trailing entries.
         */
        private function cleanupDescription()
        {
                $description = implode("\n", $this->_description);
                $description = trim($description, "\n");
                $description = explode("\n", $description);
                
                if(empty($description[0])) {
                        $this->_description = array();
                } else {
                        $this->_description = $description;
                }
        }

        /**
         * Get input data.
         * 
         * @param string $text The input text.
         * @return array
         */
        private function getInputArray($text)
        {
                return explode("\n", trim(str_replace(
                            array('/**', '*/', '*', '$'), array('', '', '', ''), $text
                )));
        }

        /**
         * Add new part.
         * 
         * @param string $text The part text.
         * @param int $index The position.
         */
        private function addCommentText($text, $index)
        {
                if ($index == 0) {
                        $this->setSummary($text);
                } elseif (empty($text)) {
                        $this->addEmptyString();
                } elseif ($text[0] == '@') {
                        $this->addAnnotation($text);
                } elseif ($text[0] == '<') {
                        $this->addElement($text);
                } else {
                        $this->addDescription($text);
                }
        }

        /**
         * Set summary text.
         * 
         * @param string $text The summary text.
         */
        private function setSummary($text)
        {
                $this->_summary = $text;
        }

        /**
         * Add anotation.
         * 
         * @param string $text The annotation text.
         */
        private function addAnnotation($text, $match = array())
        {
                if (!preg_match("/@(\w+)/", $text, $match)) {
                        return;
                }

                if (!isset($this->_annotations[$match[1]])) {
                        $this->_annotations[$match[1]] = array();
                        $this->_annotations[$match[1]][] = $text;
                } else {
                        $this->_annotations[$match[1]][] = $text;
                }
        }

        /**
         * 
         * @param type $text
         * @return type
         */
        private function addDescription($text)
        {
                $this->_description[] = $text;
        }

        /**
         * Add empty string.
         */
        private function addEmptyString()
        {
                if (count($this->_description) > 0) {
                        $this->_description[] = "";
                }
        }

        /**
         * Add HTML element.
         * 
         * The element will be encoded before inserted in the description array.
         * 
         * @param string $text The HTML element.
         */
        private function addElement($text)
        {
                $this->_description[] = htmlspecialchars($text);
        }

}
