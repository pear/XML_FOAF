<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt                                   |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Davey Shafik <davey@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * FOAF Creator
 * @package XML_FOAF
 * @category XML
 */

require_once 'XML/FOAF/Common.php';

/**
 * FOAF Creator
 *
 * @package XML_FOAF
 * @author Davey <davey@php.net>
 * @version 0.2
 * @copyright Copyright 2003 Davey Shafik and Synaptic Media. All Rights Reserved.
 * @example docs/examples/example1.php Basic Usage of XML_FOAF
 * @todo Implement PEAR_Error handling
 */

class XML_FOAF extends XML_FOAF_Common
{

    /**
     * @var object XML_Tree object containing the FOAF RDF/XML Tree
     */

    var $foaf = null;

    /**
     * @var array Contains all namespaces in use
     */

    var $namespaces = array();

    /**
     * @var array Contains XML_Tree Child nodes for all FOAF elements
     */

    var $children = array();

    /**
     * @var object XML_Tree object for the FOAF
     */

    var $xml_tree = null;

    /**
     * XML_FOAF Constructor
     * @access private
     */

    function __construct ($xml_foaf_parser_object = null)
    {
        if(!is_null($xml_foaf_parser_object)) {
           if(is_a($xml_foaf_parser_object,'xml_foaf_parser')) {
                $regex[] = '/<([a-zA-Z0-9_]+:)?RDF .*?>/';
                $regex[] = '/<\/([a-zA-Z0-9_]+:)?RDF>/';
                $foaf = preg_replace($regex, '', $xml_foaf_parser_object->foaf_xml);
                require_once 'XML/Tree.php';
                $this->xml_tree = new XML_Tree;
                $this->foaf =& $this->xml_tree->getTreeFromString($foaf);
            }
        }
        $this->_setXmlns();
    }

    /**
     * XML_FOAF PHP4 Compatible Constructor
     * @see XML_FOAF::__construct
     */

    function XML_FOAF ($xml_foaf_parser_object = null)
    {
        $this->__construct($xml_foaf_parser_object);
    }

    /**
     * Create new FOAF Agent
     *
     * @foafstatus Unstable
     * @param string $agent_type Agent type, this can be Person, Organization, Group, Agent.
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_Organization FOAF Specification - foaf:Organization
     * @link http://xmlns.com/foaf/0.1/#term_Group FOAF Specification - foaf:Group
     * @link http://xmlns.com/foaf/0.1/#term_Person FOAF Specification - foaf:Person
     * @link http://xmlns.com/foaf/0.1/#term_Agent FOAF Specification - foaf:Agent
     */

    function newAgent($agent_type = 'Person')
    {
        require_once 'XML/Tree.php';
        $this->xml_tree = new XML_Tree;
        $agent_type = strtolower($agent_type);
        $this->agent = $agent_type;
        switch ($agent_type) {
            case 'group':
                $this->foaf =& $this->xml_tree->addRoot('foaf:Group');
                break;
            case 'organization':
                $this->foaf =& $this->xml_tree->addRoot('foaf:Organization');
                break;
            case 'agent':
                $this->foaf =& $this->xml_tree->addRoot('foaf:Agent');
            case 'person':
            default:
                $this->foaf =& $this->xml_tree->addRoot('foaf:Person');
                break;
        }
    }

    /**
     * Set the foaf:name of the Agent
     *
     * @foafstatus Testing
     * @param string $name Name for the Agent.
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_name FOAF Specification - foaf:name
     * @todo Allow for the xml:lang to be specified
     */

    function setName($name)
    {
        $this->children['name'] =& $this->foaf->addChild('foaf:name', $name);
    }

    /**
     * Add a foaf:depiction element
     *
     * @foafstatus Testing
     * @param string $uri URI For the Depicted image
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_depiction FOAF Specification - foaf:depiction
     */

