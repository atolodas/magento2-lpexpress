<?php
/**
 * Tracking resource model
 *
 * @package    Eshoper/LPExpress/Model/ResourceModel
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\ResrouceModel;

class Tracking extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    protected $_descriptionArray;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init ( 'lpexpress_tracking_events', 'id' );

        $this->_descriptionArray = [
            'DA_ACCEPTED_LP' => __('Parcel picked up from post office'),
            'DA_ACCEPTED' => __('Parcel accepted from sender'),
            'DA_DELIVERED_LP' => __('Parcel delivered to post office'),
            'DA_DELIVERED' => __('Parcel delivered'),
            'DA_DELIVERY_FAILED' => __('Parcel delivery failed'),
            'DA_EXPORTED' => __('Parcel exported abroad'),
            'DA_PASSED_FOR_DELIVERY' => __('Parcel passed for delivery'),
            'DA_RETURNED' => __('Parcel returned'),
            'DA_RETURNING' => __('Parcel returning'),
            'DEAD' => __('Parcel passed for destroy'),
            'DEP_RECEIVED' => __('Parcel received in sorting center'),
            'DEP_SENT' => __('Parcel dispatched to another sorting center'),
            'EMA' => __('Parcel accepted from sender'),
            'EMB' => __('Parcel in sorting centre'),
            'EMC' => __('Parcel exported from Lithuania'),
            'EMD' => __('Parcel in recipient’s country'),
            'EME' => __('Parcel at recipient’s country customs'),
            'EMF' => __('Parcel sent to recipient‘s post office'),
            'EMG' => __('Parcel at recipient‘s post office'),
            'EMH' => __('Unsuccessful delivery attempt'),
            'EMI' => __('Parcel delivered to recipient'),
            'FETCHCODE' => __('Parcel delivered to self service terminal'),
            'LABEL_CANCELLED' => __('Label cancelled'),
            'LABEL_CREATED' => __('Label created'),
            'LP_DELIVERY_FAILED' => __('Parcel not picked up by recipient'),
            'LP_RECEIVED' => __('Parcel was received in the post office'),
            'PARCEL_DELIVERED' => __('Parcel delivered to self service terminal'),
            'PARCEL_DROPPED' => __('Parcel dropped at self service terminal for shipment'),
            'PARCEL_PICKED_UP_AT_LP' => __('Parcel picked up by recipient'),
            'PARCEL_PICKED_UP_BY_DELIVERYAGENT' => __('Parcel picked up from self service terminal by courier'),
            'PARCEL_PICKED_UP_BY_RECIPIENT' => __('Parcel picked up by recipient'),
            'PARCEL_LOST' => __('Parcel lost'),
            'PARCEL_DEMAND' => __('Kept till demand'),
            'PARCEL_DETAINED' => __('Shipment is detained'),
            'EXA' => __('Item presented to export Customs/Security'),
            'EXB' => __('Item held by export Customs/Security'),
            'EXC' => __('Item returned from export Customs/Security'),
            'EXD' => __('Item held at outward office of exchange'),
            'EXX' => __('Export cancellation'),
            'EDA' => __('Held at inward office of exchange'),
            'EDB' => __('Item presented to import Customs'),
            'EDC' => __('Item returned from import Customs'),
            'EDD' => __('Item into sorting centre'),
            'EDE' => __('Item out of sorting centre'),
            'EDF' => __('Held at delivery depot/delivery office'),
            'EDG' => __('Item out for physical delivery'),
            'EDH' => __('Item arrival at collection point for pick-up (by recipient)'),
            'EDX' => __('Import terminated')
        ];
    }

    /**
     * Return tracking description
     *
     * @param $key
     * @return mixed
     */
    public function getDescriptionByCode ( $key )
    {
        return $this->_descriptionArray [ $key ] ?? $key;
    }
}
