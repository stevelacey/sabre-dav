<?php

namespace Sabre\DAV\XML\Request;

class PropFindTest extends \PHPUnit_Framework_TestCase {

    public function testParse() {

        $xml = <<<XML
<?xml version="1.0"?>
<d:propfind xmlns:d="DAV:">
  <d:prop>
    <d:someprop />
    <d:anotherprop />
  </d:prop>
</d:propfind>
XML;
        
        $xmlUtil = new \Sabre\DAV\XMLUtil();
        $result = $xmlUtil->parse($xml);
        $this->assertEquals([
            '{DAV:}someprop',
            '{DAV:}anotherprop',
        ], $result->properties);

    }

    public function testParseAllProp() {

        $xml = <<<XML
<?xml version="1.0"?>
<d:propfind xmlns:d="DAV:">
  <d:allprop />
</d:propfind>
XML;
        
        $xmlUtil = new \Sabre\DAV\XMLUtil();
        $result = $xmlUtil->parse($xml);
        $this->assertNull($result->properties);
        $this->assertTrue($result->allProp);

    }

    public function testParseIgnoreMappedElemes() {

        // There was a problem with this parser. Everything in the <d:prop />
        // list should just a flat list of elements, but if a mapped element
        // name was in the list, this parser will try to invoke the element 
        // class.  

        $xml = <<<XML
<?xml version="1.0"?>
<d:propfind xmlns:d="DAV:">
  <d:prop>
     <d:multistatus>some random stuff</d:multistatus>
  </d:prop>
</d:propfind>
XML;
        
        $xmlUtil = new \Sabre\DAV\XMLUtil();
        $result = $xmlUtil->parse($xml);
        $this->assertEquals(['{DAV:}multistatus'], $result->properties);

    }
}