    function addDepiction($uri)
    {
        $this->children['depiction'][] =& $this->foaf->addChild('foaf:depiction', '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:fundedBy element
     *
     * @foafstatus Unstable
     * @param string $uri URI for the funder
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_fundedBy FOAF Specification - foaf:fundedBy
     */

    function addFundedBy($uri)
    {
        $this->children['fundedby'][] =& $this->foaf->addChild('foaf:fundedBy', '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:logo element
     *
     * @foafstatus Testing
     * @param string $uri URI for Logo Image
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_logo FOAF Specification - foaf:logo
     */

    function addLogo($uri)
    {
        $this->children['logo'][] =& $this->foaf->addChild('foaf:logo', '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:page element
     *
     * @foafstatus Testing
     * @param string $document_uri URI for the Document being reference
     * @param string $title Title for the Document
     * @param string $description Description for the Document
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_page FOAF Specification - foaf:page
     */

    function addPage($document_uri,$title = null,$description = null)
    {
        $page =& $this->foaf->addChild('foaf:page');
        $document =& $page->addChild('foaf:Document', '', array('rdf:about' => $document_uri));
        if(!is_null($title)) {
            $document->addChild('dc:title', $title);
        }
        if(!is_null($description)) {
            $document->addChild('dc:description', $description);
        }
        $this->children['page'][] =& $page;
    }

    /**
     * Add a foaf:theme element
     *
     * @foafstatus unstable
     * @param string $uri URI for the Theme
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_theme FOAF Specification - foaf:theme
     */

    function addTheme($uri)
    {
        $this->children['theme'][] =& $this->foaf->addChild('foaf:theme', '', array('rdf:resource' => $uri));
    }

    /**
     * set foaf:title
     *
     * @foafstatus testing
     * @param string $title foaf:Agents title
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_title FOAF Specification - foaf:title
     */

    function setTitle($title)
    {
        $this->children['title'] =& $this->foaf->addChild('foaf:title', $title);
    }

    /**
     * Add a foaf:nick element
     *
     * @foafstatus testing
     * @param string $nick foaf:Agents Nickname
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_nick FOAF Specification - foaf:nick
     */

    function addNick($nick)
    {
        $this->children['nick'][] =& $this->foaf->addChild('foaf:nick', $nick);
    }

    /**
     * set foaf:givenname
     *
     * @foafstatus testing
     * @param string $given_name foaf:Agents Given Name
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_givenname FOAF Specification - foaf:givenname
     */

    function setGivenName($given_name)
    {
        $this->children['givenname'] =& $this->foaf->addChild('foaf:givenname', $given_name);
    }

    /**
     * Add a foaf:phone element
     *
     * @foafstatus testing
     * @param string $phone foaf:Agents Phone Number
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_phone FOAF Specification - foaf:phone
     */

    function addPhone($phone)
    {
        if (substr($phone,0,4) != 'tel:') {
            $phone = 'tel:' .$phone;
        }
        $this->children['phone'][] =& $this->foaf->addChild('foaf:phone', '', array('rdf:resource' => $phone));
    }

    /**
     * Add a foaf:mbox or foaf:mbox_sha1sum element
     *
     * @foafstatus testing
     * @param string $mbox Mailbox, either a mailto:addr, addr or an sha1 sum of mailto:addr
     * @param boolean $sha1 Whether or not to use foaf:mbox_sha1sum
     * @param boolean $is_sha1_hash Whether or not given $mbox is already an sha1 sum
     * @access public
     * @return void
     * @see XML_FOAF::setMboxSha1Sum
     * @link http://xmlns.com/foaf/0.1/#term_mbox_sha1sum FOAF Specification - foaf:mbox_sha1sum
     * @link http://xmlns.com/foaf/0.1/#term_mbox FOAF Specification - foaf:mbox
     */

    function addMbox($mbox,$sha1 = false,$is_sha1_hash = false)
    {
        if (substr($mbox,0,7) != 'mailto:' && $is_sha1_hash == false) {
            $mbox = 'mailto:' .$mbox;
        }

        if ($sha1 == true) {
            if ($is_sha1_hash == false) {
                $mbox = sha1($mbox);
            }
            $this->children['mbox_sha1sum'][] =& $this->foaf->addChild('foaf:mbox_sha1sum', $mbox);
        } else {
            $this->children['mbox'][] =& $this->foaf->addChild('foaf:mbox', '', array('rdf:resource' => $mbox));
        }

    }

    /**
     * Add a foaf:mbox_sha1sum element
     *
     * @foafstatus testing
     * @param string $mbox Mailbox, either a mailto:addr, addr or an sha1 sum of mailto:addr
     * @param boolean $is_sha1_hash Whether or not given $mbox is already an sha1 sum
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_mbox_sha1sum FOAF Specification - foaf:mbox_sha1sum
     */

    function addMboxSha1Sum($mbox,$is_sha1_sum = false)
    {
        $this->addMbox($mbox, true, $is_sha1_sum);
    }

    /**
     * set foaf:gender
     *
     * @foafstatus testing
     * @param string $gender foaf:Agents Gender (typically 'male' or 'female')
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_gender FOAF Specification - foaf:gender
     */

    function setGender($gender)
    {
        $this->children['gender'] =& $this->foaf->addChild('foaf:gender', strtolower($gender));
    }

    /**
     * Add a foaf:jabberID element
     *
     * @foafstatus testing
     * @param string $jabbed_id A Jabber ID
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_jabberID FOAF Specification - foaf:jabberID
     */

    function addJabberID($jabber_id)
    {
        $this->children['jabbberid'][] =& $this->foaf->addChild('foaf:jabberID', $jabber_id);
    }

    /**
     * Add a foaf:aimChatID element
     *
     * @foafstatus testing
     * @param string $aim_chat_id An AIM Username
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_aimChatID FOAF Specification - foaf:aimChatID
     */

    function addAimChatID($aim_chat_id)
    {
        $this->children['aimchatid'][] =& $this->foaf->addChild('foaf:aimChatID', $aim_chat_id);
    }

    /**
     * Add a foaf:icqChatID element
     *
     * @foafstatus testing
     * @param string $icq_chat_id An ICQ Number
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_icqChatID FOAF Specification - foaf:icqChatID
     */

    function addIcqChatID($icq_chat_id)
    {
        $this->children['icqchatid'][] =& $this->foaf->addChild('foaf:icqChatID', $icq_chat_id);
    }

    /**
     * Add a foaf:yahooChatID element
     *
     * @foafstatus testing
     * @param string $yahoo_chat_id A Yahoo! Messenger ID
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_yahooChatID FOAF Specification - foaf:yahooChatID
     */

    function addYahooChatID($yahoo_chat_id)
    {
        $this->children['yahoochatid'][] =& $this->foaf->addChild('foaf:yahooChatID', $yahoo_chat_id);
    }

    /**
     * Add a foaf:msnChatID element
     *
     * @foafstatus testing
     * @param string $msn_chat_id A MSN Chat username
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_msnChatID FOAF Specification - foaf:msnChatID
     */

    function addMsnChatID($msn_chat_id)
    {
        $this->children['msnchatid'][] =& $this->foaf->addChild('foaf:msnChatID', $msn_chat_id);
    }

    /**
     * Add a foaf:OnlineAccount element
     *
     * @foafstatus unstable
     * @param string $account_name Account Name
     * @param string $account_service_homepage URI to Account Service Homepage
     * @param string $acount_type Account type (e.g http://xmlns.com/foaf/0.1/OnlineChatAccount)
     * @access public
     * @return void
     * @see XML_FOAF::setOnlineChatAccount,XML_FOAF::setMsnChatID,XML_FOAF::setIcqChatID,XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID,XML_FOAF::setJabberID,XML_FOAF::setOnlineGamingAccount,XML_FOAF::setOnlineEcommerceAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage FOAF Specification - foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName FOAF Specification - foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineAccount FOAF Specification - foaf:OnlineAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount FOAF Specification - foaf:holdsAccount
     */

    function addOnlineAccount($account_name,$account_service_homepage = null,$account_type = null)
    {
        $holds_account =& $this->foaf->addChild('foaf:holdsAccount');
        $online_account =& $holds_account->addChild('foaf:OnlineAccount');
        $online_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_account->addChild('foaf:accountServiceHomepage', '', array('rdf:resource' => $account_service_homepage));
        }
        if (!is_null($account_type)) {
            $online_account->addChild('rdf:type', '', array('rdf:resource' => $account_type));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:OnlineChatAccount element
     *
     * @foafstatus unstable
     * @param string $account_name Account Name
     * @param string $account_service_homepage URI Tto Account Service Homepage
     * @access public
     * @return void
     * @see XML_FOAF::setOnlineAccount,XML_FOAF::setMsnChatID,XML_FOAF::setIcqChatID,XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID,XML_FOAF::setJabberID,XML_FOAF::setOnlineGamingAccount,XML_FOAF::setOnlineEcommerceAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage FOAF Specification - foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName FOAF Specification - foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineChatAccount FOAF Specification - foaf:OnlineChatAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount FOAF Specification - foaf:holdsAccount
     */

    function addOnlineChatAccount($account_name,$account_service_homepage)
    {
        $holds_account =& $this->foaf->addChild('foaf:holdsAccount');
        $online_chat_account =& $holds_account->addChild('foaf:OnlineChatAccount');
        $online_chat_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_chat_account->addChild('foaf:accountServiceHomepage', '', array('rdf:resource' => $account_service_homepage));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:OnlineGamingAccount element
     *
     * @foafstatus unstable
     * @param string $account_name Account Name
     * @param string $account_service_homepage URI Tto Account Service Homepage
     * @access public
     * @return void
     * @see XML_FOAF::setOnlineAccount,XML_FOAF::setMsnChatID,XML_FOAF::setIcqChatID,XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID,XML_FOAF::setJabberID,XML_FOAF::setOnlineChatAccount,XML_FOAF::setOnlineEcommerceAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage FOAF Specification - foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName FOAF Specification - foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineChatAccount FOAF Specification - foaf:OnlineChatAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount FOAF Specification - foaf:holdsAccount
     */

    function addOnlineGamingAccount($account_name,$account_service_homepage)
    {
        $holds_account =& $this->foaf->addChild('foaf:holdsAccount');
        $online_gaming_account =& $holds_account->addChild('foaf:OnlineGamingAccount');
        $online_gaming_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_gaming_account->addChild('foaf:accountServiceHomepage', '', array('rdf:resource' => $account_service_homepage));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:OnlineEcommerceAccount element
     *
     * @foafstatus unstable
     * @param string $account_name Account Name
     * @param string $account_service_homepage URI Tto Account Service Homepage
     * @access public
     * @return void
     * @see XML_FOAF::setOnlineAccount,XML_FOAF::setMsnChatID,XML_FOAF::setIcqChatID,XML_FOAF::setAimChatID
     * @see XML_FOAF::setYahooChatID,XML_FOAF::setJabberID,XML_FOAF::setOnlineChatAccount,XML_FOAF::setOnlineGamingAccount
     * @link http://xmlns.com/foaf/0.1/#term_accountServiceHomepage FOAF Specification - foaf:accountServiceHomepage
     * @link http://xmlns.com/foaf/0.1/#term_accountName FOAF Specification - foaf:accountName
     * @link http://xmlns.com/foaf/0.1/#term_OnlineChatAccount FOAF Specification - foaf:OnlineChatAccount
     * @link http://xmlns.com/foaf/0.1/#term_holdsAccount FOAF Specification - foaf:holdsAccount
     */

    function addOnlineEcommerceAccount($account_name,$account_service_homepage)
    {
        $holds_account =& $this->foaf->addChild('foaf:holdsAccount');
        $online_ecommerce_account =& $holds_account->addChild('foaf:OnlineEcommerceAccount');
        $online_ecommerce_account->addChild('foaf:accountName', $account_name);
        if (!is_null($account_service_homepage)) {
            $online_ecommerce_account->addChild('foaf:accountServiceHomepage', '', array('rdf:resource' => $account_service_homepage));
        }
        $this->children['holdsaccount'][] =& $holds_account;
    }

    /**
     * Add a foaf:homepage element
     *
     * @foafstatus stable
     * @param string $uri URI for the Homepage
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_homepage FOAF Specification - foaf:homepage
     */

    function addHomepage($uri)
    {
        $this->children['homepage'][] =& $this->foaf->addChild('foaf:homepage', '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:weblog element
     *
     * @foafstatus testing
     * @param string $uri URI for the weblog
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_weblog FOAF Specification - foaf:weblog
     */

    function addWeblog($uri)
    {
        $this->children['weblog'][] =& $this->foaf->addChild('foaf:weblog', '', array('rdf:resource' => $uri));
    }

    /**
     * Add a foaf:made element
     * @foafstatus testing
     * @param string $uri URI for the thing foaf:Agent made
     * @access public
     * @return void
     * @link http://xmlns.com/foaf/0.1/#term_made
     */

    function addMade($uri)
    {
        $this->children['made'][] =& $this->foaf->addChild('foaf:made', '', array('rdf:resource' => $uri));
    }

    /**#@+
     * @todo Return a PEAR_Error instead of false
     */

    /* foaf:Person */

    /**
     * set foaf:geekcode
     *
     * @foafstatus testing
     * @param string $geek_code foaf:Agents Geek Code
     * @access public
     * @return boolean
     * @link http://www.joereiss.net/geek/geek.html Geek Code Generator
     * @link http://www.geekcode.com/geek.html Geek Code official website
     * @link http://xmlns.com/foaf/0.1/#term_geekcode FOAF Specification - foaf:geekcode
     */

    function setGeekcode($geek_code)
    {
        if($this->isAllowedForAgent('geekcode')) {
            $this->children['geekcode'] =& $this->foaf->addChild('foaf:geekcode', $geek_code);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:firstName
     *
     * @foafstatus testing
     * @param string $first_name foaf:Agents First Name
     * @access public
     * @return boolean
     * @see XML_FOAF::setGivenName,XML_FOAF::setName
     * @link http://xmlns.com/foaf/0.1/#term_firstName FOAF Specification - foaf:firstName
     */

    function setFirstName($first_name)
    {
        if($this->isAllowedForAgent('firstname')) {
            $this->children['firstname'] =& $this->foaf->addChild('foaf:firstName', $first_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:surname
     *
     * @foafstatus testing
     * @param string $surname foaf:Agents Surname
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_surname FOAF Specification - foaf:surname
     */

    function setSurname($surname)
    {
        if($this->isAllowedForAgent('surname')) {
            $this->children['surname'] =& $this->foaf->addChild('foaf:surname', $surname);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:familyName
     *
     * @foafstatus testing
     * @param string $family_name foaf:Agents Family name
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_firstName FOAF Specification - foaf:familyName
     */

    function setFamilyName($family_name)
    {
        if($this->isAllowedForAgent('family_name')) {
            $this->children['familyname'] =& $this->foaf->addChild('foaf:family_name', $family_name);
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:plan
     *
     * @foafstatus testing
     * @param string $plan .plan file contents
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_plan FOAF Specification - foaf:plan
     */

    function setPlan($plan)
    {
        if($this->isAllowedForAgent('plan')) {
            $this->children['plan'] =& $this->foaf->addChild('foaf:plan', $plan);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:img element
     *
     * @foafstatus testing
     * @param string $uri URI for the img being depicted
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_img FOAF Specification - foaf:img
     */

    function addImg($uri)
    {
        if($this->isAllowedForAgent('img')) {
            $this->children['img'][] =& $this->foaf->addChild('foaf:img', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:myersBriggs elements
     *
     * @foafstatus testing
     * @param string $myers_briggs Myers Briggs Personality classification
     * @access public
     * @return boolean
     * @link http://www.teamtechnology.co.uk/tt/t-articl/mb-simpl.htm Myers Briggs - Working out your type
     * @link http://xmlns.com/foaf/0.1/#term_myersBriggs FOAF Specification - foaf:myersBriggs
     */

    function addMyersBriggs($myers_briggs)
    {
        if($this->isAllowedForAgent('myersbriggs')) {
            $this->children['myersbriggs'][] =& $this->foaf->addChild('foaf:myersBriggs', $myers_briggs);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:workplaceHome element
     *
     * @foafstatus testing
     * @param string $uri URI for the Workplace Homepage
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_workplaceHomepage FOAF Specification - foaf:workplaceHomepage
     */

    function addWorkplaceHomepage($uri)
    {
        if ($this->isAllowedForAgent('workplaceHomepage')) {
            $this->children['workplacehomepage'][] =& $this->foaf->addChild('foaf:workplaceHomepage', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:workInfoHomepage element
     *
     * @foafstatus testing
     * @param string $uri URI for Work Information Homepage
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_workInfoHomepage FOAF Specification - foaf:workInfoHomepage
     */

    function addWorkInfoHomepage($uri)
    {
        if($this->isAllowedForAgent('workInfoHomepage')) {
            $this->children['workinfohomepage'][] =& $this->foaf->addChild('foaf:workInfoHomepage', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:schoolHomepage element
     *
     * @foafstatus testing
     * @param string $uri URI for School Homepage
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_schoolHomepage FOAF Specification - foaf:schoolHomepage
     */

    function addSchoolHomepage($uri)
    {
        if($this->isAllowedForAgent('schoolHomepage')) {
            $this->childen['schoolhomepage'][] = $this->foaf->addChild('foaf:schoolHomepage', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:publications elements
     *
     * @foafstatus unstable
     * @param string $uri URI to the publications
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_publications FOAF Specification - foaf:publications
     */

    function addPublications($uri)
    {
        if($this->isAllowedForAgent('publications')) {
            $this->children['publications'][] =& $this->foaf->addChild('foaf:publications', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:currentProject element
     *
     * @foafstatus testing
     * @param string $uri URI to a current projects homepage
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_currentProject FOAF Specification - foaf:currentProject
     */

    function addCurrentProject($uri)
    {
        if($this->isAllowedForAgent('currentProject')) {
            $this->children['currentproject'][] =& $this->foaf->addChild('foaf:currentProject', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add a foaf:pastProject element
     *
     * @foafstatus testing
     * @param string $uri URI to a past projects homepage
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_pastProject FOAF Specification - foaf:pastProject
     */

    function addPastProject($uri)
    {
        if($this->isAllowedForAgent('pastProject')) {
            $this->children['pastproject'][] =& $this->foaf->addChild('foaf:pastProject', '', array('rdf:resource' => $uri));
            return true;
        } else {
            return false;
        }
    }

    /**
     * set foaf:basedNear
     *
     * @foafstatus unstable
     * @param float $geo_lat Latitute for the geo:Point
     * @param float $geo_long Longitude for the geo:Point
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_based_near FOAF Specification - foaf:basedNear
     * @link http://www.w3.org/2003/01/geo/ An RDF Geo Vocabulary: Point/lat/long/alt
     * @link http://esw.w3.org/topic/GeoInfo GeoInfo Wiki
     * @link http://rdfweb.org/topic/UsingBasedNear Using foaf:based_near
     */

    function setBasedNear($geo_lat,$geo_long)
    {
        if($this->isAllowedForAgent('based_near')) {
            $this->namespaces['geo'] = 'http://www.w3.org/2003/01/geo/wgs84_pos#';
            $based_near =& $this->foaf->addChild('foaf:based_near');
            $geo_point =& $based_near->addChild('geo:Point', '', array('geo:lat' => $geo_lat, 'geo:long' => $geo_long));
            $this->children['basednear'][] =& $based_near;
            return true;
        } else {
            return false;
        }
    }

    /* foaf:Person && foaf:Group */

    /**
     * Add a foaf:interest element
     *
     * @foafstatus testing
     * @param string $uri URI with Info about the Interest
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_interest
     */

    function addInterest($uri)
    {
        if($this->isAllowedForAgent('interest')) {
            $this->children['interest'][] =& $this->foaf->addChild('foaf:interest', '', array('rdf:resource' => $uri));
        } else {
            return FALSE;
        }
    }

    /* foaf:Group */

    /**
     * Add a foaf:member element
     *
     * @foafstatus unstable
     * @param object $foaf_agent XML_FOAF object (with a foaf:agent set)
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_member FOAF Specification - foaf:member
     */

    function &addMember(&$foaf_agent)
    {
        if($this->isAllowedForAgent('member')) {
            $member =& $this->foaf->addChild('foaf:member');
            $member->addChild($foaf_agent);
            $this->children['member'][] =& $member;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set foaf:membershipClass
     *
     * @foafstatus unstable
     * @param mixed $membership_class XML String or XML_Tree/XML_Tree_Node object
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_membershipClass FOAF Specification - foaf:membershipClass
     */

    function setMembershipClass(&$membership_class)
    {
        if ($this->isAllowedForAgent('membershipClass')) {
            if (is_string($membership_class)) {
                $membership_tree = new XML_Tree;
                $membership_tree->getTreeFromString($membership_class);
                $this->children['membershipclass'] =& $this->foaf->addChild($membership_tree);
            } else {
                $this->children['membershipclass'] =& $this->foaf->addChild($membership_class);
            }
            return true;
        } else {
            return false;
        }
    }

    /**#@-*/

    /* end of Agent only methods */

    /**
     * set rdfs:seeAlso
     *
     * @param string $uri URI for the resource
     * @access public
     * @return boolean
     * @link http://www.w3.org/TR/rdf-schema/#ch_seealso RDF Schema Specification - rdfs:seeAlso
     */

    function addSeeAlso($uri)
    {
        $this->children['seealso'][] =& $this->foaf->addChild('rdfs:seeAlso', '', array('rdf:resource' => $uri));
    }

    /**
     * set a foaf:knows
     *
     * @foafstatus testing
     * @param object $foaf_agent XML_FOAF Object for the foaf:knows Agent
     * @access public
     * @return boolean
     * @link http://xmlns.com/foaf/0.1/#term_knows FOAF Specification - foaf:knows
     */

    function &addKnows(&$foaf_agent)
    {
        $this->knows =& $this->foaf->addChild('foaf:knows');
        $this->knows->addChild($foaf_agent->foaf);
        return true;
    }

    /**
     * Add an XML_Tree, XML_Tree_Node object or XML String to the FOAF
     *
     * @param mixed $xml_tree XML_Tree, XML_Tree_Node or XML String
     * @access public
     * @return boolean
     */

    function addChild(&$xml_tree)
    {
        if (is_array($xml_tree)) {
            if (is_string($xml_tree['xml'])) {
                $tree = new XML_Tree;
                $tree->getTreeFromString($xml_tree['xml']);
                $xml_tree['child']->addChild($tree);
            } else {
                $xml_tree['child']->addChild($xml_tree['xml']);
            }
        } else {
            if (is_string($xml_tree)) {
                $tree = new XML_Tree;
                $tree->getTreeFromString($xml_tree);
                $this->foaf->addChild($tree);
            } else {
                $this->foaf->addChild($xml_tree);
            }
        }
    }

    /**
     * Echo the FOAF RDF/XML tree
     *
     * @param boolean $without_rdf Ouput RDF/XML inside <rdf:RDF> root elements
     * @access public
     * @return boolean
     */

    function dump($without_rdf = false)
    {
        echo $this->get($without_rdf);
        return true;
    }

    /**
     * Return the FOAF RDF/XML tree
     *
     * @param boolean $without_rdf Return RDF/XML inside <rdf:RDF> root element
     * @access public
     * @return string
     */

    function toXML($without_rdf = false)
    {
        if ($without_rdf == false) {
            $foaf = "<rdf:RDF" .$this->_getXmlns(). ">\n" .$this->foaf->get(). "</rdf:RDF>";
        } else {
            $foaf = $this->foaf->get();
        }
        return $foaf;
    }

    /**
     * Alias for toXML
     *
     * @param boolean $without_rdf Return RDF/XML inside <rdf:RDF> root element
     * @access public
     * @return string
     */

    function get($without_rdf = false)
    {
        return $this->toXML($without_rdf);
    }


    /**
     * Set an XML Namespace
     *
     * @param string $qualifier XML Namespace qualifier
     * @param string $uri XML Namespace URI
     * @access public
     * @return boolean
     */

    function addXmlns($qualifier,$uri)
    {
        $this->namespaces[$qualifier] = $uri;
    }

    /**
     * Return XML Namespaces as xml attributes
     *
     * @access private
     * @return string
     */

    function _getXmlns()
    {
        $namespaces = '';
        foreach ($this->namespaces as $qualifier => $uri) {
            $namespaces .= ' xmlns:' .$qualifier. ' = "' .$uri. '"';
        }
        return $namespaces;
    }

    /**
     * Set default XML Namespaces
     *
     * @access private
     * @return void
     */

    function _setXmlns()
    {
        $this->namespaces['foaf'] = "http://xmlns.com/foaf/0.1/";
        $this->namespaces['rdf'] = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
        $this->namespaces['rdfs'] = "http://www.w3.org/2000/01/rdf-schema#";
        $this->namespaces['dc'] = "http://purl.org/dc/elements/1.1/";
    }
}

/*
foaf:Person
geekcode, firstName, surname, family_name, plan, img, myersBriggs, workplaceHomepage, workInfoHomepage, schoolHomepage, knows, interest, topic_interest, publications, currentProject, pastProject, based_near, name, maker, depiction, fundedBy, logo, page, theme, dnaChecksum, title, nick, givenname, phone, mbox, mbox_sha1sum, gender, jabberID, aimChatID, icqChatID, yahooChatID, msnChatID, homepage, weblog, made, holdsAccount

foaf:Organization
name, maker, depiction, fundedBy, logo, page, theme, dnaChecksum, title, nick, givenname, phone, mbox, mbox_sha1sum, gender, jabberID, aimChatID, icqChatID, yahooChatID, msnChatID, homepage, weblog, made, holdsAccount

foaf:Group
member, membershipClass, name, maker, depiction, fundedBy, logo, page, theme, dnaChecksum, title, nick, givenname, phone, mbox, mbox_sha1sum, gender, jabberID, aimChatID, icqChatID, yahooChatID, msnChatID, homepage, weblog, made, holdsAccount
*/

?>