<?php

namespace Sabre\CalDAV\XML\Property;

use Sabre\CalDAV;
use Sabre\DAV;

class AllowedSharingModesTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $sccs = new AllowedSharingModes(true,true);

    }

    /**
     * @depends testSimple
     */
    function testSerialize() {

        $property = new AllowedSharingModes(true,true);

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(<<<XML
<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
  <cs:can-be-shared/>
  <cs:can-be-published/>
</d:root>

XML
, $xml);

    }

}
