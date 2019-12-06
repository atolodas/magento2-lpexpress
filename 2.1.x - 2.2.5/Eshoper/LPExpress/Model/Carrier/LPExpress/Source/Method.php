<?php
/**
 * LPExpress shipping methods
 *
 * @package    Eshoper/LPExpress/Model/Carrier/LPExpress/Source
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Carrier\LPExpress\Source;

class Method
{
    /**
     * Carrier model to get available methods
     *
     * @var \Eshoper\LPExpress\Model\Carrier\LPExpress
     */
    protected $_carrierModel;

    /**
     * Method constructor.
     *
     * @param \Eshoper\LPExpress\Model\Carrier\LPExpress $carrierModel
     */
    public function __construct(
        \Eshoper\LPExpress\Model\Carrier\LPExpress $carrierModel
    ) {
        $this->_carrierModel = $carrierModel;
    }

    /**
     * All methods for LP Express
     *
     * @return array
     */
    public function toOptionArray ()
    {
        return $this->_carrierModel->getAllMethods();
    }
}
