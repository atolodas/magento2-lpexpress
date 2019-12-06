<?php
/**
 * Overseas shipping type
 *
 * Used for label_format shipping setting
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;


class OverseasType
{
    /**
     * @return array
     */
    public function toOptionArray ()
    {
        return [
            'CA' => 'CA',
            'IN' => 'IN'
        ];
    }
}
