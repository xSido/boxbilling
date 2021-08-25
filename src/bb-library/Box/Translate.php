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


class Box_Translate implements \Box\InjectionAwareInterface
{
    /**
     * @var \Box_Di
     */
    protected $di = null;

    protected $domain = 'default';

    protected $locale = 'en_US';

    protected $tr = null;

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return Box_Translate
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param Box_Di $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * @return Box_Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function _i18n()
    {
        $i = new Laminas\I18n\Translator\Translator();

        $i->addTranslationFilePattern("PhpArray", BB_PATH_LANGS, "%s.php", "default");
        $i->setLocale($locale);

        $i->enableEventManager();

        // Attach listener
        $i->getEventManager()->attach(
            Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION,
            static function (Laminas\EventManager\EventInterface $event) {
                var_dump($event->getName());
                // 'missingTranslation' (Laminas\I18n\Translator\Translator::EVENT_MISSING_TRANSLATION)
                var_dump($event->getParams());
                // ['message' => 'car', 'locale' => 'de_DE', 'text_domain' => 'default']
            }
        );

        return $i;
    }

    public function setup()
    {
        if (!function_exists('__')) {
            function __($msgid, array $values = NULL)
            {
                if (empty($msgid)) return null;
                
                $tra = new Box_Translate();
                $locale = $tra->getLocale();
                $str = $tra->_i18n()->translate($msgid, "default", $locale);
                
                return empty($values) ? $str : strtr($str, $values);
            }
        }

    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $domain
     * @return Box_Translate
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    public function __($msgid, array $values = NULL)
    {
        return __($msgid, $values);
    }
}