<?php

namespace UUP\WebService\Wsdl\Parser;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2018-08-22 at 19:02:59.
 */
class CommentTest extends \PHPUnit_Framework_TestCase
{

        /**
         * @var Comment
         */
        protected $object;

        /**
         * Sets up the fixture, for example, opens a network connection.
         * This method is called before a test is executed.
         */
        protected function setUp()
        {
                $this->object = new Comment("/**
         * Queues an job for later execution.
         *
         * This text should appear inside the description.
         * 
         * Here are some
         * more
         * text for description.
         *
         * <code>
         * \$result = \$service->enqueue(\$indata, true);
         * </code>
         * 
         * @param string \$indata The input data.
         * @param bool \$immediate Schedule for immediate execution.
         * @return QueuedJob[]
         
         * @author Anders Lövgren
         * @author Oscar Svensson <oscarsv@gmail.com>
         * 
         * @see https://nowise.se/oss/uup-soap/
         */");
        }

        /**
         * Tears down the fixture, for example, closes a network connection.
         * This method is called after a test is executed.
         */
        protected function tearDown()
        {
                
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::__call
         */
        public function test__call()
        {
                $expect = true;
                $actual = $this->object->hasAuthor();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);

                $expect = array(
                        '@author Anders Lövgren',
                        '@author Oscar Svensson <oscarsv@gmail.com>'
                );
                $actual = $this->object->getAuthor();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::getSummary
         */
        public function testGetSummary()
        {
                $expect = "Queues an job for later execution.";
                $actual = $this->object->getSummary();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::getDescription
         */
        public function testGetDescription()
        {
                $actual = $this->object->getDescription(false, false);
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals(9, count($actual));

                $actual = $this->object->getDescription(false, true);
                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals(7, count($actual));

                $actual = $this->object->getDescription(true, false);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals(158, strlen($actual));

                $actual = $this->object->getDescription(true, true);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals(156, strlen($actual));
                
                $actual = $this->object->getDescription("\n\t", false);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals(166, strlen($actual));

                $actual = $this->object->getDescription("\n\t", true);
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));
                $this->assertEquals(162, strlen($actual));
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::hasDescription
         */
        public function testHasDescription()
        {
                $expect = true;
                $actual = $this->object->hasDescription();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::getAnnotations
         */
        public function testGetAnnotations()
        {
                $expect = array("param", "return", "author", "see");
                $actual = $this->object->getAnnotations();

                $this->assertNotNull($actual);
                $this->assertTrue(is_array($actual));
                $this->assertEquals(4, count($actual));
                $this->assertEquals($expect, array_keys($actual));
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::getReturn
         */
        public function testGetReturn()
        {
                $expect = "@return QueuedJob[]";
                $actual = $this->object->getReturn();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::hasReturn
         */
        public function testHastReturn()
        {
                $expect = true;
                $actual = $this->object->hasReturn();
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::getAnnotation
         */
        public function testGetAnnotation()
        {
                $expect = null;
                $actual = $this->object->getAnnotation("missing");
                $this->assertEquals($expect, $actual);

                $expect = array("@see https://nowise.se/oss/uup-soap/");
                $actual = $this->object->getAnnotation("see");
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::hasAnnotation
         */
        public function testHasAnnotation()
        {
                $expect = false;
                $actual = $this->object->hasAnnotation("missing");
                $this->assertEquals($expect, $actual);

                $expect = true;
                $actual = $this->object->hasAnnotation("see");
                $this->assertNotNull($actual);
                $this->assertEquals($expect, $actual);
        }

        /**
         * @covers UUP\WebService\Wsdl\Parser\Comment::getDocument
         */
        public function testGetDocument()
        {
                $actual = $this->object->getDocument();
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));

                $expect = 421;
                $actual = strlen($actual);
                $this->assertEquals($expect, $actual);

                $actual = (string) $this->object;
                $this->assertNotNull($actual);
                $this->assertTrue(is_string($actual));

                $expect = 421;
                $actual = strlen($actual);
                $this->assertEquals($expect, $actual);
        }

}
