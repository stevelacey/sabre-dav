<?php

namespace Sabre\CalDAV\XML\Property;

use Sabre\CalDAV;
use Sabre\DAV;

class ScheduleCalendarTranspTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $sccs = new ScheduleCalendarTransp('transparent');
        $this->assertEquals('transparent', $sccs->getValue());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testBadArg() {

        $sccs = new ScheduleCalendarTransp('foo');

    }

    function values() {

        return array(
            array('transparent'),
            array('opaque'),
        );

    }

    /**
     * @depends testSimple
     * @dataProvider values
     */
    function testSerialize($value) {

        $property = new ScheduleCalendarTransp($value);

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
  <cal:' . $value . '/>
</d:root>
', $xml);

    }

    /**
     * @depends testSimple
     * @dataProvider values
     */
    function testUnserializer($value) {

        $xml = '<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:cal="' . CalDAV\Plugin::NS_CALDAV . '">
  <cal:'.$value.'/>
</d:root>';

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}root'] = 'Sabre\\CalDAV\\XML\\Property\\ScheduleCalendarTransp';
        $property = $xmlUtil->parse($xml);

        $this->assertTrue($property instanceof ScheduleCalendarTransp);
        $this->assertEquals($value, $property->getValue());

    }

    /**
     * @depends testSimple
     */
    function testUnserializerBadData() {

        $xml = '<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:cal="' . CalDAV\Plugin::NS_CALDAV . '">' .
'<cal:foo/>' .
'</d:root>';

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}root'] = 'Sabre\\CalDAV\\XML\\Property\\ScheduleCalendarTransp';
        $property = $xmlUtil->parse($xml);

        $this->assertNull($property);

    }
}
