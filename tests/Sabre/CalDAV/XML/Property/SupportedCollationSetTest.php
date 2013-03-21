<?php

namespace Sabre\CalDAV\XML\Property;

use Sabre\CalDAV;
use Sabre\DAV;

class SupportedCollationSetTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $scs = new SupportedCollationSet();

    }

    /**
     * @depends testSimple
     */
    function testSerialize() {

        $property = new SupportedCollationSet();

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
  <cal:supported-collation>i;ascii-casemap</cal:supported-collation>
  <cal:supported-collation>i;octet</cal:supported-collation>
  <cal:supported-collation>i;unicode-casemap</cal:supported-collation>
</d:root>
', $xml);

    }

}
