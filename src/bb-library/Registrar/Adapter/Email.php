<?php
/**
 * BoxBilling
 *
 * @copyright BoxBilling, Inc (http://www.boxbilling.com)
 * @license   Apache-2.0
 *
 * Copyright BoxBilling, Inc
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */

class Registrar_Adapter_Email extends Registrar_AdapterAbstract
{
    protected $config;

    public function __construct($options)
    {
        if (isset($options["email"]) && !empty($options["email"])) {
            $this->config["email"] = $options["email"];
            unset($options["email"]);
        } else {
            throw new Registrar_Exception(
                'Email Registrar config requires param "email"'
            );
        }

        if (isset($options["use_whois"])) {
            $this->config["use_whois"] = (bool) $options["use_whois"];
        } else {
            $this->config["use_whois"] = false;
        }

        $this->config["from"] = $this->config["email"];
    }

    public static function getConfig()
    {
        return [
            "label" =>
                "This registrar type sends notifications to the given email about domain management events. For example, when client registers a new domain an email with domain details will be sent to you. It is then your responsibility to register domain on real registrar.",
            "form" => [
                "email" => [
                    "text",
                    [
                        "label" => "Email address",
                        "description" =>
                            "Email to send domain change notifications",
                    ],
                ],
                "use_whois" => [
                    "radio",
                    [
                        "multiOptions" => ["1" => "Yes", "0" => "No"],
                        "label" => "Use WHOIS to check for domain availability",
                    ],
                ],
            ],
        ];
    }

    public function getTlds()
    {
        return [];
    }

    public function isDomainAvailable(Registrar_Domain $domain)
    {
        $this->getLog()->debug(
            "Checking domain availability: " . $domain->getName()
        );

        if ($this->config["use_whois"]) {
            $w = new Whois2($domain->getName());
            return $w->isAvailable();
        }
        throw new Registrar_Exception(
            "Email registrar can not determine whether domain is available"
        );
    }

    public function isDomainCanBeTransfered(Registrar_Domain $domain)
    {
        throw new Registrar_Exception(
            "Email registrar can not determine whether domain can be transferred"
        );
    }

    public function modifyNs(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Modify Name Servers";
        $params["content"] =
            "A request to change domain nameservers has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function transferDomain(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Transfer domain";
        $params["content"] = "A request to transfer domain has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function getDomainDetails(Registrar_Domain $domain)
    {
        return $domain;
    }

    public function deleteDomain(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Delete domain";
        $params["content"] = "A request to delete domain has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function registerDomain(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Register domain";
        $params["content"] = "A request to register domain has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function renewDomain(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Renew domain";
        $params["content"] = "A request to renew domain has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function modifyContact(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Modify Domain Contact";
        $params["content"] =
            "A request to update domain contacts details has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function enablePrivacyProtection(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Turn On Domain privacy protection";
        $params["content"] =
            "A request to change domain privacy protection has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function disablePrivacyProtection(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Turn Off Domain privacy protection";
        $params["content"] =
            "A request to change domain privacy protection has been received.";

        return $this->sendEmail($domain, $params);
    }

    public function getEpp(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Request for Epp code was received";
        $params["content"] = "A request for Domain Transfer code was received.";

        return $this->sendEmail($domain, $params);
    }

    public function lock(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Request to lock domain received";
        $params["content"] = "A request to lock domain was received.";

        return $this->sendEmail($domain, $params);
    }

    public function unlock(Registrar_Domain $domain)
    {
        $params = [];
        $params["subject"] = "Request to unlock domain received";
        $params["content"] = "A request to unlock domain was received.";

        return $this->sendEmail($domain, $params);
    }

    private function sendEmail(Registrar_Domain $domain, array $params)
    {
        $c = $params["content"];
        $c .= PHP_EOL;
        $c .= PHP_EOL;
        $c .= "Domain should be configured as follows:";
        $c .= PHP_EOL;
        $c .= PHP_EOL;
        $c .= $domain->__toString();

        $log = $this->getLog();
        if ($this->_testMode) {
            $log->alert($params["subject"] . PHP_EOL . PHP_EOL . $c);
            return true;
        }

        mail($this->config["email"], $params["subject"], $c);
        $log->info("Email sent: " . $params["subject"]);
        return true;
    }
}

class Whois2
{
    public $domain = "";
    protected $idn = [
        224,
        225,
        226,
        227,
        228,
        229,
        230,
        231,
        232,
        233,
        234,
        235,
        240,
        236,
        237,
        238,
        239,
        241,
        242,
        243,
        244,
        245,
        246,
        248,
        254,
        249,
        250,
        251,
        252,
        253,
        255,
    ];

