<?php
/* vim: get expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
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
 * FOAF Parser
 * @package XML_FOAF
 * @category XML
 */

require_once 'XML/FOAF/Common.php';

define('RDFAPI_INCLUDE_DIR', 'XML/FOAF/RAP/');
define('XML_FOAF_NS','http://xmlns.com/foaf/0.1/');
define('XML_FOAF_DC_NS','http://purl.org/dc/elements/1.1/');
define('XML_FOAF_RDF_NS','http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('XML_FOAF_RDF_SCHEMA_NS', 'http://www.w3.org/2000/01/rdf-schema#');
define('XML_FOAF_PERSON', 1);
define('XML_FOAF_GROUP', 2);
define('XML_FOAF_ORGANIZATION', 3);
define('XML_FOAF_AGENT', 4);

/**
 * FOAF Parser
 *
 * Individual element parsers that start with _fetch will return multiple elements
 * into the result Array, those that start with _get will return only a single element
 *
 * @package XML_FOAF
 * @author Davey <davey@php.net>
 * @version 0.1
 * @copyright Copyright 2003 Davey Shafik and Synaptic Media. All Rights Reserved.
 * @example docs/examples/example2.php Basic Usage of XML_FOAF_Parser
 * @todo Implement PEAR_Error handling
 */

class XML_FOAF_Parser extends XML_FOAF_Common
{
    /**
     * @var string Original FOAF file
     */

    var $foaf_xml = '';

    /**
     * @var array FOAF data as Array
     */

    var $foaf_data;

    /**
     * @var object MemModel of FOAF
     */

    var $foaf;

    /**
     * @var object Instance of the RAP RDF_Parser
     */

    var $rdf_parser;

    /**
     * @var array Nodes assumed to be primary foaf:Agents
     */

    var $agent_nodes = array();

    /**
     * @var array Nodes found in <foaf:knows>
     */

    var $known_nodes = array();

    /**
     * XML_FOAF_Parser Constructor
     */

    function __construct() {
        require_once RDFAPI_INCLUDE_DIR . 'RdfAPI.php';
        $this->rdf_parser =& new RdfParser;
    }

    /**
     * XML_FOAF_Parser PHP4 Compatible Constructor
     */

    function XML_FOAF_Parser()
    {
        $this->__construct();
    }

    /**
     * Parse a FOAF at the specified URI
     *
     * @param $uri string URI for a FOAF file
     * @access public
     * @return void
     */

    function parseFromURI($uri)
    {
        $this->parseFromFile($uri);
    }

    /**
     * Parse a FOAF in the specified File
     *
     * @param $file string File Path for a FOAF file
     * @param $use_include_path Whether to look for the file in the php include_path
     * @access public
     * @return void
     */

    function parseFromFile($file,$use_include_path = false)
    {
        $this->foaf_xml = file_get_contents($file,$use_include_path);
        $this->_parse();
    }

    /**
     * Parse a FOAF contained in the specified variable
     *
     * @param $mem string Variable holding a FOAF file's XML
     * @access public
     * @return void
     */

    function parseFromMem($mem)
    {
        $this->foaf_xml = $mem;
        $this->_parse();
    }

    /**#@+
     * @access private
     * @return void
     */

    /**
     * Calls all the seperate property parsers
     */

    function _parse()
    {
        $this->foaf =& $this->rdf_parser->generateModel($this->foaf_xml);
        $this->foaf_data = $this->_fetchAgent();
        $this->_fetchAimChatID();
        $this->_fetchCurrentProject();
        $this->_fetchDcDescription();
        $this->_fetchDcTitle();
        $this->_fetchDepiction();
        $this->_fetchFundedBy();
        $this->_fetchHoldsAccount();
        $this->_fetchHomepage();
        $this->_fetchIcqChatID();
        $this->_fetchImg();
        $this->_fetchInterest();
        $this->_fetchJabberID();
        $this->_fetchLogo();
        $this->_fetchMade();
        $this->_fetchMbox();
        $this->_fetchMboxSha1Sum();
        $this->_fetchMember();
        $this->_fetchMsnChatID();
        $this->_fetchMyersBriggs();
        $this->_fetchNick();
        $this->_fetchPage();
        $this->_fetchPastProject();
        $this->_fetchPhone();
        $this->_fetchPublication();
        $this->_fetchSchoolHomepage();
        $this->_fetchSeeAlso();
        $this->_fetchTheme();
        $this->_fetchWeblog();
        $this->_fetchWorkInfoHomepage();
        $this->_fetchWorkplaceHomepage();
        $this->_fetchYahooChatID();
        $this->_getBasedNear();
        $this->_getFamilyName();
        $this->_getFirstName();
        $this->_getGeekcode();
        $this->_getGender();
        $this->_getGivenName();
        $this->_getMembershipClass();
        $this->_getName();
        $this->_getPlan();
        $this->_getSurname();
        $this->_getTitle();
    }

    /**
     * Parses our the foaf:Agents
     *
     * Looks for all foaf:Agents (foaf:Person,foaf:Group,foaf:Organzation and foaf:Agent)
     * and decides which are the primary agents (who/what the FOAF is about) and
     * which are only known by the primary agents
     *
     * @access private
     * @return void
     */

    function _fetchAgent()
    {
        $person_resource = new Resource(XML_FOAF_NS . 'Person');
        $persons = $this->foaf->find(null,null,$person_resource);
        $group_resource = new Resource(XML_FOAF_NS . 'Group');
        $groups = $this->foaf->find(null,null,$group_resource);
        $organization_resource = new Resource(XML_FOAF_NS . 'Organization');
        $organizations = $this->foaf->find(null,null,$organization_resource);
        $agent_resource = new Resource(XML_FOAF_NS . 'Agent');
        $agents = $this->foaf->find(null,null,$agent_resource);
        $agents->addModel($persons);
        $agents->addModel($groups);
        $agents->addModel($organizations);
        $knows_resource = new Resource(XML_FOAF_NS . 'knows');
        $knows = $this->foaf->find(null,$knows_resource,null);
        $i = 0;
        $agent_nodes = array();
        $known_nodes = array();
        foreach ($agents->triples as $agent) {
            $agent_nodes[$agent->subj->uri] = $agent->obj->uri;
            $i += 1;
            foreach ($knows->triples as $know) {
                if ($agent->subj->uri == $know->obj->uri) {
                    $agent_type = pathinfo($agent->obj->uri);
                    $agent_type = $agent_type['basename'];
                    $known = array('node' => $know->obj->uri, 'type' => $agent_type);
                    $known_nodes["{$know->subj->uri}"][] = $known;
                    $this->known_nodes[] = $know->obj->uri;
                    unset($agent_nodes[$agent->subj->uri]);
                }
            }
        }

        $agents = array();
        $i = 0;
        foreach ($agent_nodes as $node => $agent_type) {
            $agent_type = pathinfo($agent_type);
            $agent_type = $agent_type['basename'];
            $agents[$i] = array ('node' => $node, 'type' => $agent_type);
            $this->agent_nodes[] = $node;
            if (isset($known_nodes[$node])) {
                foreach ($known_nodes[$node] as $knows) {
                    $agents[$i]['knows'][] = $knows;
                }
            }
            $i += 1;
        }
        if (!is_array($this->known_nodes)) {
            $this->known_nodes = array();
        }
        return $agents;
    }

    /**
     * Finds the foaf:name's and inserts them into the result array
     *
     * If more than one foaf:name is found for a single foaf:Agent, the
     * last found will be the one shown in the result
     */

    function _getName()
    {
        $this->_getProperty(XML_FOAF_NS, 'name', 'label');
    }

    /**
     * Finds all foaf:depiction's and inserts them into the result Array
     */

    function _fetchDepiction()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'depiction', 'uri');
    }

    /**
     * Finds all foaf:fundedBy's and inserts them into the result Array
     */

    function _fetchFundedBy()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'fundedBy', 'uri');
    }

    /**
     * Finds all foaf:logo's and inserts them into the result Array
     */

    function _fetchLogo()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'logo', 'uri');
    }

    /**
     * Finds all foaf:page's and inserts them into the result Array
     */

    function _fetchPage()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'page', 'uri');
    }

    /**
     * Finds all foaf:theme's and inserts them into the result Array
     */

    function _fetchTheme()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'theme', 'uri');
    }

    /**
     * Finds all the foaf:title and inserts them into the result Array
     *
     * If more than one foaf:title is found for one foaf:Agent the
     * last one found is insert into the result
     */

    function _getTitle()
    {
        $this->_getProperty(XML_FOAF_NS, 'title', 'label');
    }

    /**
     * Finds all foaf:nick's and inserts them into the result Array
     */

    function _fetchNick()
    {
        $nick_resource = new Resource(XML_FOAF_NS . 'nick');
        $nicks = $this->foaf->find(null,$nick_resource,null);
        foreach ($nicks->triples as $nick) {
            if (in_array($nick->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $nick->subj->uri) {
                        $this->foaf_data[$key]['nick'][] = $nick->obj->label;
                    }
                    break;
                }
            } elseif (in_array($nick->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    if (isset($agent['knows']) && is_array($agent['knows'])) {
                        foreach ($agent['knows'] as $nick_key => $nick_array) {
                            if (isset($nick_array['node']) && ($nick_array['node'] == $nick->subj->uri)) {
                                $this->foaf_data[$agent_key]['knows'][$nick_key]['nick'][] = $nick->obj->label;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:givenName's and inserts them into the result Array
     *
     * If more than one foaf:givenName is found for a single foaf:Agent, the
     * last one found is inserted into the result array
     */

    function _getGivenName()
    {
        $this->_getProperty(XML_FOAF_NS, 'givenName', 'label');
    }

    /**
     * Finds all foaf:phone's and inserts them into the result Array
     */

    function _fetchPhone()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'phone', 'uri');
    }

    /**
     * Finds all foaf:mbox's and inserts them into the result Array
     */

    function _fetchMbox()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'mbox', 'uri');
    }

    /**
     * Finds all foaf:mbox_sha1sum's and inserts them into the result Array
     */

    function _fetchMboxSha1Sum()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'mbox_sha1sum', 'label');
    }

    /**
     * Finds all foaf:gender's and inserts them into the result Array
     *
     * If more than one foaf:gender is found for a single foaf:Agent, the
     * last found is inserted into the result Array.
     */

    function _getGender()
    {
        $this->_getProperty(XML_FOAF_NS, 'gender', 'label');
    }

    /**
     * Finds all foaf:jabberID's and inserts them into the result Array
     */

    function _fetchJabberID()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'jabberID', 'label');
    }

    /**
     * Finds all foaf:aimChatID's and inserts them into the result Array
     */

    function _fetchAimChatID()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'aimChatID', 'label');
    }

    /**
     * Finds all foaf:icqChatID's and inserts them into the result Array
     */

    function _fetchIcqChatID()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'icqChatID', 'label');
    }

    /**
     * Finds all foaf:yahooChatID's and inserts them into the result Array
     */

    function _fetchYahooChatID()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'yahooChatID', 'label');
    }

    /**
     * Finds all foaf:msnChatID's and inserts them into the result Array
     */

    function _fetchMsnChatID()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'msnChatID', 'label');
    }

    /**
     * Finds all foaf:onlineAccount's and inserts them into the result Array
     */

    function _fetchHoldsAccount()
    {
        $holds_account_resource = new Resource(XML_FOAF_NS . 'holdsAccount');
        $holds_accounts = $this->foaf->find(null,$holds_account_resource,null);
        $account_name_resource = new Resource(XML_FOAF_NS . 'accountName');
        $account_names = $this->foaf->find(null,$account_name_resource,null);
        $account_service_homepage_resource = new Resource(XML_FOAF_NS . 'accountServiceHomepage');
        $account_service_homepages = $this->foaf->find(null,$account_service_homepage_resource,null);
        $rdf_type_resource = new Resource(XML_FOAF_RDF_NS . 'type');
        $rdf_types = $this->foaf->find(null,$rdf_type_resource,null);
        foreach ($holds_accounts->triples as $holds_account) {
            foreach ($account_names->triples as $account_name) {
                if ($account_name->subj->uri == $holds_account->obj->uri) {
                    $accounts[$account_name->subj->uri]['accountname'] = $account_name->obj->label;
                }
            }
            foreach ($account_service_homepages->triples as $account_service_homepage) {
                if ($account_service_homepage->subj->uri == $holds_account->obj->uri) {
                    $accounts[$account_service_homepage->subj->uri]['accountservicehompage'] = $account_service_homepage->obj->uri;
                }
            }
            foreach ($rdf_types->triples as $rdf_type) {
                if ($rdf_type->subj->uri == $holds_account->obj->uri) {
                    $account_type = pathinfo($rdf_type->obj->uri);
                    $accounts[$rdf_type->subj->uri]['type'] = $account_type['basename'];
                }
            }
        }

        $online_account_resource = new Resource(XML_FOAF_NS . 'OnlineAccount');
        $online_accounts = $this->foaf->find(null,null,$online_account_resource);
        $online_chat_account_resource = new Resource(XML_FOAF_NS .'OnlineChatAccount');
        $online_chat_accounts = $this->foaf->find(null,null,$online_chat_account_resource);
        $online_gaming_account_resource = new Resource(XML_FOAF_NS . 'OnlineGamingAccount');
        $online_gaming_accounts = $this->foaf->find(null,null,$online_gaming_account_resource);
        $online_ecommerce_account_resource = new Resource(XML_FOAF_NS . 'OnlineEcommerceAccount');
        $online_ecommerce_accounts = $this->foaf->find(null,null,$online_ecommerce_account_resource);

        foreach ($online_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineAccount';
            }
        }

        foreach ($online_chat_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineChatAccount';
            }
        }

        foreach ($online_gaming_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineGamingAccount';
            }
        }

        foreach ($online_ecommerce_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineEcommerceAccount';
            }
        }

        foreach ($holds_accounts->triples as $holds_account) {
            $agent_accounts[$holds_account->subj->uri][] = $accounts[$holds_account->obj->uri];
        }

        if (isset($agent_accounts)) {
            foreach ($agent_accounts as $node => $accounts) {
                if (in_array($node,$this->agent_nodes)) {
                    foreach ($this->foaf_data as $key => $value) {
                        if ($value['node'] == $node) {
                            $this->foaf_data[$key]['holdsaccount'] = $agent_accounts[$node];
                        }
                        break;
                    }
                } elseif (in_array($node,$this->known_nodes)) {
                    foreach ($this->foaf_data as $agent_key => $agent) {
                        foreach ($agent['knows'] as $holds_account_key => $holds_account_array) {
                            if (isset($holds_account_array['node']) && ($holds_account_array['node'] == $node)) {
                                $this->foaf_data[$agent_key]['knows'][$holds_account_key]['holdsaccount'] = $agent_accounts[$node];
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:homepage's and inserts them into the result Array
     */

    function _fetchHomepage()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'homepage', 'uri');
    }

    /**
     * Finds all foaf:weblog's and inserts them into the result Array
     */

    function _fetchWeblog()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'weblog', 'uri');
    }

    /**
     * Finds all foaf:made's and inserts them into the result Array
     */

    function _fetchMade()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'made', 'uri');
    }

    /* foaf:Person */

    /**
     * Finds all foaf:geekcode's and inserts them into the result Array
     *
     * If more than one foaf:geekcode is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getGeekcode()
    {
        $this->_getProperty(XML_FOAF_NS, 'geekcode', 'label');
    }

    /**
     * Finds all foaf:firstName's and inserts them into the result Array
     *
     * If more than one foaf:firstName is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getFirstName()
    {
        $this->_getProperty(XML_FOAF_NS, 'firstName', 'label');
    }

    /**
     * Finds all foaf:surname's and inserts them into the result Array
     *
     * If more than one foaf:surname is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getSurname()
    {
        $this->_getProperty(XML_FOAF_NS, 'surname', 'label');
    }

    /**
     * Finds all foaf:familyName's and inserts them into the result Array
     *
     * If more than one foaf:familyName is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getFamilyName()
    {
        $this->_getProperty(XML_FOAF_NS, 'familyName', 'label');
    }

    /**
     * Finds all foaf:plan's and inserts them into the result Array
     *
     * If more than one foaf:plan is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getPlan()
    {
        $this->_getProperty(XML_FOAF_NS, 'plan', 'label');
    }

    /**
     * Finds all foaf:img's and inserts them into the result Array
     */

    function _fetchImg()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'img', 'uri');
    }

    /**
     * Finds all foaf:myersBriggs's and inserts them into the result Array
     */

    function _fetchMyersBriggs()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'myersBriggs', 'label');
    }

    /**
     * Finds all foaf:workplaceHompage's and inserts them into the result Array
     */

    function _fetchWorkplaceHomepage()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'workplaceHomepage', 'uri');
    }

    /**
     * Finds all foaf:workInfoHomepage's and inserts them into the result Array
     */

    function _fetchWorkInfoHomepage()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'workInfoHomepage', 'uri');
    }

    /**
     * Finds all foaf:schoolHomepage's and inserts them into the result Array
     */

    function _fetchSchoolHomepage()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'schoolHomepage', 'uri');
    }

    /**
     * Finds all foaf:publication's and inserts them into the result Array
     */

    function _fetchPublication()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'publication', 'uri');
    }

    /**
     * Finds all foaf:currentProject's and inserts them into the result Array
     */

    function _fetchCurrentProject()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'currentProject', 'uri');
    }

    /**
     * Finds all foaf:pastProject's and inserts them into the result Array
     */

    function _fetchPastProject()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'pastProject', 'uri');
    }

    /**
     * Finds all foaf:basedNear's and inserts them into the result Array
     */

    function _getBasedNear()
    {

    }

    /* foaf:Person && foaf:Group */

    /**
     * Finds all foaf:interest's and inserts them into the result Array
     */

    function _fetchInterest()
    {
        $this->_fetchProperty(XML_FOAF_NS, 'interest', 'uri');
    }

    /* foaf:Group */

    /**
     * Finds all foaf:member's and inserts them into the result Array
     *
     * @todo Need to figure out how to point to an agent in the foaf_data :)
     */

    function _fetchMember()
    {

    }

    /**
     * Finds all foaf:membershipClass's and inserts them into the result Array
     *
     * If more than one foaf:plan is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     *
     * @todo Use http://xmlns.com/foaf/0.1/#term_Group for reference (second example)
     * @todo figure out how to point to an agent in the foaf_data
     */

    function _getMembershipClass()
    {

    }

    /* end of Agent only methods */

    /**
     * Finds all rdf:seeAlso's and inserts them into the result Array
     */

    function _fetchSeeAlso()
    {
        $this->_fetchProperty(XML_FOAF_RDF_SCHEMA_NS, 'seeAlso', 'uri');
    }

    /**
     * Finds all dc:title's and inserts them into the result Array
     *
     * These are inserted at $result['dc']['title'][$uri] where $uri is the URI
     * they are for. You will need to check this for titles and descriptions upon output
     * for any element you want them for.
     */

    function _fetchDcTitle()
    {
        $dc_title_resource = new Resource(XML_FOAF_DC_NS . 'title');
        $dc_titles = $this->foaf->find(null,$dc_title_resource,null);
        foreach ($dc_titles->triples as $title) {
            $this->foaf_data['dc']['title'][$title->subj->uri] = $title->obj->label;
        }
    }


    /**
     * Finds all dc:description's and inserts them into the result Array
     *
     * These are inserted at $result['dc']['description'][$uri] where $uri is the URI
     * they are for. You will need to check this for titles and descriptions upon output
     * for any element you want them for.
     */

    function _fetchDcDescription()
    {
        $dc_description_resource = new Resource(XML_FOAF_DC_NS . 'description');
        $dc_descriptions = $this->foaf->find(null,$dc_description_resource,null);
        foreach ($dc_descriptions->triples as $description) {
            $this->foaf_data['dc']['description'][$description->subj->uri] = $description->obj->label;
        }
    }

    /**#@-*/

    /**
     * Fetch a FOAF Property with multiple values
     *
     * @param $xmlns string XML Namespace URI
     * @param $property string XML Element name
     * @param $obj_value string Triple's "Object" value (label or uri typically)
     * @access private
     * @return void
     */

    function _fetchProperty($xmlns,$property,$obj_value)
    {
        $obj_value = strtolower($obj_value);
        $property_resource = new Resource($xmlns . $property);
        $properties = $this->foaf->find(null,$property_resource,null);
        foreach ($properties->triples as $triple) {
            if (in_array($triple->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $node_uri => $node_data) {
                    if ($node_data == $triple->subj->uri) {
                        $property = strtolower(str_replace('_','',$property));
                        $this->foaf_data[$node_uri][$property][] = $triple->obj->{$obj_value};
                    }
                    break;
                }
            } elseif (in_array($triple->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    if (isset($agent['knows']) && is_array($agent['knows'])) {
                        foreach ($agent['knows'] as $node_uri => $node_data) {
                            if (isset($node_data['node']) && ($node_data['node'] == $triple->subj->uri)) {
                                $property = strtolower(str_replace('_','',$property));
                                $this->foaf_data[$agent_key]['knows'][$node_uri][$property][] = $triple->obj->{$obj_value};
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Fetch a FOAF Property with a single value
     *
     * @param $xmlns string XML Namespace URI
     * @param $property string XML Element name
     * @param $obj_value string Triple's "Object" value (label or uri typically)
     * @access private
     * @return void
     */

    function _getProperty($xmlns,$property,$obj_value)
    {
        $obj_value = strtolower($obj_value);
        $property_resource = new Resource($xmlns . $property);
        $properties = $this->foaf->find(null,$property_resource,null);
        foreach ($properties->triples as $triple) {
            if (in_array($triple->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $node_uri => $node_data) {
                    if ($node_data == $triple->subj->uri) {
                        $property = strtolower(str_replace('_','',$property));
                        $this->foaf_data[$node_uri][$property] = $triple->obj->{$obj_value};
                    }
                    break;
                }
            } elseif (in_array($triple->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    if (isset($agent['knows']) && is_array($agent['knows'])) {
                        foreach ($agent['knows'] as $node_uri => $node_data) {
                            if (isset($node_data['node']) && ($node_data['node'] == $triple->subj->uri)) {
                                $property = strtolower(str_replace('_','',$property));
                                $this->foaf_data[$agent_key]['knows'][$node_uri][$property] = $triple->obj->{$obj_value};
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Return parsed FOAF data as an Object
     *
     * @todo Make it work!
     * @access public
     * @return object
     */

    function toObject()
    {
        $foaf_object = new stdClass();
        foreach ($this->foaf_data as $key=>$value) {
            $foaf_object->$key = $value;
        }
        return $foaf_object;
    }

    /**
     * Return parsed FOAF data as an Array
     *
     * @access public
     * @return array
     */

    function toArray()
    {
        return $this->foaf_data;
    }

    /**
     * Return parsed FOAF data pretty HTML
     *
     * @todo Write code to return an HTML table
     * @access public
     * @return string
     */

    function toHTML(&$foaf_data)
    {
        require_once 'Validate.php';
        $table = '<table>';
        if (!is_array ($foaf_data)) {
            $foaf_data = array();
        }
        foreach ($foaf_data as $key => $agent) {
            if (isset ($agent['type'])) {
                $table .= '<tr><th colspan="2" class="xml_foaf_' .strtolower($agent['type']). '><h1 class="xml_foaf">';
                if (isset($agent['name'])) {
                    $table .= $agent['name'];
                } else {
                    $name = NULL;
                    if (isset($agent['firstname'])) {
                        $name .= $agent['firstname'];
                    } elseif (isset($agent['givenname'])) {
                        $name .= $agent['givenname'];
                    }
                    if (isset($agent['surname'])) {
                        $name .= ' ' .$agent['surname'];
                    } elseif (isset($agent['familyname'])) {
                        $name .= ' ' .$agent['familyname'];
                    }
                    if (is_null($name) && isset($agent['nick'])) {
                        $name = $agent['nick'][0];
                    }
                    if (is_null($name)) {
                        $name = $agent['node'];
                    }
                    $table .= $name;
                }
                $table .= '</h1></th></tr>';
                unset($agent['node']);
                foreach ($agent as $key => $property) {
                    $table .= '<tr><th>' .$key. '</th><td>';
                    if (!is_array($property)) {
                        if (Validate::uri($property,array('allowed_schemes' => array('http','ftp')))) {
                            $table .= '<a href="' .$property. '">' .$property. '</a></td></tr>';
                        } else {
                            $table .= $property. '</td></tr>';
                        }
                    } else {
                        if ($key == 'knows') {
                            $table .= $this->toHTML($property);
                        } elseif ($key != 'holdsaccount') {
                            $table .= '<ul>';
                            foreach ($property as $child) {
                                if (Validate::uri($child,array('allowed_schemes' => array('http','ftp')))) {
                                    $table .= '<li>';
                                    if (isset($this->foaf_data['dc']['title'][$child])) {
                                        $table .= '<h2 class="xml_foaf"><a href="' .$child. '">';
                                        $table .= $this->foaf_data['dc']['title'][$child];
                                        $table .= '</a></h2>';
                                    } else {
                                        $table .= '<a href="' .$child. '">' .$child. '</a>';
                                    }
                                    if (isset($this->foaf_data['dc']['description'][$child])) {
                                        $table .= '<p class="xml_foaf">' .$this->foaf_data['dc']['description'][$child]. '</p>';
                                    }
                                    $table .= '</li>';
                                } else {
                                    $table .= '<li>' .$child. '</li>';
                                }
                            }
                            $table .= '</ul>';
                        } else {
                            foreach ($property as $account) {
                                $table .= '<table>';
                                foreach ($account as $key => $data) {
                                    $table .= '<tr><th>' .$key. '</th><td>' .$data. '</td></tr>';
                                }
                                $table .= '</table>';
                            }
                        }
                    }
                }
            }
        }
        $table .= '</table>';
        return $table;
    }
}

?>