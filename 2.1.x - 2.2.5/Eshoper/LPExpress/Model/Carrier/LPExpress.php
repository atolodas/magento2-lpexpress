<?php
namespace Eshoper\LPExpress\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Ups\Helper\Config;
use Magento\Framework\Xml\Security;


class LPExpress extends AbstractCarrierOnline implements CarrierInterface
{
/**
     * Terminal weight limit
     */
    const TERMINAL_WEIGHT_LIMIT = 30;

    /**
     * Post office weight limit
     */
    const POST_OFFICE_WEIGHT_LIMIT = 10;

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'lpexpress';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * Available methods
     *
     * @var array
     */
    protected $_methods;

    /**
     * Api helper
     *
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * Array for shippment request, so no duplicates
     *
     * @var array
     */
    protected $_labels;

    /**
     * Message manager from error and notice messages
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Custom tracking information resource collection
     *
     * @var \Eshoper\LPExpress\Model\ResrouceModel\Tracking\Collection
     */
    protected $_trackCollection;

    /**
     * Custom tracking information resource model
     *
     * @var \Eshoper\LPExpress\Model\ResrouceModel\Tracking
     */
    protected $_trackingResource;

    /**
     * Custom rates collection
     *
     * @var \Eshoper\LPExpress\Model\ResrouceModel\Rates\Collection
     */
    protected $_rates;

    /**
     * @var \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination
     */
    protected $_overseasDestination;


