<?php

namespace Sabre\DAV\XML\Property;

use
    Sabre\XML\Element,
    Sabre\XML\Reader,
    Sabre\XML\Writer,
    Sabre\HTTP;

/**
 * This property represents the {DAV:}getlastmodified property.
 *
 * Defined in:
 * http://tools.ietf.org/html/rfc4918#section-15.7
 *
 * Although this is normally a simple property, windows requires us to add
 * some new attributes.
 *
 * This class uses unix timestamps internally, and converts them to RFC 1123 times for
 * serialization
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class GetLastModified implements Element {

    /**
     * time
     *
     * @var \DateTime
     */
    public $time;

    /**
     * Constructor 
     *
     * @param int|DateTime $time
     */
    public function __construct($time) {

        if ($time instanceof \DateTime) {
            $this->time = $time;
        } elseif (is_int($time) || ctype_digit($time)) {
            $this->time = new \DateTime('@' . $time);
        } else {
            $this->time = new \DateTime($time);
        }

        // Setting timezone to UTC
        $this->time->setTimezone(new \DateTimeZone('UTC'));

    }

    /**
     * getTime
     *
     * @return \DateTime
     */
    public function getTime() {

        return $this->time;

    }

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * Important note 2: If you are writing any new elements, you are also
     * responsible for closing them.
     *
     * @param Writer $writer
     * @return void
     */
    public function xmlSerialize(Writer $writer) {

        $writer->write(
            HTTP\Util::toHTTPDate($this->time)
        );

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * Important note 2: You are responsible for advancing the reader to the
     * next element. Not doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseInnerTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @param Reader $reader
     * @return mixed
     */
    static public function xmlDeserialize(Reader $reader) {

        return
            new self($reader->parseInnerTree());

    }
}

