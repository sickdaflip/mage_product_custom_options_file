<?php
require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS.'Catalog'.DS.'ProductController.php');

class Sickdaflip_ProductCustomOptionsFile_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController {

    protected function _initProductSave()
    {
        $product     = $this->_initProduct();
        $productData = $this->getRequest()->getPost('product');
        if ($productData) {
            $this->_filterStockData($productData['stock_data']);
        }

        /**
         * Websites
         */
        if (!isset($productData['website_ids'])) {
            $productData['website_ids'] = array();
        }

        $wasLockedMedia = false;
        if ($product->isLockedAttribute('media')) {
            $product->unlockAttribute('media');
            $wasLockedMedia = true;
        }

        $product->addData($productData);

        if ($wasLockedMedia) {
            $product->lockAttribute('media');
        }

        if (Mage::app()->isSingleStoreMode()) {
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        /**
         * Create Permanent Redirect for old URL key
         */
        if ($product->getId() && isset($productData['url_key_create_redirect']))
            // && $product->getOrigData('url_key') != $product->getData('url_key')
        {
            $product->setData('save_rewrites_history', (bool)$productData['url_key_create_redirect']);
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $product->setData($attributeCode, false);
            }
        }

        /**
         * Init product links data (related, upsell, crosssel)
         */
        $links = $this->getRequest()->getPost('links');
        if (isset($links['related']) && !$product->getRelatedReadonly()) {
            $product->setRelatedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['related']));
        }
        if (isset($links['upsell']) && !$product->getUpsellReadonly()) {
            $product->setUpSellLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['upsell']));
        }
        if (isset($links['crosssell']) && !$product->getCrosssellReadonly()) {
            $product->setCrossSellLinkData(Mage::helper('adminhtml/js')
                ->decodeGridSerializedInput($links['crosssell']));
        }
        if (isset($links['grouped']) && !$product->getGroupedReadonly()) {
            $product->setGroupedLinkData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($links['grouped']));
        }

        /**
         * Initialize product categories
         */
        $categoryIds = $this->getRequest()->getPost('category_ids');
        if (null !== $categoryIds) {
            if (empty($categoryIds)) {
                $categoryIds = array();
            }
            $product->setCategoryIds($categoryIds);
        }

        /**
         * Initialize data for configurable product
         */
        if (($data = $this->getRequest()->getPost('configurable_products_data'))
            && !$product->getConfigurableReadonly()
        ) {
            $product->setConfigurableProductsData(Mage::helper('core')->jsonDecode($data));
        }
        if (($data = $this->getRequest()->getPost('configurable_attributes_data'))
            && !$product->getConfigurableReadonly()
        ) {
            $product->setConfigurableAttributesData(Mage::helper('core')->jsonDecode($data));
        }

        $product->setCanSaveConfigurableAttributes(
            (bool) $this->getRequest()->getPost('affect_configurable_product_attributes')
            && !$product->getConfigurableReadonly()
        );

        $skuImageName=trim($product->getSku());
        $imagesFiles=$_FILES;
        $path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'customoption';

        foreach ($imagesFiles as $key=>$value)
        {
            $optionsValue = explode('-',$key);
            foreach ($value as $key2=>$value2)
            {
                if($key2=='name' && $value2!="") {
                    try {
                        $uploader = new Varien_File_Uploader($key);
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','svg'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $optionTitle = trim($productData['options'][$optionsValue[0]]['title']);
                        $optionValueTitle = trim($productData['options'][$optionsValue[0]]['values'][$optionsValue[1]]['title']);
                        $imageExtension = pathinfo($value2,PATHINFO_EXTENSION);
                        $newImageName =$skuImageName.'_'.$optionTitle.'_'.$optionValueTitle.'.'.$imageExtension;
                        $uploader->save($path, $newImageName);
                        $productData['options'][$optionsValue[0]]['values'][$optionsValue[1]]['image']=$uploader->getUploadedFileName();
                    } catch(Exception $e) {
                        Mage::log('Unable to save custom option image. ' . $e->getMessage(), null, null, true);
                    }

                }
            }
        }

        /**
         * Initialize product options
         */
        if (isset($productData['options']) && !$product->getOptionsReadonly()) {
            $product->setProductOptions($productData['options']);
        }

        $product->setCanSaveCustomOptions(
            (bool)$this->getRequest()->getPost('affect_product_custom_options')
            && !$product->getOptionsReadonly()
        );

        Mage::dispatchEvent(
            'catalog_product_prepare_save',
            array('product' => $product, 'request' => $this->getRequest())
        );

        return $product;
    }

}
