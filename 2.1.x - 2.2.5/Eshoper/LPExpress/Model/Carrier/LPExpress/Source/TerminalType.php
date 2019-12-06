<?php
/**
 * Terminal type
 *
 * Used for terminal_type shipping setting
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;

class TerminalType
{
    /**
     * Return [ id => value ]
     *
     * @return array
     */
    public function toOptionArray ()
    {
        return [
            'HC' => 'HC',
            'CC' => 'CC'
        ];
    }
}