    /**
     * @var \Eshoper\LPExpress\Model\ResrouceModel\OverseasRates
     */
    protected $_overseasRates;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper,
        \Eshoper\LPExpress\Model\ResrouceModel\Tracking\Collection $trackCollection,
        \Eshoper\LPExpress\Model\ResrouceModel\Tracking $trackingResource,
        \Eshoper\LPExpress\Model\ResrouceModel\Rates $rates,
        \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination $overseasDestination,
        \Eshoper\LPExpress\Model\ResrouceModel\OverseasRates $overseasRates,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );

        $this->_apiHelper = $apiHelper;
        $this->_messageManager = $messageManager;
        $this->_trackCollection = $trackCollection;
        $this->_trackingResource = $trackingResource;
        $this->_rates = $rates;
        $this->_overseasDestination = $overseasDestination;
        $this->_overseasRates = $overseasRates;

        // Set available methods
        $this->_methods = [
            [ 'label' => 'To Nearest Post Office', 'value' => 'post_office' ],
            [ 'label' => 'To Terminal', 'value' => 'terminal' ],
            [ 'label' => 'Courier', 'value' => 'courier' ]
        ];

        $this->_labels = [];
    }

    /**
     * Pass all methods to sourceModel
     *
     * @return array
     */
    public function getAllMethods ()
    {
        return [
            [ 'label' => __( 'To Nearest Post Office' ), 'value' => 'post_office' ],
            [ 'label' => __( 'To Terminal' ), 'value' => 'terminal' ],
            [ 'label' => __( 'Courier' ), 'value' => 'courier' ]
        ];
    }

    public function getAllowedMethods()
    {
        // get allowed methods setting
        $allowedMethods = $this->getConfigData ( 'allowedmethods' );

        // filter methods to get the right structure
        return array_filter ( $this->_methods, function ( $method ) use ( $allowedMethods ) {
            return in_array ( $method [ 'value' ], explode ( ',', $allowedMethods ) );
        });
    }

    /**
     * Get total weight of items in cart
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return integer
    */
    public function getQuoteWeight ( \Magento\Quote\Model\Quote\Address\RateRequest $request )
    {
        $weight = 0;
        if ( $items = $this->getAllItems ( $request ) ) {
            foreach ( $items as $item ) {
                $weight += ( $item->getWeight () * $item->getQty () );
            }
        }

        return $weight;
    }

    /**
     * Get shipping rate by method
     *
     * @param array $allowedMethod
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return mixed
     */
    protected function _getShippingRate ( $allowedMethod, $request )
    {
        $method = $this->_rateMethodFactory->create ();

        // Set carrier's method data
        $method->setCarrier ( $this->getCarrierCode () );
        $method->setCarrierTitle( $this->getConfigData ( 'name' ) );

        // Displayed as shipping method under Carrier
        $method->setMethod ( $this->getCarrierCode () . '_' . $allowedMethod [ 'value' ] );
        $method->setMethodTitle ( __( $allowedMethod [ 'label' ] )  );

        // Restrict terminal from foreign countries
        if ( $allowedMethod [ 'value' ] === 'terminal' && $request->getDestCountryId () !== 'LT' ) {
            return false;
        }

        // Restrict post office from foreign countries
        if ( $allowedMethod [ 'value' ] === 'post_office' && $request->getDestCountryId () !== 'LT' ) {
            return false;
        }

        // Restrict courier from not allowed countries
        if ( $allowedMethod [ 'value' ] === 'courier' &&
            !$this->_overseasDestination->isAvailable ( $request->getDestCountryId () ) &&
            $request->getDestCountryId () !== 'LT' ) {
            return false;
        }

        // If using static prices
        if ( $this->getConfigData ( $allowedMethod [ 'value' ] . '_price' ) !== null ) {
            if ( $request->getDestCountryId () === 'LT' ) {
                $method->setPrice ( $this->getConfigData ($allowedMethod [ 'value' ] . '_price' ) );
                $method->setCost ( $this->getConfigData ($allowedMethod [ 'value' ] . '_price' ) );
            }
        } else {  // If using shipping rates table
            if ( $request->getDestCountryId () === 'LT' ) {
                $rate = $this->_rates->getRate($this->getQuoteWeight($request),
                    $allowedMethod ['value']);
                if ($rate != 0 && $rate != -1) {
                    $method->setPrice($rate);
                    $method->setCost($rate);
                } else {
                    return false;
                }
            }
        }

        // Overseas table rates
        if ( $this->getConfigData ( 'overseas_price' ) === null && $allowedMethod [ 'value' ] === 'courier' ) {
            if ( $request->getDestCountryId () !== 'LT' ) {
                $overseasRate = $this->_overseasRates->getRate ( $request->getDestCountryId () );
                if ( $overseasRate === null ) {
                    return false;
                }
                $method->setPrice ( $overseasRate );
                $method->setCost ( $overseasRate );
            }
        }

        // Overseas static price to courier
        if ( $this->getConfigData ( 'overseas_price' ) !== null && $allowedMethod [ 'value' ] === 'courier' ) {
            if ( $request->getDestCountryId () !== 'LT' ) {
                $method->setPrice($this->getConfigData('overseas_price'));
                $method->setCost($this->getConfigData('overseas_price'));
            }
        }

        return $method;
    }
    
    /**
     * Collect and get rates
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     * @api
     */
    public function collectRates( \Magento\Quote\Model\Quote\Address\RateRequest $request )
    {
        if ( ! $this->isActive() ) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateFactory->create ();

        foreach ( $this->getAllowedMethods () as $method ) {
            $result->append ( $this->_getShippingRate ( $method, $request ) );
        }

        return $result;
    }

    /**
     * Check if parcel can support parts
     *
     * @param $request
     * @return bool
     */
    protected function canSupportParts ( $request )
    {
        if ( $request->getRecipientAddressCountryCode () !== 'LT' ) {
            // Parts are only available for EB and IN type in Latvia
            if ( $request->getRecipientAddressCountryCode () !== 'LV' ) {
                return false;
            } else {
                if ( $this->getConfigData ( 'overseas_type' ) !== 'IN' ) {
                    return false;
                }
            }
        } else {
            // Parts are only available for EB and IN type
            $shippingMethod = $request->getOrderShipment()->getOrder()->getShippingMethod();

            if ( $shippingMethod === 'lpexpress_lpexpress_post_office' || $shippingMethod === 'lpexpress_lpexpress_terminal' ) {
                return false;
            }

            if ( $shippingMethod === 'lpexpress_lpexpress_courier' && $this->getConfigData ( 'home_type' ) !== 'EB' ) {
                return false;
            }
        }

        return true;
    }

