<?php

class Sickdaflip_ProductCustomOptionsFile_Block_Adminhtml_Catalog_Product_Edit_Type_Select extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Type_Select
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit/options/type/select-with-file.phtml');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

}
