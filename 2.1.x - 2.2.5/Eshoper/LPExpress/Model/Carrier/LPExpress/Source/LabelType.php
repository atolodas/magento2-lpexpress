<?php
/**
 * Label shipping type
 *
 * Used for label_format shipping setting
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;

class LabelType
{
    /**
     * Return [ id => value ]
     *
     * @return array
     */
    public function toOptionArray ()
    {
        return [
            'lfl_a4_1' => 'A4',
            'lfl_a4_3' => 'A4 fit labels',
            'lfl_10x15' => '10x15cm'
        ];
    }
}