    public static function getServers()
    {
        /*******************************
         * Initializing server variables
         * array(top level domain,whois_Server,not_found_string or MAX number of CHARS: MAXCHARS:n)
         **/
        $servers = [
            ["ac", "whois.nic.ac", "Available"],
            ["ac.cn", "whois.cnnic.net.cn", "no matching record"],
            ["ac.jp", "whois.nic.ad.jp", "No match"],
            ["ac.uk", "whois.ja.net", "No such domain"],
            ["ad.jp", "whois.nic.ad.jp", "No match"],
            ["adm.br", "whois.nic.br", "No match"],
            ["adv.br", "whois.nic.br", "No match"],
            ["aero", "whois.information.aero", "is available"],
            ["ag", "whois.nic.ag", "Not found"],
            ["agr.br", "whois.nic.br", "No match"],
            ["ah.cn", "whois.cnnic.net.cn", "No entries found"],
            ["al", "whois.ripe.net", "No entries found"],
            ["am", "whois.amnic.net", "No match"],
            ["am.br", "whois.nic.br", "No match"],
            ["arq.br", "whois.nic.br", "No match"],
            ["at", "whois.nic.at", "nothing found"],
            ["au", "whois.aunic.net", "No Data Found"],
            ["art.br", "whois.nic.br", "No match"],
            ["as", "whois.nic.as", "Domain Not Found"],
            ["asia", "whois.nic.asia", "NOT FOUND"],
            ["asn.au", "whois.aunic.net", "No Data Found"],
            ["ato.br", "whois.nic.br", "No match"],
            ["av.tr", "whois.nic.tr", "Not found in database"],
            ["az", "whois.ripe.net", "no entries found"],
            ["ba", "whois.ripe.net", "No match for"],
            ["be", "whois.geektools.com", "FREE"],
            ["bg", "whois.digsys.bg", "does not exist"],
            ["bio.br", "whois.nic.br", "No match"],
            ["biz", "whois.biz", "Not found"],
            ["biz.tr", "whois.nic.tr", "Not found in database"],
            ["bj.cn", "whois.cnnic.net.cn", "No entries found"],
            ["bel.tr", "whois.nic.tr", "Not found in database"],
            ["bmd.br", "whois.nic.br", "No match"],
            ["br", "whois.registro.br", "No match"],
            ["by", "whois.ripe.net", "no entries found"],
            ["ca", "whois.cira.ca", "Status: AVAIL"],
            ["cc", "whois.nic.cc", "No match"],
            ["cd", "whois.cd", "No match"],
            ["ch", "whois.nic.ch", "We do not have an entry"],
            ["cim.br", "whois.nic.br", "No match"],
            ["ck", "whois.ck-nic.org.ck", "No entries found"],
            ["cl", "whois.nic.cl", "no existe"],
            ["cn", "whois.cnnic.net.cn", "No entries found"],
            ["cng.br", "whois.nic.br", "No match"],
            ["cnt.br", "whois.nic.br", "No match"],
            ["com", "whois.crsnic.net", "No match"],
            ["com.au", "whois.aunic.net", "No Data Found"],
            ["com.br", "whois.nic.br", "No match"],
            ["com.cn", "whois.cnnic.net.cn", "No entries found"],
            ["com.eg", "whois.ripe.net", "No entries found"],
            ["com.hk", "whois.hknic.net.hk", "No Match for"],
            ["com.mx", "whois.nic.mx", "Nombre del Dominio"],
            ["com.tr", "whois.nic.tr", "Not found in database"],
            ["com.ru", "whois.ripn.ru", "No entries found"],
            ["com.tw", "whois.twnic.net", "NO MATCH TIP"],
            ["conf.au", "whois.aunic.net", "No entries found"],
            ["co.at", "whois.nic.at", "nothing found"],
            ["co.jp", "whois.nic.ad.jp", "No match"],
            ["co.uk", "whois.nic.uk", "No match for"],
            ["co.in", "whois.iisc.ernet.in", "No match for"],
            ["co.za", "whois.coza.net.za", "Available"],
            ["cq.cn", "whois.cnnic.net.cn", "No entries found"],
            ["csiro.au", "whois.aunic.net", "No Data Found"],
            ["cx", "whois.nic.cx", "No match"],
            ["cy", "whois.ripe.net", "no entries found"],
            ["cz", "whois.nic.cz", "No data found"],
            ["de", "whois.denic.de", "not found"],
            ["dr.tr", "whois.nic.tr", "Not found in database"],
            ["dk", "whois.dk-hostmaster.dk", "No entries found"],
            ["dz", "whois.ripe.net", "no entries found"],
            ["ecn.br", "whois.nic.br", "No match"],
            ["ee", "whois.eenet.ee", "NOT FOUND"],
            ["edu", "whois.crsnic.net", "No match"],
            ["edu.au", "whois.aunic.net", "No Data Found"],
            ["edu.br", "whois.nic.br", "No match"],
            ["edu.tr", "whois.nic.tr", "Not found in database"],
            ["eg", "whois.ripe.net", "No entries found"],
            ["es", "whois.ripe.net", "No entries found"],
            ["esp.br", "whois.nic.br", "No match"],
            ["etc.br", "whois.nic.br", "No match"],
            ["eti.br", "whois.nic.br", "No match"],
            ["eun.eg", "whois.ripe.net", "No entries found"],
            ["emu.id.au", "whois.aunic.net", "No Data Found"],
            ["eng.br", "whois.nic.br", "No match"],
            ["eu", "whois.eu", "Status: AVAILABLE"],
            ["far.br", "whois.nic.br", "No match"],
            ["fi", "whois.ripe.net", "No entries found"],
            ["fj", "whois.usp.ac.fj", ""],
            ["fj.cn", "whois.cnnic.net.cn", "No entries found"],
            ["fm.br", "whois.nic.br", "No match"],
            ["fnd.br", "whois.nic.br", "No match"],
            ["fo", "whois.ripe.net", "no entries found"],
            ["fot.br", "whois.nic.br", "No match"],
            ["fst.br", "whois.nic.br", "No match"],
            ["fr", "whois.nic.fr", "No entries found"],
            ["gb", "whois.ripe.net", "No match for"],
            ["gb.com", "whois.nomination.net", "No match for"],
            ["gb.net", "whois.nomination.net", "No match for"],
            ["g12.br", "whois.nic.br", "No match"],
            ["gd.cn", "whois.cnnic.net.cn", "No entries found"],
            ["ge", "whois.ripe.net", "no entries found"],
            ["gen.tr", "whois.nic.tr", "Not found in database"],
            ["ggf.br", "whois.nic.br", "No match"],
            ["gl", "whois.ripe.net", "no entries found"],
            ["gr", "whois.ripe.net", "no entries found"],
            ["gr.jp", "whois.nic.ad.jp", "No match"],
            ["gs", "whois.adamsnames.tc", "is not registered"],
            ["gs.cn", "whois.cnnic.net.cn", "No entries found"],
            ["gov.au", "whois.aunic.net", "No Data Found"],
            ["gov.br", "whois.nic.br", "No match"],
            ["gov.cn", "whois.cnnic.net.cn", "No entries found"],
            ["gov.hk", "whois.hknic.net.hk", "No Match for"],
            ["gov.tr", "whois.nic.tr", "Not found in database"],
            ["gob.mx", "whois.nic.mx", "Nombre del Dominio"],
            ["gs", "whois.adamsnames.tc", "is not registered"],
            ["gz.cn", "whois.cnnic.net.cn", "No entries found"],
            ["gx.cn", "whois.cnnic.net.cn", "No entries found"],
            ["he.cn", "whois.cnnic.net.cn", "No entries found"],
            ["ha.cn", "whois.cnnic.net.cn", "No entries found"],
            ["hb.cn", "whois.cnnic.net.cn", "No entries found"],
            ["hi.cn", "whois.cnnic.net.cn", "No entries found"],
            ["hl.cn", "whois.cnnic.net.cn", "No entries found"],
            ["hn.cn", "whois.cnnic.net.cn", "No entries found"],
            ["hm", "whois.registry.hm", "(null)"],
            ["hk", "whois.hknic.net.hk", "No Match for"],
            ["hk.cn", "whois.cnnic.net.cn", "No entries found"],
            ["hu", "whois.ripe.net", "MAXCHARS:500"],
            ["id.au", "whois.aunic.net", "No Data Found"],
            ["ac.id", "whois.magnet-id.com", "No match for domain"],
            ["co.id", "whois.magnet-id.com", "No match for domain"],
            ["net.id", "whois.magnet-id.com", "No match for domain"],
            ["or.id", "whois.magnet-id.com", "No match for domain"],
            ["web.id", "whois.magnet-id.com", "No match for domain"],
            ["sch.id", "whois.magnet-id.com", "No match for domain"],
            ["mil.id", "whois.magnet-id.com", "No match for domain"],
            ["go.id", "whois.magnet-id.com", "No match for domain"],
            ["ie", "whois.domainregistry.ie", "no match"],
            ["ind.br", "whois.nic.br", "No match"],
            ["imb.br", "whois.nic.br", "No match"],
            ["inf.br", "whois.nic.br", "No match"],
            ["info", "whois.afilias.info", "Not found"],
            ["info.au", "whois.aunic.net", "No Data Found"],
            ["info.tr", "whois.nic.tr", "Not found in database"],
            ["it", "whois.nic.it", "No entries found"],
            ["idv.tw", "whois.twnic.net", "NO MATCH TIP"],
            ["in", "whois.inregistry.net", "NOT FOUND"],
            ["int", "whois.iana.org", "not found"],
            ["is", "whois.isnic.is", "No entries found"],
            ["il", "whois.isoc.org.il", "No data was found"],
            ["jl.cn", "whois.cnnic.net.cn", "No entries found"],
            ["jor.br", "whois.nic.br", "No match"],
            ["jp", "whois.nic.ad.jp", "No match"],
            ["js.cn", "whois.cnnic.net.cn", "No entries found"],
            ["jx.cn", "whois.cnnic.net.cn", "No entries found"],
            ["k12.tr", "whois.nic.tr", "Not found in database"],
            ["ke", "whois.rg.net", "No match for"],
            ["kr", "whois.krnic.net", "is not registered"],
            ["la", "whois.nic.la", "NO MATCH"],
            ["lel.br", "whois.nic.br", "No match"],
            ["li", "whois.nic.ch", "We do not have an entry"],
            ["lk", "whois.nic.lk", "No domain registered"],
            ["ln.cn", "whois.cnnic.net.cn", "No entries found"],
            ["lt", "whois.domreg.lt", "Status: available"],
            ["lu", "whois.dns.lu", "No entries found"],
            ["lv", "whois.ripe.net", "no entries found"],
            ["ltd.uk", "whois.nic.uk", "No match for"],
            ["ma", "whois.ripe.net", "No entries found"],
            ["mat.br", "whois.nic.br", "No match"],
            ["mc", "whois.ripe.net", "No entries found"],
            ["md", "whois.ripe.net", "No match for"],
            ["me.uk", "whois.nic.uk", "No match for"],
            ["med.br", "whois.nic.br", "No match"],
            ["mil", "whois.nic.mil", "No match"],
            ["mil.br", "whois.nic.br", "No match"],
            ["mil.tr", "whois.nic.tr", "Not found in database"],
            ["mk", "whois.ripe.net", "No match for"],
            ["mn", "whois.nic.mn", "Domain not found"],
            ["mobi", "whois.dotmobiregistry.net", "NOT FOUND"],
            ["mo.cn", "whois.cnnic.net.cn", "No entries found"],
            ["ms", "whois.adamsnames.tc", "is not registered"],
            ["mt", "whois.ripe.net", "No Entries found"],
            ["mus.br", "whois.nic.br", "No match"],
            ["mx", "whois.nic.mx", "Nombre del Dominio"],
            ["name", "whois.nic.name", "No match"],
            ["name.tr", "whois.nic.tr", "Not found in database"],
            ["ne.jp", "whois.nic.ad.jp", "No match"],
            ["net", "whois.crsnic.net", "No match"],
            ["net.au", "whois.aunic.net", "No Data Found"],
            ["net.br", "whois.nic.br", "No match"],
            ["net.cn", "whois.cnnic.net.cn", "No entries found"],
            ["net.eg", "whois.ripe.net", "No entries found"],
            ["net.hk", "whois.hknic.net.hk", "No Match for"],
            ["net.lu", "whois.dns.lu", "No entries found"],
            ["net.mx", "whois.nic.mx", "Nombre del Dominio"],
            ["net.uk", "whois.nic.uk", "No match for "],
            ["net.ru", "whois.ripn.ru", "No entries found"],
            ["net.tr", "whois.nic.tr", "Not found in database"],
            ["net.tw", "whois.twnic.net", "NO MATCH TIP"],
            ["nl", "whois.domain-registry.nl", "is free"],
            ["nm.cn", "whois.cnnic.net.cn", "No entries found"],
            ["no", "whois.norid.no", "no matches"],
            ["no.com", "whois.nomination.net", "No match for"],
            ["nom.br", "whois.nic.br", "No match"],
            ["not.br", "whois.nic.br", "No match"],
            ["ntr.br", "whois.nic.br", "No match"],
            ["nu", "whois.nic.nu", "NO MATCH for"],
            ["nx.cn", "whois.cnnic.net.cn", "No entries found"],
            ["nz", "whois.domainz.net.nz", "Not Listed"],
            ["plc.uk", "whois.nic.uk", "No match for"],
            ["odo.br", "whois.nic.br", "No match"],
            ["oop.br", "whois.nic.br", "No match"],
            ["or.jp", "whois.nic.ad.jp", "No match"],
            ["or.at", "whois.nic.at", "nothing found"],
            ["org", "whois.pir.org", "NOT FOUND"],
            ["org.au", "whois.aunic.net", "No Data Found"],
            ["org.br", "whois.nic.br", "No match"],
            ["org.cn", "whois.cnnic.net.cn", "No entries found"],
            ["org.hk", "whois.hknic.net.hk", "No Match for"],
            ["org.lu", "whois.dns.lu", "No entries found"],
            ["org.ru", "whois.ripn.ru", "No entries found"],
            ["org.tr", "whois.nic.tr", "Not found in database"],
            ["org.tw", "whois.twnic.net", "NO MATCH TIP"],
            ["org.uk", "whois.nic.uk", "No match for"],
            ["pk", "whois.pknic.net", "is not registered"],
            ["pl", "whois.ripe.net", "No information about"],
            ["pol.tr", "whois.nic.tr", "Not found in database"],
            ["pp.ru", "whois.ripn.ru", "No entries found"],
            ["ppg.br", "whois.nic.br", "No match"],
            ["pro.br", "whois.nic.br", "No match"],
            ["psi.br", "whois.nic.br", "No match"],
            ["psc.br", "whois.nic.br", "No match"],
            ["pt", "whois.ripe.net", "No match for"],
            ["qh.cn", "whois.cnnic.net.cn", "No entries found"],
            ["qsl.br", "whois.nic.br", "No match"],
            ["rec.br", "whois.nic.br", "No match"],
            ["ro", "whois.ripe.net", "No entries found"],
            ["ru", "whois.ripn.ru", "No entries found"],
            ["sc.cn", "whois.cnnic.net.cn", "No entries found"],
            ["sd.cn", "whois.cnnic.net.cn", "No entries found"],
            ["se", "whois.nic-se.se", "No data found"],
            ["se.com", "whois.nomination.net", "No match for"],
            ["se.net", "whois.nomination.net", "No match for"],
            ["sg", "whois.nic.net.sg", "NO entry found"],
            ["sh", "whois.nic.sh", "No match for"],
            ["sh.cn", "whois.cnnic.net.cn", "No entries found"],
            ["si", "whois.arnes.si", "No entries found"],
            ["sk", "whois.ripe.net", "no entries found"],
            ["slg.br", "whois.nic.br", "No match"],
            ["sm", "whois.ripe.net", "no entries found"],
            ["sn.cn", "whois.cnnic.net.cn", "No entries found"],
            ["srv.br", "whois.nic.br", "No match"],
            ["st", "whois.nic.st", "No entries found"],
            ["su", "whois.ripe.net", "No entries found"],
            ["sx.cn", "whois.cnnic.net.cn", "No entries found"],
            ["tc", "whois.adamsnames.tc", "is not registered"],
            ["tel", "whois.nic.tel", "Not found:"],
            ["tel.tr", "whois.nic.tr", "Not found in database"],
            ["th", "whois.nic.uk", "No entries found"],
            ["tj.cn", "whois.cnnic.net.cn", "No entries found"],
            ["tm", "whois.nic.tm", "No match for"],
            ["tn", "whois.ripe.net", "No entries found"],
            ["tmp.br", "whois.nic.br", "No match"],
            ["to", "whois.tonic.to", "No match"],
            ["trd.br", "whois.nic.br", "No match"],
            ["tur.br", "whois.nic.br", "No match"],
            ["tv", "whois.nic.tv", "No match for "],
            ["tv.br", "whois.nic.br", "No match"],
            ["tw", "whois.twnic.net", "NO MATCH TIP"],
            ["tw.cn", "whois.cnnic.net.cn", "No entries found"],
            ["ua", "whois.ripe.net", "No entries found"],
            ["uk", "whois.thnic.net", "No match for"],
            ["uk.com", "whois.nomination.net", "No match for"],
            ["uk.net", "whois.nomination.net", "No match for"],
            ["us", "whois.nic.us", "Not found"],
            ["va", "whois.ripe.net", "No entries found"],
            ["vet.br", "whois.nic.br", "No match"],
            ["vg", "whois.adamsnames.tc", "is not registered"],
            ["wattle.id.au", "whois.aunic.net", "No Data Found"],
            ["web.tr", "whois.nic.tr", "Not found in database"],
            ["ws", "whois.worldsite.ws", "No match for"],
            ["xj.cn", "whois.cnnic.net.cn", "No entries found"],
            ["xz.cn", "whois.cnnic.net.cn", "No entries found"],
            ["yn.cn", "whois.cnnic.net.cn", "No entries found"],
            ["yu", "whois.ripe.net", "No entries found"],
            ["za", "whois.frd.ac.za", "No match for"],
            ["zlg.br", "whois.nic.br", "No match"],
            ["zj.cn", "whois.cnnic.net.cn", "No entries found"],
        ];

        return $servers;
    }

