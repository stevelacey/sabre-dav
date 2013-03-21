<?php

namespace Sabre\DAV; 

class XMLUtilTest extends \PHPUnit_Framework_TestCase {

    function testToClarkNotation() {

        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><test1 xmlns="http://www.example.org/">Testdoc</test1>');

        $this->assertEquals(
            '{http://www.example.org/}test1',
            XMLUtil::toClarkNotation($dom->firstChild)
        );

    }

    function testToClarkNotation2() {

        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><s:test1 xmlns:s="http://www.example.org/">Testdoc</s:test1>');

        $this->assertEquals(
            '{http://www.example.org/}test1',
            XMLUtil::toClarkNotation($dom->firstChild)
        );

    }

    function testToClarkNotationDAVNamespace() {

        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><s:test1 xmlns:s="urn:DAV">Testdoc</s:test1>');

        $this->assertEquals(
            '{DAV:}test1',
            XMLUtil::toClarkNotation($dom->firstChild)
        );

    }

    function testToClarkNotationNoElem() {

        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><s:test1 xmlns:s="urn:DAV">Testdoc</s:test1>');

        $this->assertNull(
            XMLUtil::toClarkNotation($dom->firstChild->firstChild)
        );

    }

    function testConvertDAVNamespace() {

        $xml='<?xml version="1.0"?><document xmlns="DAV:">blablabla</document>';
        $this->assertEquals(
            '<?xml version="1.0"?><document xmlns="urn:DAV">blablabla</document>',
            XMLUtil::convertDAVNamespace($xml)
        );

    }

    function testConvertDAVNamespace2() {

        $xml='<?xml version="1.0"?><s:document xmlns:s="DAV:">blablabla</s:document>';
        $this->assertEquals(
            '<?xml version="1.0"?><s:document xmlns:s="urn:DAV">blablabla</s:document>',
            XMLUtil::convertDAVNamespace($xml)
        );

    }

    function testConvertDAVNamespace3() {

        $xml='<?xml version="1.0"?><s:document xmlns="http://bla" xmlns:s="DAV:" xmlns:z="http://othernamespace">blablabla</s:document>';
        $this->assertEquals(
            '<?xml version="1.0"?><s:document xmlns="http://bla" xmlns:s="urn:DAV" xmlns:z="http://othernamespace">blablabla</s:document>',
            XMLUtil::convertDAVNamespace($xml)
        );

    }

    function testConvertDAVNamespace4() {

        $xml='<?xml version="1.0"?><document xmlns=\'DAV:\'>blablabla</document>';
        $this->assertEquals(
            '<?xml version="1.0"?><document xmlns=\'urn:DAV\'>blablabla</document>',
            XMLUtil::convertDAVNamespace($xml)
        );

    }

    function testConvertDAVNamespaceMixedQuotes() {

        $xml='<?xml version="1.0"?><document xmlns=\'DAV:" xmlns="Another attribute\'>blablabla</document>';
        $this->assertEquals(
            $xml,
            XMLUtil::convertDAVNamespace($xml)
        );

    }

    /**
     * @depends testConvertDAVNamespace
     */
    function testLoadDOMDocument() {

        $xml='<?xml version="1.0"?><document></document>';
        $dom = XMLUtil::loadDOMDocument($xml);
        $this->assertTrue($dom instanceof \DOMDocument);

    }

    /**
     * @depends testLoadDOMDocument
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testLoadDOMDocumentEmpty() {

        XMLUtil::loadDOMDocument('');

    }

    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     * @depends testConvertDAVNamespace
     */
    function testLoadDOMDocumentInvalid() {

        $xml='<?xml version="1.0"?><document></docu';
        $dom = XMLUtil::loadDOMDocument($xml);

    }

    /**
     * @depends testLoadDOMDocument
     */
    function testLoadDOMDocumentUTF16() {

        $xml='<?xml version="1.0" encoding="UTF-16"?><root xmlns="DAV:">blabla</root>';
        $xml = iconv('UTF-8','UTF-16LE',$xml);
        $dom = XMLUtil::loadDOMDocument($xml);
        $this->assertEquals('blabla',$dom->firstChild->nodeValue);

    }

    function testParseClarkNotation() {

        $this->assertEquals(array(
            'DAV:',
            'foo',
        ), XMLUtil::parseClarkNotation('{DAV:}foo'));

        $this->assertEquals(array(
            'http://example.org/ns/bla',
            'bar-soap',
        ), XMLUtil::parseClarkNotation('{http://example.org/ns/bla}bar-soap'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testParseClarkNotationFail() {

        XMLUtil::parseClarkNotation('}foo');

    }

}

