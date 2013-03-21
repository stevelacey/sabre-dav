<?php

namespace Sabre\DAVACL\XML\Property;

use Sabre\DAV;
use Sabre\HTTP;

class ACLRestrictionsTest extends \PHPUnit_Framework_TestCase {

    function testConstruct() {

        $prop = new AclRestrictions();

    }

    function testSerializeEmpty() {

        $xmlUtil = new DAV\XMLUtil();

        $output = $xmlUtil->write([
            '{DAV:}root' => new AclRestrictions()
        ]);

        $expected = '<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:grant-only/>
  <d:no-invert/>
</d:root>
';
        $this->assertEquals($expected, $output);

    }


}