    /**
     * Constructor of class domain
     * @param string	$str_domainname    the full name of the domain
     * @desc Constructor of class domain
     */
    public function __construct($str_domainname)
    {
        $this->domain = $str_domainname;
    }

    /**
     * Returns the whois data of the domain
     * @return string $whoisdata Whois data as string
     * @desc Returns the whois data of the domain
     */
    public function info()
    {
        $tldname = $this->get_tld();
        $domainname = $this->get_domain();
        $whois_server = $this->get_whois_server();

        // If tldname have not been found
        if ($whois_server == "") {
            throw new Box_Exception("No whois server for this tld in list!");
        }

        // Getting whois information
        $fp = @fsockopen($whois_server, 43);

        if (!$fp) {
            throw new Box_Exception(
                "Whois server " . $whois_server . " is not available"
            );
        }

        $dom = $domainname . "." . $tldname;

        // New IDN
        if ($tldname == "de") {
            fputs($fp, "-C ISO-8859-1 -T dn $dom\r\n");
        } else {
            fputs($fp, "$dom\r\n");
        }

        // Getting string
        $string = "";

        // Checking whois server for .com and .net
        if ($tldname == "com" || $tldname == "net" || $tldname == "edu") {
            while (!feof($fp)) {
                $line = trim(fgets($fp, 128));

                $string .= $line;

                $lineArr = explode(":", $line);

                if (strtolower($lineArr[0]) == "whois server") {
                    $whois_server = trim($lineArr[1]);
                }
            }
            // Getting whois information
            $fp = fsockopen($whois_server, 43);
            if (!is_resource($fp)) {
                throw new \Box_Exception(
                    "Could not connect to whois :server server",
                    [":server" => $whois_server]
                );
            }

            $dom = $domainname . "." . $tldname;
            fputs($fp, "$dom\r\n");

            // Getting string
            $string = "";

            while (!feof($fp)) {
                $string .= fgets($fp, 128);
            }

            // Checking for other tld's
        } else {
            while (!feof($fp)) {
                $string .= fgets($fp, 128);
            }
        }
        fclose($fp);
        return $string;
    }

