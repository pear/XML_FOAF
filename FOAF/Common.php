<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML_FOAF_Common
 *
 * XML FOAF Common methods
 *
 * PHP versions 4 and 5
 *
 * LICENSE:
 *
 * Copyright (c) 2003-2008 Davey Shafik and Synaptic Media.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The name of the author may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  XML
 * @package   XML_FOAF
 * @author    Davey Shafik <davey@php.net>
 * @copyright 2003-2008 Davey Shafik and Synaptic Media.
 * @license   http://opensource.org/licenses/bsd-license New BSD License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/XML_FOAF
 */


/**
 * XML FOAF Common methods
 *
 * @category  XML
 * @package   XML_FOAF
 * @author    Davey Shafik <davey@php.net>
 * @copyright 2003-2008 Davey Shafik and Synaptic Media.
 * @license   http://opensource.org/licenses/bsd-license New BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/XML_FOAF
 */
class XML_FOAF_Common
{
    /**
     * Check if a property is allows for the current foaf:Agent
     *
     * @param string $property name of the Property to check. Without a namespace
     *
     * @return boolean
     * @access public
     */
    function isAllowedForAgent($property)
    {
        $property     = strtolower($property);
        $common       = array (
            'name',
            'maker',
            'depiction',
            'fundedby',
            'logo',
            'page',
            'theme',
            'dnachecksum',
            'title',
            'nick',
            'givenname',
            'phone',
            'mbox',
            'mbox_sha1sum',
            'gender',
            'jabberid',
            'aimchatid',
            'icqchatid',
            'yahoochatid',
            'msnchatid',
            'homepage',
            'weblog',
            'made',
            'holdsaccount');
        $person       = array (
            'geekcode',
            'interest',
            'firstname',
            'surname',
            'family_name',
            'plan',
            'img',
            'myersbriggs',
            'workplacehomepage',
            'workinfohomepage',
            'schoolhomepage',
            'knows',
            'publications',
            'currentproject',
            'pastproject',
            'based_near');
        $organization = array();
        $group        = array(
            'member',
            'membershipclass');
        if (in_array($property, $common) || in_array($property, ${$this->agent})) {
            return true;
        } else {
            return false;
        }
    }

}
