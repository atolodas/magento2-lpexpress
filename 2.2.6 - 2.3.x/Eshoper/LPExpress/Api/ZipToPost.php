<?php
/**
 * Zip to post office
 *
 * @package    Eshoper/LPExpress/Api
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Api;


class ZipToPost
{
    /**
     * @var \Eshoper\LPExpress\Helper\ApiHelper
     */
    protected $_apiHelper;

    /**
     * ZipToPost constructor.
     * @param \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
     */
    public function __construct (
        \Eshoper\LPExpress\Helper\ApiHelper $apiHelper
    ) {
        $this->_apiHelper = $apiHelper;
    }

    /**
     * Returns post office from zip code
     *
     * @api
     * @param string $zipcode
     *
     * @return string
     */
    public function getPostOffice ( $zipcode )
    {
        // Reformatted zipcode
        $validatedZip = $this->validate ( $zipcode );
        try {
            $postOffice = $this->_apiHelper->getPostByZip ( $validatedZip );
            return mb_convert_encoding ( $postOffice->address, 'UTF-8' );
        } catch ( \Exception $e ) {
            return  mb_convert_encoding ( __( $e->getMessage () ), 'UTF-8' );
        }
    }

    /**
     * Reformat zipcode
     * @param $zipcode
     *
     * @return string
     */
    private function validate ( $zipcode )
    {
        // Remove spaces around
        $zipcode = trim ( $zipcode );

        // Remove symbols
        $zipcode = preg_replace('/\D/', '', $zipcode );

        // Remove all spaces
        $zipcode = str_replace ( ' ', '', $zipcode );

        return $zipcode;
    }
}