    /**
     * Returns name of the whois server of the tld
     * @return string $server the whois servers hostname
     * @desc Returns name of the whois server of the tld
     */
    private function get_whois_server()
    {
        $found = false;
        $tldname = $this->get_tld();
        $servers = self::getServers();
        $counted = count($servers);
        for ($i = 0; $i < $counted; $i++) {
            if ($servers[$i][0] == $tldname) {
                $server = $servers[$i][1];
                $found = true;
            }
        }

        if (!$found) {
            throw new Exception(
                sprintf("Whois server for TLD %s not found", $tldname)
            );
        }
        return $server;
    }

    /**
     * Returns the tld of the domain without domain name
     * @return string $tldname the TLDs name without domain name
     * @desc Returns the tld of the domain without domain name
     */
    private function get_tld()
    {
        // Splitting domainname
        $domain = explode(".", $this->domain);

        if (count($domain) > 2) {
            $domainname = $domain[0];
            $counted = count($domain);
            for ($i = 1; $i < $counted; $i++) {
                if ($i == 1) {
                    $tldname = $domain[$i];
                } else {
                    $tldname .= "." . $domain[$i];
                }
            }
        } else {
            $domainname = $domain[0];
            $tldname = $domain[1];
        }
        return $tldname;
    }

    /**
     * Returns all TLDs which are supported by the class
     * @return string $tlds all TLDs as array
     * @desc Returns all TLDs which are supported by the class
     */
    public static function getTlds()
    {
        $tlds = "";
        $servers = self::getServers();
        $counted = count($servers);
        for ($i = 0; $i < $counted; $i++) {
            $tlds[$i] = $servers[$i][0];
        }
        return $tlds;
    }

