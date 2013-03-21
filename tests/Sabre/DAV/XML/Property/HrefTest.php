<?php

namespace Sabre\DAV\XML\Property;
use Sabre\DAV;

class HrefTest extends \PHPUnit_Framework_TestCase {

    function testConstruct() {

        $href = new Href(array('foo','bar'));
        $this->assertEquals(array('foo','bar'),$href->getHrefs());

    }

    function testSerialize() {

        $href = new Href(array('foo','bar'));
        $this->assertEquals(array('foo','bar'),$href->getHrefs());

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->baseUri = '/bla/';
        $xml = $xmlUtil->write([
            '{DAV:}anything' => $href
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:anything xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:href>/bla/foo</d:href>
  <d:href>/bla/bar</d:href>
</d:anything>
', $xml);

    }

    function testSerializeNoPrefix() {

        $href = new Href(array('foo','bar'), false);
        $this->assertEquals(array('foo','bar'),$href->getHrefs());

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->baseUri = '/bla/';
        $xml =$xmlUtil->write([
            '{DAV:}anything' => $href
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:anything xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:href>foo</d:href>
  <d:href>bar</d:href>
</d:anything>
', $xml);

    }

    function testUnserialize() {

        $xml = '<?xml version="1.0"?>
<d:anything xmlns:d="DAV:"><d:href>/bla/foo</d:href><d:href>/bla/bar</d:href></d:anything>
';

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}anything'] = 'Sabre\\DAV\\XML\\Property\\Href';
        $href = $xmlUtil->parse($xml);

        $this->assertEquals(array('/bla/foo','/bla/bar'),$href->getHrefs());

    }

    function testUnserializeIncompatible() {

        $xml = '<?xml version="1.0"?>
<d:anything xmlns:d="DAV:"><d:href2>/bla/foo</d:href2></d:anything>
';

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}anything'] = 'Sabre\\DAV\\XML\\Property\\Href';
        $href = $xmlUtil->parse($xml);
        $this->assertEquals(null, $href);

    }

}
