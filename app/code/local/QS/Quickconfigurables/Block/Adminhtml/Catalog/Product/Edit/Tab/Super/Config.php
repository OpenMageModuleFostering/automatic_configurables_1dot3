<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml catalog super product configurable tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class QS_Quickconfigurables_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Config extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Initialize block
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('quickconfigurables/catalog/product/edit/super/config.phtml');
    }    
	
	/**
     * Prepare Layout data
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
     */
    protected function _prepareLayout()
    {
        if ($this->_getProduct()->getId()) {
            $this->setChild('combination',
                $this->getLayout()->createBlock('quickconfigurables/adminhtml_catalog_product_edit_tab_super_config_combination')
            );
		}
        return parent::_prepareLayout();
    }
	
    /**
     * Retrieve Create New Empty Product URL
     *
     * @return string
     */
	public function getCombinationsUrl()
	{
		return $this->getUrl(
            'quickconfigurables/adminhtml_product/combination',
            array(
                'set'      => $this->_getProduct()->getAttributeSetId(),
				'product'      => $this->_getProduct()->getId(),
                'required' => $this->_getRequiredAttributesIds(),
            )
        );
	}
}
