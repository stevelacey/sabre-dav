<?php

namespace Sabre\CardDAV;

use Sabre\DAV;

class AddressBookQueryParserTest extends \PHPUnit_Framework_TestCase {

    public $elementMap = [
        '{DAV:}prop' => 'Sabre\\XML\\Element\\KeyValue',
        '{urn:ietf:params:xml:ns:carddav}addressbook-query' => 'Sabre\\CardDAV\\XML\\Request\\AddressBookQueryReport',
        '{urn:ietf:params:xml:ns:carddav}prop-filter'       => 'Sabre\\CardDAV\\XML\\Filter\\PropFilter',
        '{urn:ietf:params:xml:ns:carddav}param-filter'      => 'Sabre\\CardDAV\\XML\\Filter\\ParamFilter',
    ];

    function parse($xml) {

        $xml = implode("\n", $xml);

        $util = new DAV\XMLUtil();
        $util->elementMap = $this->elementMap;

        $result = $util->parse($xml);
        return $result;

    }

    function testFilterBasic() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter>',
            '     <c:prop-filter name="NICKNAME" />',
            '   </c:filter>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

        $this->assertEquals(
            array('{DAV:}foo'),
            $q->properties
        );

        $this->assertEquals(
            array(
                array(
                    'name' => 'NICKNAME',
                    'test' => 'anyof',
                    'is-not-defined' => false,
                    'param-filters' => array(),
                    'text-matches' => array(),
                ),
            ),
            $q->filter
        );

        $this->assertNull($q->limit);
        $this->assertEquals('anyof', $q->test);

    }

    function testNoFilter() {

        // This is non-standard, but helps working around a KDE bug
        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

        $this->assertEquals(
            array('{DAV:}foo'),
            $q->properties
        );

        $this->assertEquals(
            array(),
            $q->filter
        );

        $this->assertNull($q->limit);
        $this->assertEquals('anyof', $q->test);

    }

    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testFilterDoubleFilter() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter>',
            '     <c:prop-filter name="NICKNAME" />',
            '   </c:filter>',
            '   <c:filter>',
            '     <c:prop-filter name="NICKNAME" />',
            '   </c:filter>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

    }
    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testFilterCorruptTest() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter test="foo">',
            '     <c:prop-filter name="NICKNAME" />',
            '   </c:filter>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

    }

    function testPropFilter() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter test="allof">',
            '     <c:prop-filter name="NICKNAME" />',
            '     <c:prop-filter name="EMAIL" test="allof" />',
            '     <c:prop-filter name="FN">',
            '        <c:is-not-defined />',
            '     </c:prop-filter>',
            '   </c:filter>',
            '   <c:limit><c:nresults>4</c:nresults></c:limit>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

        $this->assertEquals(
            array(
                array(
                    'name' => 'NICKNAME',
                    'test' => 'anyof',
                    'is-not-defined' => false,
                    'param-filters' => array(),
                    'text-matches' => array(),
                ),
                array(
                    'name' => 'EMAIL',
                    'test' => 'allof',
                    'is-not-defined' => false,
                    'param-filters' => array(),
                    'text-matches' => array(),
                ),
                array(
                    'name' => 'FN',
                    'test' => 'anyof',
                    'is-not-defined' => true,
                    'param-filters' => array(),
                    'text-matches' => array(),
                ),
            ),
            $q->filter
        );

        $this->assertEquals(4,$q->limit);
        $this->assertEquals('allof', $q->test);

    }

    function testParamFilter() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter>',
            '     <c:prop-filter name="NICKNAME">',
            '        <c:param-filter name="BLA" />',
            '        <c:param-filter name="BLA2">',
            '          <c:is-not-defined />',
            '        </c:param-filter>',
            '     </c:prop-filter>',
            '   </c:filter>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

        $this->assertEquals(
            array(
                array(
                    'name' => 'NICKNAME',
                    'test' => 'anyof',
                    'is-not-defined' => false,
                    'param-filters' => array(
                        array(
                            'name' => 'BLA',
                            'is-not-defined' => false,
                            'text-match' => null
                        ),
                        array(
                            'name' => 'BLA2',
                            'is-not-defined' => true,
                            'text-match' => null
                        ),
                    ),
                    'text-matches' => array(),
                ),
            ),
            $q->filter
        );

    }

    function testTextMatch() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter>',
            '     <c:prop-filter name="NICKNAME">',
            '        <c:text-match>evert</c:text-match>',
            '        <c:text-match collation="i;octet">evert</c:text-match>',
            '        <c:text-match negate-condition="yes">rene</c:text-match>',
            '        <c:text-match match-type="starts-with">e</c:text-match>',
            '        <c:param-filter name="BLA">',
            '            <c:text-match>foo</c:text-match>',
            '        </c:param-filter>',
            '     </c:prop-filter>',
            '   </c:filter>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

        $this->assertEquals(
            array(
                array(
                    'name' => 'NICKNAME',
                    'test' => 'anyof',
                    'is-not-defined' => false,
                    'param-filters' => array(
                        array(
                            'name' => 'BLA',
                            'is-not-defined' => false,
                            'text-match' => array(
                                'negate-condition' => false,
                                'collation' => 'i;unicode-casemap',
                                'match-type' => 'contains',
                                'value'     => 'foo',
                            ),
                        ),
                    ),
                    'text-matches' => array(
                        array(
                            'negate-condition' => false,
                            'collation' => 'i;unicode-casemap',
                            'match-type' => 'contains',
                            'value'     => 'evert',
                        ),
                        array(
                            'negate-condition' => false,
                            'collation' => 'i;octet',
                            'match-type' => 'contains',
                            'value'     => 'evert',
                        ),
                        array(
                            'negate-condition' => true,
                            'collation' => 'i;unicode-casemap',
                            'match-type' => 'contains',
                            'value'     => 'rene',
                        ),
                        array(
                            'negate-condition' => false,
                            'collation' => 'i;unicode-casemap',
                            'match-type' => 'starts-with',
                            'value'     => 'e',
                        ),
                    ),
                ),
            ),
            $q->filter
        );

    }

    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testBadTextMatch() {

        $xml = array(
            '<?xml version="1.0"?>',
            '<c:addressbook-query xmlns:c="urn:ietf:params:xml:ns:carddav" xmlns:d="DAV:">',
            '   <d:prop>',
            '      <d:foo />',
            '   </d:prop>',
            '   <c:filter>',
            '     <c:prop-filter name="NICKNAME">',
            '        <c:text-match match-type="foo">evert</c:text-match>',
            '     </c:prop-filter>',
            '   </c:filter>',
            '</c:addressbook-query>'
        );

        $q = $this->parse($xml);

    }
}
