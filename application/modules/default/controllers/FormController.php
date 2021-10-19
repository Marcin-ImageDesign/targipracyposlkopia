<?php

class FormController extends Engine_Controller_Frontend
{
    private $_useSSL = false;

    public function init()
    {
        if (Engine_Variable::getInstance()->issetVariable('forms_use_ssl')) {
            $this->_useSSL = (bool) Engine_Variable::getInstance()->getVariable('forms_use_ssl');
        }
    }

    public function getAction()
    {
        $this->_helper->layout->setLayout('layout_form');

        $formOptions = [];

        if ('none' !== $this->getParam('baseUser')) {
            $formOptions['baseUser'] = BaseUser::findOneByHash($this->getParam('baseUser'));
        }
        if ('none' !== $this->getParam('exhibStand')) {
            $formOptions['exhibStand'] = ExhibStand::findOneByHash($this->getParam('exhibStand'));
        }
        if ('none' !== $this->getParam('standProduct')) {
            $formOptions['standProduct'] = StandProduct::findOneByHash($this->getParam('standProduct'));
        }
        if ('none' !== $this->getParam('user')) {
            $formOptions['user'] = $this->userAuth;
        }
        $formClassName = urldecode(str_replace(['%252F', '%255C'], ['%2F', '%5C'], $this->getParam('className')));
        $formAction = str_replace('__', '/', urldecode($this->getParam('formAction')));
        $formId = urldecode(str_replace(['%252F', '%255C'], ['%2F', '%5C'], $this->getParam('formId')));

        if ($this->_useSSL && defined('SSL_URL_PATH')) {
            $formAction = SSL_URL_PATH . $formAction;
        }

        if (class_exists($formClassName)) {
            $form = new $formClassName($formOptions);
            $form->setRenderIframe(false);
            $form->setAttrib('id', $formId);
            $form->setAction($formAction);

            $this->view->isChat = false;
            if ($form->getAttrib('id') == $form->getElementsBelongTo() . 'Form_Chat') {
                $this->view->isChat = true;
            }

            $this->view->form = $form;
        } else {
            throw new Exception('Form ' . $formClassName . ' does not exist!');
        }
    }
}
