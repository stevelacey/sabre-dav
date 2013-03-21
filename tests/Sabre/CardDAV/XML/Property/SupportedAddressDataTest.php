<?php

namespace Sabre\CardDAV\XML\Property;

use Sabre\CardDAV;
use Sabre\DAV;

class SupportedAddressDataDataTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $property = new SupportedAddressData();

    }

    /**
     * @depends testSimple
     */
    function testSerialize() {

        $property = new SupportedAddressData();

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CardDAV\Plugin::NS_CARDDAV] = 'card';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:card="urn:ietf:params:xml:ns:carddav">
  <card:address-data-type content-type="text/vcard" version="3.0"/>
</d:root>
', $xml);

    }

}
