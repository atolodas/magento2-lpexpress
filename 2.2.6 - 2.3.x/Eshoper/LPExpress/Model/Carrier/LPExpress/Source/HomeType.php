<?php
/**
 * Home shipping type
 *
 * Used for home_type shipping setting
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;

class HomeType
{
    /**
     * Return [ id => value ]
     *
     * @return array
     */
    public function toOptionArray ()
    {
        return [
            'EB' => 'EB',
            'CH' => 'CH'
        ];
    }
}
