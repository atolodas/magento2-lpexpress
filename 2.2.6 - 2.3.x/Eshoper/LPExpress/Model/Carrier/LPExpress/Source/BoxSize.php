<?php
/**
 * Box size
 *
 * Used for box_size shipping setting
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;


class BoxSize
{
    /**
     * Determine constant box sizes
     * Return [ id => value ]
     *
     * @return array
     */
    public function toOptionArray ()
    {
        return [
            'XSmall' => 'XSmall',
            'Small' => 'Small',
            'Medium' => 'Medium',
            'XLarge' => 'XLarge'
        ];
    }
}