    /**
     * Returns the name of the domain without tld
     * @return string $domain the domains name without tld name
     * @desc Returns the name of the domain without tld
     */
    private function get_domain()
    {
        // Splitting domainname
        $domain = explode(".", $this->domain);
        return $domain[0];
    }

    /**
     * Returns the string which will be returned by the whois server of the tld if a domain is avalable
     * @return string $notfound  the string which will be returned by the whois server of the tld if a domain is avalable
     * @desc Returns the string which will be returned by the whois server of the tld if a domain is avalable
     */
    private function get_notfound_string()
    {
        $found = false;
        $tldname = $this->get_tld();
        $servers = self::getServers();
        $counted = count($servers);
        for ($i = 0; $i < $counted; $i++) {
            if ($servers[$i][0] == $tldname) {
                $notfound = $servers[$i][2];
            }
        }
        return $notfound;
    }

    /**
     * Returns if the domain is available for registering
     * @return boolean $is_available Returns 1 if domain is available and 0 if domain isn't available
     * @desc Returns if the domain is available for registering
     */
    public function isAvailable()
    {
        $whois_string = $this->info(); // Gets the entire WHOIS query from registrar
        $not_found_string = $this->get_notfound_string(); // Gets 3rd item from array
        $domain = $this->domain; // Gets current domain being queried

        $whois_string2 = @preg_replace("/$domain/", "", $whois_string);

        $whois_string = @preg_replace("/\s+/", " ", $whois_string); //Replace whitespace with single space

        $array = explode(":", $not_found_string);

        if ($array[0] == "MAXCHARS") {
            if (strlen($whois_string2) <= $array[1]) {
                return true;
            } else {
                return false;
            }
        } else {
            if (preg_match("/" . $not_found_string . "/i", $whois_string)) {
                return true;
            } else {
                return false;
            }
        }
    }
}
