<?php

class Sickdaflip_ProductCustomOptionsFile_Block_Catalog_Product_View_Options_Type_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select
{

    public function getValuesHtml()
    {
        $_option = $this->getOption();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $_option->getId());
        $store = $this->getProduct()->getStore();

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
            $require = ($_option->getIsRequire()) ? ' required-entry' : '';
            $extraParams = '';
            $select = $this->getLayout()->createBlock('core/html_select')
                ->setData(array(
                    'id' => 'select_'.$_option->getId(),
                    'class' => $require.' product-custom-option'
                ));
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $select->setName('options['.$_option->getid().']')
                    ->addOption('', $this->__('-- Please Select --'));
            } else {
                $select->setName('options['.$_option->getid().'][]');
                $select->setClass('multiselect'.$require.' product-custom-option');
            }
            foreach ($_option->getValues() as $_value) {
                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice(($_value->getPriceType() == 'percent'))
                ), false);
                $select->addOption(
                    $_value->getOptionTypeId(),
                    $_value->getTitle() . ' ' . $priceStr . '',
                    array('price' => $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false))
                );
            }
            if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_MULTIPLE) {
                $extraParams = ' multiple="multiple"';
            }
            if (!$this->getSkipJsReloadPrice()) {
                $extraParams .= ' onchange="opConfig.reloadPrice()"';
            }
            $select->setExtraParams($extraParams);

            if ($configValue) {
                $select->setValue($configValue);
            }

            return $select->getHtml();
        }

        if ($_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO
            || $_option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX
        ) {
            $selectHtml = '<ul id="options-'.$_option->getId().'-list" class="options-list">';
            $require = ($_option->getIsRequire()) ? ' validate-one-required-by-name' : '';
            $arraySign = '';
            switch ($_option->getType()) {
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO:
                    $type = 'radio';
                    $class = 'radio';
                    if (!$_option->getIsRequire()) {
                        $selectHtml .= '<li><input type="radio" id="options_' . $_option->getId() . '" class="'
                            . $class . ' product-custom-option" name="options[' . $_option->getId() . ']"'
                            . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                            . ' value="" checked="checked" /><span class="label"><label for="options_'
                            . $_option->getId() . '">' . $this->__('None') . '</label></span></li>';
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_TYPE_CHECKBOX:
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    break;
            }
            $count = 1;
            foreach ($_option->getValues() as $_value) {
                $count++;
                $priceStr = $this->_formatPrice(array(
                    'is_percent'    => ($_value->getPriceType() == 'percent'),
                    'pricing_value' => $_value->getPrice($_value->getPriceType() == 'percent')
                ));

                $htmlValue = $_value->getOptionTypeId();
                if ($arraySign) {
                    $checked = (is_array($configValue) && in_array($htmlValue, $configValue)) ? 'checked' : '';
                } else {
                    $checked = $configValue == $htmlValue ? 'checked' : '';
                }
                if ($_value->getImage() && !$_value->getDescription()) {
                    $liclass = 'class="with-image"';
                } elseif ($_value->getImage() && $_value->getDescription()) {
                    $liclass = 'class="with-image-desc"';
                } elseif (!$_value->getImage() && $_value->getDescription()) {
                    $liclass = 'class="with-desc"';
                } else {
                    $liclass = '';
                }

                $selectHtml .= '<li ' . $liclass . '><input type="' . $type . '" class="' . $class . ' ' . $require
                    . ' product-custom-option"'
                    . ($this->getSkipJsReloadPrice() ? '' : ' onclick="opConfig.reloadPrice()"')
                    . ' name="options[' . $_option->getId() . ']' . $arraySign . '" id="options_' . $_option->getId()
                    . '_' . $count . '" value="' . $htmlValue . '" ' . $checked . ' price="'
                    . $this->helper('core')->currencyByStore($_value->getPrice(true), $store, false) . '" />'
                    . '<span class="label"><label for="options_' . $_option->getId() . '_' . $count . '">';
                if ($_value->getImage()) {
                    $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'catalog' . DS . 'customoption' .DS;
                    $selectHtml .= '<img width="75" src="' . $path . $_value->getImage() . '" alt="' . $this->escapeHtml($_value->getTitle()) . '" />';
                }
                $selectHtml .=  '<span class="text">' . $this->escapeHtml($_value->getTitle()) . ' ' . $priceStr . '</span>';
                $selectHtml .= '</label></span>';

                if ($_option->getIsRequire()) {
                    $selectHtml .= '<script type="text/javascript">' . '$(\'options_' . $_option->getId() . '_'
                        . $count . '\').advaiceContainer = \'options-' . $_option->getId() . '-container\';'
                        . '$(\'options_' . $_option->getId() . '_' . $count
                        . '\').callbackFunction = \'validateOptionsCallback\';' . '</script>';
                }
                if ($_value->getDescription()) {
                    $selectHtml .= '<a data-toggle="options' . $_option->getId() . '' . $count . '"><i class="fas fa-info"></i></a>';
                    $selectHtml .= '<div class="reveal" id="options' . $_option->getId() . '' . $count . '" data-reveal>';
                    $selectHtml .= '<p class="h3">' . $this->escapeHtml($_value->getTitle()) . '</p>';
                    $selectHtml .= $_value->getDescription();
                    $selectHtml .= '<button class="close-button" data-close aria-label="Close modal" type="button"><span aria-hidden="true">&times;</span></button></div>';
                }
                $selectHtml .= '</li>';
            }
            $selectHtml .= '</ul>';

            return $selectHtml;
        }
    }
}