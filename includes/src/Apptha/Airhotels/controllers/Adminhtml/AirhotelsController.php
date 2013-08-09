<?php
/**
 * Property Records
 * @ Author      : Apptha team
 * @package      : Apptha_Airhotels
 * @copyright    : Copyright (c) 2011 (www.apptha.com)
 * @license      : http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Modified Date:June 2012
 */
class Apptha_Airhotels_Adminhtml_AirhotelsController extends Mage_Adminhtml_Controller_action {
    
    const EMAIL_ADMIN_TEMPLATE_XML_PATH = 'airhotels/refund_email/refund_template';

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('airhotels/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * Property record edit action
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('airhotels/airhotels')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
          
            Mage::register('airhotels_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('airhotels/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('airhotels/adminhtml_airhotels_edit'))
                    ->_addLeft($this->getLayout()->createBlock('airhotels/adminhtml_airhotels_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('airhotels')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save the edited details action
     */
    public function saveAction() {

        if ($data = $this->getRequest()->getPost()) {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('airhotels/airhotels');
           
            $model->setData($data)
                  ->setId($id);

            try {
                
                $model->save();
                
                $dataValue = $model->load($id);
     
                /**
                 * Get details and assign to variables for sending email 
                 */
                if ($dataValue->getStatus() == 1 || $dataValue->getStatus() == 2) {

                    $entityId = $dataValue->getEntityId();
                    $cust_Id = Mage::getModel('catalog/product')->load($entityId)->getUserid();
                    $Hoster = Mage::getModel('customer/customer')->load($cust_Id);
                    
                    $customerId = $dataValue->getCustomerId();
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                   
                    $templateId = Mage::getStoreConfig(self::EMAIL_ADMIN_TEMPLATE_XML_PATH);
                    
                    //$mailSubject = $this->__('Payment Status');
                    $adminName = Mage::getStoreConfig('trans_email/ident_general/name');
                    $adminEmail = Mage::getStoreConfig('trans_email/ident_general/email');

                    $sendbyAdmin= Array('name' => $adminName,
                                       'email' => $adminEmail);
                    /**
                     * In case of multiple recipient use array here.
                     */
                     $email = $Hoster->getEmail();
                     $name = $Hoster->getName();
                     
                    /**
                     * If $name = null, then magento will parse the email id
                     * and use the base part as name.
                     */
                   
                    $vars = Array();
                    /* An example how you can pass magento objects and normal variables */

                    $vars = Array(
                        'orderid' => $dataValue->getOrderId(),
                        'productname' => $dataValue->getProductName(),
                        'checkin' => $dataValue->getFromdate(),
                        'checkout' => $dataValue->getTodate(),
                        'customer_name' => $customer->getName(),
                        'customer_email' => $customer->getEmail(),
                        'admin_message' => nl2br($dataValue->getMessage()),
                    );

                    /* This is optional */
                    $storeId = Mage::app()->getStore()->getId();

                    $translate = Mage::getSingleton('core/translate');

                    $mailTemplate = Mage::getModel('core/email_template')
                            //->setTemplateSubject($mailSubject)
                            ->sendTransactional($templateId, $sendbyAdmin, $email, $name, $vars, $storeId);
                 
                    $translate->setTranslateInline(true);


                    if (!$mailTemplate->getSentSuccess()) {
                        $this->_getSession()->addError("There is a problem in Sending Mail! Email not Sent!");
                        $this->_redirect('*/*/');
                        return;
                    } else {

                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('airhotels')->__('Item was successfully saved'));
                    }
                }
                
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('airhotels')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Delete property records
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('airhotels/airhotels');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete multiple selected property records
     */
    public function massDeleteAction() {
        $airhotelsIds = $this->getRequest()->getParam('airhotels');
        if (!is_array($airhotelsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($airhotelsIds as $airhotelsId) {
                    $airhotels = Mage::getModel('airhotels/airhotels')->load($airhotelsId);
                    $airhotels->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($airhotelsIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Export Property records grid to CSV format
     */
    public function exportCsvAction() {
        $fileName = 'airhotels.csv';
        $content = $this->getLayout()->createBlock('airhotels/adminhtml_airhotels_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export Property records grid to XML format
     */
    public function exportXmlAction() {
        $fileName = 'airhotels.xml';
        $content = $this->getLayout()->createBlock('airhotels/adminhtml_airhotels_grid')->getXml();
        $this->_sendUploadResponse($fileName, $content);
        //$this->_prepareDownloadResponse($fileName, $content, $contentType);	
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}