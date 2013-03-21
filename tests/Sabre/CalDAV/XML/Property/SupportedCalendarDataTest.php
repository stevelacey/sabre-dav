<?php

namespace Sabre\CalDAV\XML\Property;

use Sabre\CalDAV;
use Sabre\DAV;

class SupportedCalendarDataTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $sccs = new SupportedCalendarData();

    }

    /**
     * @depends testSimple
     */
    function testSerialize() {

        $property = new SupportedCalendarData();

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
  <cal:calendar-data content-type="text/calendar" version="2.0"/>
</d:root>
', $xml);

    }

}
