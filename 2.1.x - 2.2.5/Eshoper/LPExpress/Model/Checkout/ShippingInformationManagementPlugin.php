<?php
/**
 * Checkout information processor plugin
 *
 * Used for adding selected terminal to quote
 *
 * @package    Eshoper/LPExpress/Model/Checkout
 * @author     MB "Eshoper" <pagalba@noriusvetaines.lt>
 * @version    1.0.1
 * @since      File available since Release 1.0.1
 */
namespace Eshoper\LPExpress\Model\Checkout;

class ShippingInformationManagementPlugin
{
    /**
     * Quote
     *
     * @var \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    protected $quoteRepository;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Process before checkout shipping info save
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes ();
        $selectedTerminal = $extAttributes->getLpexpressTerminal ();
        $postOffice = $extAttributes->getLpexpressPostOffice ();

        $quote = $this->quoteRepository->getActive ( $cartId );

        if ( $addressInformation->getShippingMethodCode() ==
            'lpexpress_terminal' && $selectedTerminal == null ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __( 'Please select LP Express terminal.' )
            );
        }
        
        if ( $addressInformation->getShippingMethodCode() ==
            'lpexpress_post_office' ) {
            if ( $postOffice == __( 'Invalid post code' ) || $postOffice == __( 'Post code does not exist' ) || $postOffice == __( 'No post office found.' ) ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __( 'Please input a valid postcode.' )
                );
            }
        }

        $quote->setLpexpressTerminal ( $selectedTerminal );
        $quote->setLpexpressPostOffice ( $postOffice );
    }
}
