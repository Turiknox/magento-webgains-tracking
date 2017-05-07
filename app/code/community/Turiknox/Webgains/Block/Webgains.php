<?php
/*
 * Turiknox_Webgains

 * @category   Turiknox
 * @package    Turiknox_Webgains
 * @copyright  Copyright (c) 2017 Turiknox
 * @license    https://github.com/Turiknox/magento-webgains-tracking/blob/master/LICENSE.md
 * @version    1.0.0
 */
class Turiknox_Webgains_Block_Webgains extends Mage_Checkout_Block_Onepage_Success
{
    /**
     * Order object
     *
     * @Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Webgains Program ID
     */
    const XML_PATH_WG_PROGRAM_ID = 'webgains/general/program_id';

    /**
     * Webgains Event ID
     */
    const XML_PATH_WG_EVENT_ID = 'webgains/general/event_id';

    /**
     * Webgains Version
     */
    const WG_VERSION = '1.2';

    /**
     * Get Webgains Program ID
     *
     * @return int
     */
    public function getWebgainsProgramId()
    {
        return Mage::getStoreConfig(self::XML_PATH_WG_PROGRAM_ID);
    }

    /**
     * Get Webgains Event ID
     *
     * @return int
     */
    public function getWebgainsEventId()
    {
        return Mage::getStoreConfig(self::XML_PATH_WG_EVENT_ID);
    }

    /**
     * Get Webgains Version
     *
     * @return string
     */
    public function getWebgainsVersion()
    {
        return self::WG_VERSION;
    }

    /**
     * Set order
     *
     * @return Mage_Sales_Model_Order
     */
    public function setOrder()
    {
        return $this->_order = Mage::getModel('sales/order')->loadByIncrementId(
            Mage::getSingleton('checkout/session')->getLastRealOrderId()
        );
    }

    /**
     * Get order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            return $this->setOrder();
        }
        return $this->_order;
    }

    /**
     * Get the order ID
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();

    }

    /**
     * Get the order total
     */
    public function getGrandTotal()
    {
        return number_format($this->_order->getGrandTotal(), 2, '.' , '');
    }

    /**
     * Get the shipping amount
     */
    public function getShippingAmount()
    {
        return number_format($this->_order->getShippingInclTax(), 2, '.' , '');
    }

    /**
     * Get the order currency code.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_order->getOrderCurrencyCode();
    }

    /**
     * Get all visible items from order
     *
     * @return array
     */
    public function getVisibleOrderItems()
    {
        return $this->_order->getAllVisibleItems();
    }

    /**
     * Get coupon code from order
     *
     * @return string
     */
    public function getCouponCode()
    {
        return $this->_order->getCouponCode();
    }

    /**
     * Get item string data
     *
     * @return string
     */
    public function getWebGainsItemData()
    {
        $shippingAmount = $this->getShippingAmount();
        $shippingRatio = $shippingAmount / count($items = $this->getVisibleOrderItems());
        $wgItems = '';
        $itemCount = 0;

        foreach ($items as $item) {
            $itemTotal = 0;

            if ($item->getQtyOrdered() > 1) {
                $shippingAmountPerProduct = $shippingRatio / $item->getQtyOrdered();
                for ($i = 1; $i <= $item->getQtyOrdered(); $i++) {
                    $itemTotal = 0;

                    // Event ID
                    $wgItems .= $this->getWebgainsEventId() . '::';

                    // Item Price
                    $itemTotal += $item->getPriceInclTax() - $item->getDiscountAmount()/$item->getQtyOrdered();
                    $itemTotal += $shippingAmountPerProduct;
                    $wgItems .= number_format($itemTotal, 2) . '::';

                    // Name
                    $wgItems .= $item->getName() . '::';

                    // SKU
                    $wgItems .= $item->getSku() . '::';

                    // Coupon code
                    if ($item->getDiscountAmount() > 0) {
                        $wgItems .= $this->getCouponCode() . '::';
                    }

                    // Add pipe
                    if ($i != $item->getQtyOrdered()) {
                        $wgItems .= ' | ';
                    } else {
                        if ($itemCount != count($items) - 1) {
                            $wgItems .= ' | ';
                        }
                    }
                }
            } else {
                // Event ID
                $wgItems .= $this->getWebgainsEventId() . '::';

                // Item Price
                $itemTotal += $item->getPriceInclTax() - $item->getDiscountAmount()/$item->getQtyOrdered();
                $itemTotal += $shippingRatio;
                $wgItems .= number_format($itemTotal, 2) . '::';

                // Name
                $wgItems .= $item->getName() . '::';

                // SKU
                $wgItems .= $item->getSku() . '::';

                // Coupon code
                if ($item->getDiscountAmount() > 0) {
                    $wgItems .= $this->getCouponCode() . '::';
                }

                if ($itemCount == count($item->getQtyOrdered()) - 1) {
                    $wgItems .= ' | ';
                }
            }
            $itemCount++;
        }
        return $wgItems;
    }
}