<?php

namespace Sabre\DAVACL\XML\Property;

use
    Sabre\DAV,
    Sabre\HTTP,
    Sabre\DAV\XMLUtil;

class PrincipalTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $principal = new Principal(Principal::TYPE_UNAUTHENTICATED);
        $this->assertEquals(Principal::TYPE_UNAUTHENTICATED, $principal->getType());
        $this->assertNull($principal->getHref());

        $principal = new Principal(Principal::TYPE_AUTHENTICATED);
        $this->assertEquals(Principal::TYPE_AUTHENTICATED, $principal->getType());
        $this->assertNull($principal->getHref());

        $principal = new Principal(Principal::TYPE_HREF,'admin');
        $this->assertEquals(Principal::TYPE_HREF, $principal->getType());
        $this->assertEquals('admin',$principal->getHref());

    }

    /**
     * @depends testSimple
     * @expectedException \InvalidArgumentException
     */
    function testNoHref() {

        $principal = new Principal(Principal::TYPE_HREF);

    }

    /**
     * @depends testSimple
     */
    function testSerializeUnAuthenticated() {

        $prin = new Principal(Principal::TYPE_UNAUTHENTICATED);

        $xmlUtil = new XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}principal' => $prin
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:principal xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:unauthenticated/>
</d:principal>
', $xml);

    }


    /**
     * @depends testSerializeUnAuthenticated
     */
    function testSerializeAuthenticated() {

        $prin = new Principal(Principal::TYPE_AUTHENTICATED);

        $xmlUtil = new XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}principal' => $prin
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:principal xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:authenticated/>
</d:principal>
', $xml);

    }


    /**
     * @depends testSerializeUnAuthenticated
     */
    function testSerializeHref() {

        $prin = new Principal(Principal::TYPE_HREF,'principals/admin');

        $xmlUtil = new XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}principal' => $prin
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:principal xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:href>/principals/admin</d:href>
</d:principal>
', $xml);

    }

    function testUnserializeHref() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'<d:href>/principals/admin</d:href>' .
'</d:principal>';

        $xmlUtil = new XMLUtil();
        $xmlUtil->elementMap['{DAV:}principal'] = 'Sabre\\DAVACL\\XML\\Property\\Principal';

        $principal = $xmlUtil->parse($xml);

        $this->assertEquals(Principal::TYPE_HREF, $principal->getType());
        $this->assertEquals('/principals/admin', $principal->getHref());

    }

    function testUnserializeAuthenticated() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'  <d:authenticated />' .
'</d:principal>';

        $xmlUtil = new XMLUtil();
        $xmlUtil->elementMap['{DAV:}principal'] = 'Sabre\\DAVACL\\XML\\Property\\Principal';

        $principal = $xmlUtil->parse($xml);

        $this->assertEquals(Principal::TYPE_AUTHENTICATED, $principal->getType());

    }

    function testUnserializeUnauthenticated() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'  <d:unauthenticated />' .
'</d:principal>';

        $xmlUtil = new XMLUtil();
        $xmlUtil->elementMap['{DAV:}principal'] = 'Sabre\\DAVACL\\XML\\Property\\Principal';

        $principal = $xmlUtil->parse($xml);

        $this->assertEquals(Principal::TYPE_UNAUTHENTICATED, $principal->getType());

    }

    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testUnserializeUnknown() {

        $xml = '<?xml version="1.0"?>
<d:principal xmlns:d="DAV:">' .
'  <d:foo />' .
'</d:principal>';

        $xmlUtil = new XMLUtil();
        $xmlUtil->elementMap['{DAV:}principal'] = 'Sabre\\DAVACL\\XML\\Property\\Principal';

        $xmlUtil->parse($xml);

    }

}
