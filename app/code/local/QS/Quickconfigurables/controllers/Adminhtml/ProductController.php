<?php
/**
 * Adminhtml quickconfigurable product controller
 *
 * @category   QS
 * @package    QS_Quickconfigurables
 * @author     Quart-soft Magento Team <info@quart-soft.com> 
 * @copyright  Copyright (c) 2010 Quart-soft Ltd http://quart-soft.com
 */

class QS_Quickconfigurables_Adminhtml_ProductController extends Mage_Adminhtml_Controller_action
{
    public function combinationAction()
    {
		$result = array();
		$error = false;
		$success = false;
		
        /* @var $configurableProduct Mage_Catalog_Model_Product */
        $configurableProduct = Mage::getModel('catalog/product')
            ->setStoreId(0)
            ->load($this->getRequest()->getParam('product'));

        if (!$configurableProduct->isConfigurable()) {
            // If invalid parent product
            $this->_redirect('*/*/');
            return;
        }
		
		$postData = $this->getRequest()->getParam('combination', array());
		
		$attributeValues = array();
		$requiredAttributesIds = explode(',',$this->getRequest()->getParam('required'));
		foreach ($requiredAttributesIds as $requiredAttributeId){
			$attributeData = Mage::getModel('eav/config')->getAttribute('catalog_product', $requiredAttributeId);
				foreach ($postData[$attributeData->getAttributeCode()] as $option){
					if ($option){
						$attributeValues[$attributeData->getAttributeCode()][] = $option;
					}
				}
		}
		
		$combinationsTemp = array();
		$combinationsFinal = array();
		$i = 0;
		
		foreach($attributeValues as $code=>$values){
			$combinationsFinal = array();
			if ($combinationsTemp){
				foreach ($combinationsTemp as $tmpComb){
					foreach ($values as $value){
						$combinationsFinal[] = array(
							'codes' => $tmpComb['codes'] . ':' . $code,
							'values' => $tmpComb['values'] . ':' . $value,
						);
					}
				}
			} else {
				foreach ($values as $value){
					$combinationsFinal[] = array(
						'codes' => $code,
						'values' => $value,  
					);
				}
			}
			$combinationsTemp = $combinationsFinal;
		}
		
		$result['product_ids'] = array();
		
		foreach ($combinationsFinal as $comb){
	
			/* @var $product Mage_Catalog_Model_Product */
			$product = Mage::getModel('catalog/product')
				->setStoreId(0)
				->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
				->setAttributeSetId($configurableProduct->getAttributeSetId());


			foreach ($product->getTypeInstance()->getEditableAttributes() as $attribute) {
				if ($attribute->getIsUnique()
					|| $attribute->getFrontend()->getInputType() == 'gallery'
					|| $attribute->getFrontend()->getInputType() == 'media_image'
					|| !$attribute->getIsVisible()) {
					continue;
				}

				$product->setData(
					$attribute->getAttributeCode(),
					$configurableProduct->getData($attribute->getAttributeCode())
				);
			}

			$product->addData($this->getRequest()->getParam('combination', array()));
			
			$codesToSet = explode(':',$comb['codes']);
			$valuesToSet = explode(':',$comb['values']);
			$i = 0;
			foreach ($codesToSet as $codeToSet ){
				$product->setData(
						$codeToSet,
						$valuesToSet[$i++]		
					);
			}
			
			$product->setWebsiteIds($configurableProduct->getWebsiteIds());

			$autogenerateOptions = array();

			$attrsIds = explode(',',$this->getRequest()->getParam('required'));
			
			foreach ($attrsIds as $attrId) {
				$attribute = Mage::getModel('eav/entity_attribute')->load($attrId);
				Mage::log($attribute);
				$value = $product->getAttributeText($attribute->getAttributeCode());
				$autogenerateOptions[] = $value;
				$result['attributes'][] = array(
					'label'         => $value,
					'value_index'   => $product->getData($attribute->getAttributeCode()),
					'attribute_id'  => $attribute->getId()
				);
			}

			if ($product->getNameAutogenerate()) {
				$product->setName($configurableProduct->getName() . '-' . implode('-', $autogenerateOptions));
			}

			if ($product->getSkuAutogenerate()) {
				$product->setSku($configurableProduct->getSku() . '-' . implode('-', $autogenerateOptions));
			}

			if (is_array($product->getPricing())) {
			   $result['pricing'] = $product->getPricing();
			   $additionalPrice = 0;
			   foreach ($product->getPricing() as $pricing) {
				   if (empty($pricing['value'])) {
					   continue;
				   }

				   if (!empty($pricing['is_percent'])) {
					   $pricing['value'] = ($pricing['value']/100)*$product->getPrice();
				   }

				   $additionalPrice += $pricing['value'];
			   }

			   $product->setPrice($product->getPrice() + $additionalPrice);
			   $product->unsPricing();
			}

			try {
				$success = true;
				$product->validate();
				$product->save();
				$result['product_ids'][] = $product->getId();
			} catch (Mage_Core_Exception $e) {
				$result['error'] = array(
					'message' =>  $e->getMessage(),
					'fields'  => array(
						'sku'  =>  $product->getSku()
					)
				);

			} catch (Exception $e) {
				Mage::logException($e);
				$result['error'] = array(
					'message'   =>  $this->__('Product saving error. ') . $e->getMessage()
				 );
			}
		}

		if ($success || $result['product_ids']){
			$this->_getSession()->addSuccess(Mage::helper('catalog')->__('Total %s products were successfully created.',count($result['product_ids'])));
			$this->_initLayoutMessages('adminhtml/session');
			$result['messages']  = $this->getLayout()->getMessagesBlock()->getGroupedHtml();
		}
		
		//$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		$this->getResponse()->setBody(Zend_Json::encode($result));
    }	
	
}