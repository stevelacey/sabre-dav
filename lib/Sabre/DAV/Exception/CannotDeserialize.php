<?php

namespace Sabre\DAV\Exception;

/**
 * CannotDeserialize
 *
 * The CannotDeserialize exception is thrown when an attempt was made to
 * de serialize an xml Element object that did not have a de serializer
 * implemented.
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class CannotDeserialize extends \Sabre\DAV\Exception {
}
