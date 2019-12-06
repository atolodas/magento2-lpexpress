<?php
/**
 * Main LPExpress API helper
 *
 * Used for API communication
 *
 * @package    Eshoper/LPExpress/Helper
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Helper;

class ApiHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Gateway for testing purposes
     *
     * @var string
     */
    protected $_testGateway = 'apibeta.balticpost.lt';

    /**
     * Gateway for production
     *
     * @var string
     */
    protected $_defaultGateway = 'api.balticpost.lt';

    /**
     * Soap production mode
     *
     * @var string
     */
    protected $_defaultWsdl = 'https://api.balticpost.lt/bpdcws/wsdl/';

    /**
     * Soap sandbox mode
     *
     * @var string
     */
    protected $_testWsdl = 'http://apibeta.balticpost.lt/bpdcws/wsdl/';

    /**
     * Tracking information url
     *
     * @var string
     */
    protected $_trackingUrl = 'http://apibeta.balticpost.lt/download/ttevents/v2?key=';

    /**
     * SoapClient class factory
     *
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $_soapClientFactory;

    /**
     * Soap Connection
     *
     * @var \SoapClient
     */
    protected $_connector;

    /**
     * Get all needed configuration
     *
     * @var \Eshoper\LPExpress\Model\Config
     */
    protected $_config;

    /**
     * ApiHelper constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory,
        \Eshoper\LPExpress\Model\Config $config
    ) {
        $this->_soapClientFactory = $soapClientFactory;
        $this->_config = $config;

        parent::__construct ( $context );
    }

    /**
     * Get API gateway depending on test mode setting
     *
     * @return string
     */
    private function getApiGateway ()
    {
        return $this->_config->getIsTestMode() ? $this->_testGateway : $this->_defaultGateway;
    }

    /**
     * Get API WSDL gateway depending on test mode setting
     *
     * @return string
     */
    private function getWsdlGateway ()
    {
        return $this->_config->getIsTestMode() ? $this->_testWsdl : $this->_defaultWsdl;
    }

    /**
     * Get schema https:// or http:// depend on test enviroment
     *
     * @return string
     */
    private function getSchema ()
    {
        return $this->_config->getIsTestMode () ? 'http://' : 'https://';
    }

    /**
     * Format options
     *
     * @return array
     */
    public function getApiOptions ()
    {
        $options = [
            'hostname' => $this->getApiGateway (),
            'partnerId' => $this->_config->getApiId (),
            'partnerPassword' => $this->_config->getApiKey (),
            'rawOptions' => [
                'trace' => true,
                'connection_timeout' => 5,
                'cache_wsdl' => WSDL_CACHE_DISK
            ],
            'customerID' => $this->_config->getApiKepoluserid (),
            'paymentPin' => $this->_config->getApiPaymentpin (),
            'adminPin'   => $this->_config->getApiAdminpin ()
        ];

        return $options;
    }

    /**
     * Returns the list of Soap headers
     *
     * @return SoapHeader []
     */
    protected function getSoapHeaders()
    {
        $credentials = new \stdClass ();
        $credentials->userid = new \SoapVar ( $this->_config->getApiId (), XSD_STRING );
        $credentials->password = new \SoapVar ( $this->_config->getApiKey (), XSD_STRING );

        $userAuth = new \SoapVar ( $credentials, SOAP_ENC_OBJECT );

        return array(
            new \SoapHeader ( 'bpdcws', 'UserAuth', $userAuth )
        );
    }

    /**
     * Connect to the webservice
     *
     * @return \SoapClient
     * @throws \SoapFault
     */
    public function connect ()
    {
        if ( $this->_connector === null ) {
            $options = [
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            ];

            $options = array_merge ( $this->getApiOptions (), $options );
            $connector = $this->_soapClientFactory->create ( $this->getWsdlGateway (), $options );
            $connector->__setSoapHeaders ( $this->getSoapHeaders () );
            $this->_connector = $connector;
        }

        return $this->_connector;
    }

    /**
     * Call api method with params
     *
     * @param $method
     * @param array $params
     * @return mixed
     * @throws \SoapFault
     */
    public function call ( $method, $params = [] )
    {
        $this->connect ();
        return $this->_connector->__soapCall ( $method, $params );
    }

    /**
     * Get public terminal list
     *
     * @return array
     * @throws \SoapFault
     */
    public function getTerminalList ()
    {
        // Call lp express gateway
        $terminals = [];
        $response  = $this->call ( 'public_terminals' );

        if ( $response ) {
            foreach ( $response as $terminal ) {
                $terminals [ $terminal->machineid ] =
                    $terminal->city . ' ' . $terminal->address . ' - ' . $terminal->name;
            }
        }

        return $terminals;
    }

    /**
     * Get box size and other parameters
     *
     * @param string $product_type
     * @param array $dimensions
     * @return \stdClass | array
     * @throws \SoapFault
     */
    public function getProductByDimensions ( $product_type, $dimensions )
    {
        $params = [
            'product_type' => $product_type,
            'dimension_x' => $dimensions [ 'x' ],
            'dimension_y' => $dimensions [ 'y' ],
            'dimension_z' => $dimensions [ 'z' ]
        ];

        return $this->call ( 'get_product_by_dimensions',
                $params );
    }

    /**
     * Add labels
     *
     * @param string $orderid
     * @param array $labels
     * @return \stdClass | array
     * @throws \SoapFault
     */
    public function addLabels ( $orderid, $labels )
    {
        $params = [
            'parterorderid' => $orderid . '_' . strtotime ( date ( 'Y-m-d H:i:s' ) ),
            'kepoluserid' => $this->_config->getApiKepoluserid(),
            'paymentpin' => $this->_config->getApiPaymentpin(),
            'labels' => $labels
        ];

        return $this->call ( 'add_labels', $params );
    }

    /**
     * Get order labels
     *
     * @param string $orderid - lpexpress order id
     * @return \stdClass | array
     * @throws \SoapFault
     */
    public function confirmLabels ( $orderid )
    {
        $params = [
            'orderid' => $orderid,
            'payment_type' => 'CC'
        ];

        return $this->call ( 'confirm_labels', $params );
    }

    /**
     * Cancel labels
     *
     * @param $labels
     * @return \stdClass | array
     * @throws \SoapFault
     * @return void
     */
    public function cancelLabels ( $labels )
    {
        $params = [
            'labels' => $labels
        ];

        return $this->call ( 'cancel_labels', $params );
    }

    /**
     * Get pdf url
     *
     * @param string $orderpdfid - lpexpress label pdf id (got from confirmLabels)
     * @param string $lfl - label format
     * @return string
     */
    public function getLabelsUri ( $orderpdfid, $lfl = null )
    {
        $uri = $this->getSchema () . $this->getApiGateway () . '/getpdf/label/' . $orderpdfid;

        if ($lfl !== null) {
            $uri .= '/?lfl=' . $lfl;
        }

        return $uri;
    }

    /**
     * Get the manifest url
     *
     * @param $manifestid
     * @return mixed
     */
    public function getManifestUri ( $manifestid )
    {
        $url = $this->getSchema () . $this->getApiGateway() . '/getpdf/manifest/' . $manifestid;

        return $url;
    }

    /**
     * Generate label request
     *
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Framework\DataObject $package
     * @param int $packageCount
     * @param float $parcelWeight
     * @param int $packageIndex
     * @param bool $supportParts
     * @return \stdClass
     * @throws \SoapFault
     */
    public function generateLabel ( $request, $package, $packageCount, $parcelWeight, $packageIndex, $supportParts )
    {


        $label      = new \stdClass ();
        $order      = $request->getOrderShipment ()->getOrder  ();

        // Example 000000013_label_5bdd0b74e9a6c
        $label->partnerorderartid = $request->getOrderShipment ()
                ->getOrder ()
                ->getIncrementId () . '_label_' . uniqid ();

        switch ( $order->getShippingMethod () ) {
            case 'lpexpress_lpexpress_terminal':
                $label->targetmachineidentification =
                    $order->getLpexpressTerminal ();
                $label->productcode = $this->_config->getTerminalType ();
                break;
            case 'lpexpress_lpexpress_courier':
                $label->productcode = $this->_config->getHomeType ();

                if ( $request->getRecipientAddressCountryCode () !== 'LT' ) {
                    $label->productcode = $this->_config->getOverseasType ();
                }
                break;
            case 'lpexpress_lpexpress_post_office':
                $label->productcode = $this->_config::AB_TYPE;
                break;
        }

        // One package size ( convert to millimeters ) - Box size
        if ( $package [ 'params' ][ 'width' ] === 0 || $package [ 'params' ][ 'height' ] == 0
            || $package [ 'params' ][ 'length' ] === 0 ) {
            $label->boxsize = $this->_config->getDefaultBoxSize ();
        } else {
            $boxSizeRequest = $this->getProductByDimensions('CC', [
                    'x' => $package ['params']['width'] * 10,
                    'y' => $package ['params']['height'] * 10,
                    'z' => $package ['params']['length'] * 10
                ]
            );
            $label->boxsize = $boxSizeRequest->boxsize;
        }

        $label->parts           = $packageCount;
        $label->parcelweight    = $parcelWeight;
        
        // Sender address
        $label->sendername              = $this->_config->getSenderName ();
        $label->sendermobile            = $this->_config->getSenderPhone ();
        $label->senderemail             = $this->_config->getSenderEmail ();
        $label->senderaddressfield1     = $this->_config->getSenderAddress ();
        $label->senderaddresscity       = $this->_config->getSenderCity ();
        $label->senderaddresszip        = $this->_config->getSenderPostcode ();
        $label->senderaddresscountry    = $this->_config->getSenderCountry ();

        // Receiver
        $label->receivername            = $request->getRecipientContactPersonName ();
        $label->receivermobile          = $request->getRecipientContactPhoneNumber ();
        $label->receiveremail           = $request->getRecipientEmail ();
        $label->receiveraddressfield1   = $request->getRecipientAddressStreet ();
        $label->receiveraddresscity     = $request->getRecipientAddressCity ();
        $label->receiveraddresszip      = str_replace( 'LT-', '', $request->getRecipientAddressPostalCode () );
        $label->receiveraddresscountry  = $request->getRecipientAddressCountryCode ();

        // Comment
        $commentHistory = $order->getStatusHistoryCollection ();

        if ( ! empty ( $commentHistory ) ) {
            // Add delivery comment as last comment in order
            $comment = $commentHistory->getFirstItem ()->getComment ();
            $label->deliverycomment = $comment;
        }

        // COD
        if ( $order->getPayment ()->getMethod () === 'cashondelivery' ) {
            $label->parcelvalue = $order->getGrandTotal ();

            if ( !$supportParts ) {
                $packagePrice = 0;

                foreach ( $package [ 'items' ] as $item  ) {
                    $packagePrice += $item [ 'price' ] * $item [ 'qty' ];
                }

                // Add shipping cost to the first package
                if ( $packageIndex === 1 ) {
                    $label->parcelvalue = $packagePrice + $order->getShippingAmount();
                } else {
                    $label->parcelvalue = $packagePrice;
                }
            }

            $label->parcelvaluecurrency = $order->getOrderCurrencyCode ();
        }

        return $label;
    }

    /**
     * Get tracking information from API
     * Type: xml
     *
     * @return \stdClass | array
     */
    public function getTrackingEvents ()
    {
        // Initialize with curl
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_URL, $this->_trackingUrl . $this->_config->getPartnerKey() );

        $trackingInfo = curl_exec ( $ch );
        curl_close ( $ch );

        // If returned xml result
        if ( $trackingInfo && $data = simplexml_load_string ( $trackingInfo ) ) {
            return $data;
        }

        return [];
    }

    /**
     * Get post office from zip code API
     *
     * @param string $zipcode
     * @return array
     * @throws \SoapFault
     */
    public function getPostByZip ( $zipcode )
    {
        $params = [
            'zip' => $zipcode
        ];

        return $this->call ( 'zip2postoffice', $params );
    }

    /**
     * Generate manifest
     *
     * @param array $parcels
     * @return \stdClass | array
     * @throws \SoapFault
     */
    public function callCourier ( $parcels )
    {
        $params = [
            'kepoluserid' => $this->_config->getApiKepoluserid (),
            'adminpin' => $this->_config->getApiAdminpin (),
            'parcels' => $parcels
        ];

        return $this->call ( 'call_courier', $params );
    }

    /**
     * Get overseas destinations
     *
     * @return mixed
     * @throws \SoapFault
     */
    public function getOverseasDestinations ()
    {
        return $this->call ( 'overseas_destinations' );
    }
}