/**
     * Do shipment request to carrier web service,
     * obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \SoapFault
     */
    protected function _doShipmentRequest( \Magento\Framework\DataObject $request )
    {
        // Add labels pdf and tracking number
        $result = new \Magento\Framework\DataObject ();

        if ( empty ( $this->_labels ) ) {
            // Create labels from packages
            $parcelWeight = 0;
            $packageCount = count ( $request->getPackages () );

            $packageParams [ 'params' ][ 'width' ]          = 0;
            $packageParams [ 'params' ][ 'height' ]         = 0;
            $packageParams [ 'params' ][ 'length' ]         = 0;
            $packageParams [ 'params' ][ 'customs_value' ]  = 0;

            if ( $supportParts = $this->canSupportParts ( $request ) ) {
                foreach ( $request->getPackages () as $package ) {
                    // Limits for terminal and post_office if at least one package has reached weight limit
                    if ( $request->getOrderShipment ()->getOrder ()->getShippingMethod ()
                        === 'lpexpress_lpexpress_post_office' &&
                        $package [ 'params' ][ 'weight' ] > self::POST_OFFICE_WEIGHT_LIMIT ) {
                        throw new \Magento\Framework\Exception\LocalizedException (
                            __( 'Package weight is too large. Limit is $1 $2',
                                self::POST_OFFICE_WEIGHT_LIMIT, 'kg' )
                        );
                    }

                    if ( $request->getOrderShipment ()->getOrder ()->getShippingMethod ()
                        === 'lpexpress_lpexpress_terminal' &&
                        $package [ 'params' ][ 'weight' ] > self::TERMINAL_WEIGHT_LIMIT ) {
                        throw new \Magento\Framework\Exception\LocalizedException (
                            __( 'Package weight is too large. Limit is $1 $2',
                                self::TERMINAL_WEIGHT_LIMIT, 'kg' )
                        );
                    }

                    if ( is_numeric ( $package [ 'params' ][ 'width' ] )
                        && is_numeric ( $package [ 'params' ][ 'height' ] )
                        && is_numeric ( $package [ 'params' ][ 'length' ] ) ) {
                        $packageParams [ 'params' ][ 'width' ] += $package [ 'params' ][ 'width' ];
                        $packageParams [ 'params' ][ 'height' ] += $package [ 'params' ][ 'height' ];
                        $packageParams [ 'params' ][ 'length' ] += $package [ 'params' ][ 'length' ];
                    }

                    $parcelWeight += $package [ 'params' ][ 'weight' ];
                }

                $this->_labels [] = $this->_apiHelper->generateLabel(
                    $request, $packageParams, $packageCount, $parcelWeight, 1, $supportParts
                );
            } else {
                // For COD purposes give packageIndex
                $packageIndex = 1;

                // Create labels from packages
                foreach ( $request->getPackages () as $package ) {
                    $this->_labels [] = $this->_apiHelper->generateLabel (
                        $request, $package, 1, $package [ 'params' ][ 'weight' ], $packageIndex++, $supportParts
                    );
                }
            }

            try {
                // Call api to validate labels
                $requestLabels = $this->_apiHelper->addLabels (
                    $request->getOrderShipment ()->getOrder ()->getIncrementId (),
                    $this->_labels
                );

                // Confirm labels to get pdf information
                if ( $requestLabels !== null && property_exists ( $requestLabels, 'orderid' ) ) {
                    $confirmLabels = $this->_apiHelper->confirmLabels ( $requestLabels->orderid );
                }

                if ( $confirmLabels !== null && property_exists ( $confirmLabels, 'orderpdfid' ) ) {
                    // Label 10x15 is available only in these countries
                    $labelFormat = in_array ( $request->getRecipientAddressCountryCode (), [ 'LT', 'LV', 'EE' ] ) ?
                        $this->getConfigData ( 'label_format' ) : 'lfl_a4_1';

                    $result->setShippingLabelContent (
                        file_get_contents ( $this->_apiHelper->getLabelsUri (
                            $confirmLabels->orderpdfid,
                            $labelFormat
                        ))
                    );
                    $result->setTrackingNumber ( $confirmLabels->labels [0]->identcode );
                }
            } catch ( \Exception $e ) {
                $this->_messageManager->addErrorMessage ( $e->getMessage () );
            }
        }

        return $result;
    }

    /**
     * Tracking information
     *
     * @param $trackings
     * @return mixed
     */
    public function getTracking ( $trackings )
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create ();
        $tracking = $this->_trackStatusFactory->create ();

        foreach ( $trackings as $trackingCode ) {
            $tracking->setTracking( $trackingCode );
            $trackingInfo = $this->_trackCollection->getItemsByColumnValue (
                'identcode', $trackingCode
            );

            $packageEvents = [];

            foreach ( $trackingInfo as $event ) {
                // Subtract 3 hours because magento converts to UTC time
                $time = new \DateTime ( explode ( ' ', $event->getTime () )[1] );
                $time->modify ( '-3 hours' );

                array_push ( $packageEvents, [
                    'deliverydate' => explode ( ' ', $event->getTime () )[0],
                    'deliverytime' => $time->format ( 'H:i:s' ),
                    'activity' => $this->_trackingResource->getDescriptionByCode ( $event->getEvent () )
                ] );
            }

            $resultArr = [];
            $resultArr ['carrier_title'] = 'LP Express';

            if ( ! empty ( $trackingInfo ) ) {
                $resultArr ['status'] = $this->_trackingResource
                    ->getDescriptionByCode(end($trackingInfo)->getEvent());
                $resultArr ['progressdetail'] = $packageEvents;
            }

            $tracking->addData($resultArr);
            $result->append($tracking);
        }

        return $result;
    }

     /**
     * Enable shipping labels
     * @return bool
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }
    
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request) {
        return true;
    }
}