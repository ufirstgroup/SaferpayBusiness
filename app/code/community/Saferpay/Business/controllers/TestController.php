<?php

class Saferpay_Business_TestController extends Mage_Core_Controller_Front_Action
{
	protected $_registerCardUrl;

	public function redirectAction()
	{
		$this->getResponse()->setRedirect('http://local.saferpay/', 300)->sendHeaders();
	}

	public function ajaxAction()
	{
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('core/template', 'test')
			->setTemplate('saferpay/test.phtml');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}

	public function gettestAction()
	{
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('core/template', 'test')
			->assign('registerCardUrl', $this->_getPayRequestPath())
			->assign('saferPayRequestParams', $this->_getPayRequestParams())
			->setTemplate('saferpay/gettest.phtml');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}

	protected function _getRegisterCardUrl()
	{
		if (! $this->_registerCardUrl)
		{
			$this->_registerCardUrl = Mage::getUrl('saferpay/test/success') . '?a=b&c=d';

			$params = array(
				'ACCOUNTID' => Mage::getStoreConfig('saferpay/settings/saferpay_account_id'),
				'SUCCESSLINK' => Mage::getUrl('saferpay/test/success', array('_nosid' => 1)),
				'FAILLINK' => Mage::getUrl('saferpay/test/fail', array('_nosid' => 1)),
				'CARDREFID' => $this->_createCardRefId()
			);
			$url = Mage::getStoreConfig('saferpay/settings/payinit_base_url');
			foreach ($params as $k => $v)
			{
				$url .= strpos($url, '?') === false ? '?' : '&';
				$url .= sprintf("%s=%s", $k, urlencode($v));
			}
			$this->_registerCardUrl = trim(file_get_contents($url));
			//Mage::log($this->_registerCardUrl);
		}

		return $this->_registerCardUrl;
	}

	protected function _getPayRequestParams()
	{
		$url = $this->_getRegisterCardUrl();
		$info = parse_url($url);
		$params = array();
		parse_str($info['query'], $params);
		$out = '';
		foreach ($params as $k => $v)
		{
			$out .= sprintf('<input type="hidden" name="%s" value="%s"/>', $k, htmlspecialchars($v));
		}
		return $out;
	}

	protected function _getPayRequestPath()
	{
		return $this->_getRegisterCardUrl();
	}

	public function _createCardRefId()
	{
		return md5(mt_rand(0, 1000) . microtime());
	}

	public function successAction()
	{
		Mage::log(__METHOD__);
		Mage::log($this->getRequest()->getParams());
		$this->_forward('gettest');
	}

	public function failAction()
	{
		Mage::log(__METHOD__);
		Mage::log($this->getRequest()->getParams());
		$this->_forward('gettest');
	}
}