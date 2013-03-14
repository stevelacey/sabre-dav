<?php

namespace Sabre\DAV\Exception;

/**
 * CannotSerialize
 *
 * The CannotSerialize exception is thrown when an attempt was made to
 * serialize an xml Element object that did not have a serializer implemented.
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class CannotSerialize extends \Sabre\DAV\Exception {
}
