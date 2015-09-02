<?php

class SomethingDigital_AjaxAddtoCart_Model_Observer 
{
  public function ajaxAction(Varien_Event_Observer $observer)
  {
    $controllerAction = $observer->getControllerAction();
    $response         = Mage::app()->getResponse();
    $result           = [];

    if(!$controllerAction->getRequest()->isAjax()) {
      return;
    }

    /* @var $catalogModel Mage_Core_Model_Catalog_Product */
    $catalogModel = Mage::getModel('catalog/product');
    /* @var $catalogModel Mage_Core_Helper_Abstract */
    $coreHelper   = Mage::helper('core');

    $storeId      = Mage::app()->getStore()->getId();
    $productId    = $observer->getControllerAction()->getRequest()->getParam('product');
    $product      = $catalogModel->setStoreId($storeId)->load($productId);

    try {
      if (!$product) {
        $result['status'] = 'ERROR';
        $result['message'] = $coreHelper->__('Unable to find Product ID');
      }
      $message = $coreHelper->__('%s was added to your shopping cart.', $coreHelper->htmlEscape($product->getName()));
      $result['status'] = 'SUCCESS';
      $result['message'] = $message;
      $controllerAction->loadLayout();
      $sidebar = $controllerAction->getLayout()->getBlock('minicart_head')->toHtml();
      $result['minicart_head'] = '<div class="header-minicart">' . $sidebar . '</div>';
    } catch(Mage_Core_Exception $e) {
          $result['status'] = 'ERROR';
          $result['message'] = $coreHelper->__('Cannot add the item to shopping cart.');
          Mage::logException($e);
    }
    if($result['status'] == 'ERROR'){
        $result['message'] = '<ul class="messages"><li class="error-msg"><ul><li class="out-of-stock-error">' . $result['message'] . '</li></ul></li></ul>';
    }

    $response->clearAllHeaders();

    if($result['status']==='SUCCESS'){
      $response->setHttpResponseCode(200);
    } else {
      $response->setHttpResponseCode(520);
    }

    $response->setBody($coreHelper->jsonEncode($result))
      ->setHeader('Content-Type', 'application/json')
      ->sendHeaders();
  }
}
