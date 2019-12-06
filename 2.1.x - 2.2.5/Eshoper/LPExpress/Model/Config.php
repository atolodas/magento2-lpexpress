<?php
/**
 * Main configuration class
 *
 * Used to gather information from module settings
 *
 * @package    Eshoper/LPExpress/Model
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model;


class Config
{
    const CONFIG_PATH_ENABLED           = 'carriers/lpexpress/active';
    const CONFIG_PATH_TEST_MODE         = 'carriers/lpexpress/test_mode';
    const CONFIG_PATH_API_ID            = 'carriers/lpexpress/api_id';
    const CONFIG_PATH_API_KEY           = 'carriers/lpexpress/api_key';
    const CONFIG_PATH_KEPOLUSERID       = 'carriers/lpexpress/kepoluserid';
    const CONFIG_PATH_PAYMENTPIN        = 'carriers/lpexpress/paymentpin';
    const CONFIG_PATH_ADMINPIN          = 'carriers/lpexpress/adminpin';
    const CONFIG_PATH_PARTNER_KEY       = 'carriers/lpexpress/partner_key';
    const CONFIG_PATH_BOXSIZE           = 'carriers/lpexpress/box_size';
    const CONFIG_PATH_TERMINALTYPE      = 'carriers/lpexpress/terminal_type';
    const CONFIG_PATH_HOMETYPE          = 'carriers/lpexpress/home_type';
    const CONFIG_PATH_OVERSEASTYPE      = 'carriers/lpexpress/overseas_type';
    CONST CONFIG_PATH_LABELFORMAT       = 'carriers/lpexpress/label_format';
    const CONFIG_PATH_SENDER_NAME       = 'general/store_information/name';
    const CONFIG_PATH_SENDER_PHONE      = 'general/store_information/phone';
    const CONFIG_PATH_SENDER_EMAIL      = 'trans_email/ident_general/email';
    const CONFIG_PATH_SENDER_ADDRESS    = 'shipping/origin/street_line1';
    const CONFIG_PATH_SENDER_CITY       = 'shipping/origin/city';
    const CONFIG_PATH_SENDER_ZIP        = 'shipping/origin/postcode';
    const CONFIG_PATH_SENDER_COUNTRY    = 'shipping/origin/country_id';

    /**
     * Label Constants
     */
    const LABEL_FORMAT     = 'lfl_a4_1';

    /**
     * Parcel types
     */
    const AB_TYPE = 'AB';
    const EB_TYPE = 'EB';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct (
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_config = $config;
    }

    /**
     * Is module enabled
     * @return mixed
     */
    public function isEnabled ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_ENABLED );
    }

    /**
     * Get is test mode
     * @return mixed
     */
    public function getIsTestMode ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_TEST_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get API ID
     * @return mixed
     */
    public function getApiId ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_API_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get API KEY
     *
     * @return mixed
     */
    public function getApiKey ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get API KEPOLUSERID
     *
     * @return mixed
     */
    public function getApiKepoluserid ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_KEPOLUSERID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get API PAYMENTPIN
     *
     * @return mixed
     */
    public function getApiPaymentpin ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_PAYMENTPIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get API ADMINPIN
     *
     * @return mixed
     */
    public function getApiAdminpin ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_ADMINPIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get PARTNER_KEY
     */
    public function getPartnerKey ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_PARTNER_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_NAME
     *
     * @return mixed
     */
    public function getSenderName ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_PHONE
     *
     * @return mixed
     */
    public function getSenderPhone ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_PHONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_ADDRESS
     *
     * @return mixed
     */
    public function getSenderAddress ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_ADDRESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_CITY
     *
     * @return mixed
     */
    public function getSenderCity ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_COUNTRY
     *
     * @return mixed
     */
    public function getSenderCountry ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_EMAIL
     *
     * @return mixed
     */
    public function getSenderEmail ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get SENDER_ZIP
     *
     * @return mixed
     */
    public function getSenderPostcode ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_SENDER_ZIP,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get TERMINAL_TYPE
     *
     * @return mixed
     */
    public function getTerminalType ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_TERMINALTYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get HOME_TYPE
     *
     * @return mixed
     */
    public function getHomeType ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_HOMETYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get OVERSEASTYPE
     *
     * @return mixed
     */
    public function getOverseasType ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_OVERSEASTYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get LABEL_FORMAT
     *
     * @return mixed
     */
    public function getLabelFormat ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_LABELFORMAT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }

    /**
     * Get BOX_SIZE
     *
     * @return mixed
     */
    public function getDefaultBoxSize ()
    {
        return $this->_config->getValue ( self::CONFIG_PATH_BOXSIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
    }
}
