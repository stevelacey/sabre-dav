<?php

namespace Sabre\DAVACL\XML\Property;

use 
    Sabre\DAV,
    Sabre\HTTP,
    Sabre\DAV\XMLUtil;


class SupportedPrivilegeSetTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $prop = new SupportedPrivilegeSet(array(
            'privilege' => '{DAV:}all',
        ));

    }


    /**
     * @depends testSimple
     */
    function testSerializeSimple() {

        $prop = new SupportedPrivilegeSet(array(
            'privilege' => '{DAV:}all',
        ));

        $xmlUtil = new XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}supported-privilege-set' => $prop
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:supported-privilege-set xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:supported-privilege>
    <d:privilege>
      <d:all/>
    </d:privilege>
  </d:supported-privilege>
</d:supported-privilege-set>
', $xml);

    }

    /**
     * @depends testSimple
     */
    function testSerializeAggregate() {

        $prop = new SupportedPrivilegeSet(array(
            'privilege' => '{DAV:}all',
            'abstract'  => true,
            'aggregates' => array(
                array(
                    'privilege' => '{DAV:}read',
                ),
                array(
                    'privilege' => '{DAV:}write',
                    'description' => 'booh',
                ),
            ),
        ));

        $xmlUtil = new XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}supported-privilege-set' => $prop
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:supported-privilege-set xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:supported-privilege>
    <d:privilege>
      <d:all/>
    </d:privilege>
    <d:abstract/>
    <d:supported-privilege>
      <d:privilege>
        <d:read/>
      </d:privilege>
    </d:supported-privilege>
    <d:supported-privilege>
      <d:privilege>
        <d:write/>
      </d:privilege>
      <d:description>booh</d:description>
    </d:supported-privilege>
  </d:supported-privilege>
</d:supported-privilege-set>
', $xml);

    }
}
