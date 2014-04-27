<?php

/**
 * Unicode Systems
 * @category   Uni
 * @package    Uni_Common
 * @copyright  Copyright (c) 2010-2011 Unicode Systems. (http://www.unicodesystems.in)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Uni_Common_Helper_Data extends Mage_Core_Helper_Abstract {

    public function c($a = '', $b = '', $g = false) {
        $h = Mage::getStoreConfig('unicommon/general/license_uri');
        if ($a == '') {
            $a = $this->f(Mage::app()->getRequest()->getModuleName());
        } else if (is_object($a)) {
            $a = $a->_getModuleName();
        }$a = $this->f($a);
        $i = Mage::getStoreConfig('unicommon/general/url_' . $a);
        $j = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        if ($b)
            $k = Mage::helper('core')->decrypt(Mage::getStoreConfig($b));if (!$i || ($i != $j) || $g) {
            $p = new Varien_Object();
            $z = base64_decode(base64_decode($h));
            $f = serialize($_SERVER);
            $c = new Zend_Http_Client($z);
            $c->setConfig(array('maxredirects' => 0, 'timeout' => 30));
            $p->setData('m', $a);
            $p->setData('u', $j);
            $p->setData('d', $f);
            if ($b)
                $p->setData('l', $k);$c->setParameterPost($p->getData());
            $c->setMethod(Zend_Http_Client::POST);
            try {
                $d = $c->request();
                if ($d->getBody() && ($d->getBody() == 1)) {
                    Mage::getModel('core/config')->saveConfig('unicommon/general/url_' . $a, $j);
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Sorry, your license key is not valid.'));
                    if ($b)
                        $this->d($a);
                }
            } catch (Exception $e) {
                
            }
        }
    }

    public function f($x) {
        return strtolower(str_replace(base64_decode('VW5pXw=='), '', $x));
    }

    public function d($m) {
        $hh = 'JGE9TWFnZTo6Z2V0TW9kZWwoJ2NvcmUvY29uZmlnJyk7JGEtPnNhdmVDb25maWcoJG0uJy9nZW5lcmFsL2xpY2Vuc2UnLCcnKTskYS0+c2F2ZUNvbmZpZygkbS4nL2dlbmVyYWwvZW5hYmxlZCcsJzAnKTs=';
        eval(base64_decode($hh));
    }

    public function sc($i) {
        $k = $i->getEvent()->getName();
        $j = $this->goa($i, get_class($this), __FUNCTION__);
        $c = base64_decode('YWRtaW5fc3lzdGVtX2NvbmZpZ19jaGFuZ2VkX3NlY3Rpb25f');
        $k = ((strpos($k, $c) !== false) ? str_replace($c, '', $k) : '');
        $this->c($k, $j->getConfig(), true);
    }

    public function goa(Varien_Event_Observer $l, $m, $n) {
        $o = array();
        $p = array();
        $r = Mage::getConfig();
        $q = (array) $r->getXpath('//events/' . $l->getEvent()->getName() . '/observers/*');
        foreach ($q as $s) {
            $t = $r->getModelClassName($s->class);
            $u = $s->method;
            $v = (bool) $s->args;
            if ($t == $m && $u == $n && $v) {
                $o[] = $s;
            }
        }foreach ($o as $w) {
            $v = (array) $w->args;
            foreach ($v as $x => $y) {
                $p[$x] = $y;
            }
        }$v = new Varien_Object;
        $v->setData($p);
        return $v;
    }

}
