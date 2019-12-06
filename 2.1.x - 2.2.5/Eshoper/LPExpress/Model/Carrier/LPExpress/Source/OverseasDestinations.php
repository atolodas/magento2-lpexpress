<?php
/**
 * Available shipping countries
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;


class OverseasDestinations
{
    /**
     * @var \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination\Collection
     */
    protected $_overseasDestCollection;

    /**
     * @var \Eshoper\LPExpress\Model\Config
     */
    protected $_config;

    /**
     * OverseasDestinations constructor.
     * @param \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination\Collection $overseasDestCollection
     * @param \Eshoper\LPExpress\Model\Config $config
     */
    public function __construct (
        \Eshoper\LPExpress\Model\ResrouceModel\OverseasDestination\Collection $overseasDestCollection,
        \Eshoper\LPExpress\Model\Config $config
    ) {
        $this->_overseasDestCollection = $overseasDestCollection;
        $this->_config = $config;
    }

    /**
     * @return array
     */
    public function toOptionArray ()
    {
        $destinations = [];

        // If parcels can be delivered to terminal then filter the collection
        if ( $this->_config->getOverseasType () === 'CA' ) {
            $collection = $this->_overseasDestCollection
                ->addFieldToFilter ( 'terminal', true )
                ->getItems ();
        } else {
            $collection = $this->_overseasDestCollection->getItems ();
        }

        // Push lithuania
        array_push ( $destinations,
            [ 'label' => 'Lithuania', 'value' => 'LT' ]
        );

        foreach ( $collection as $item ) {
            array_push ( $destinations,
                [ 'label' => $item->getCountryLabel (), 'value' => $item->getCountryId () ]
            );
        }

        return $destinations;
    }
}
