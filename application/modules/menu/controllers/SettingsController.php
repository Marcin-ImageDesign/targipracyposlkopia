<?php

class Menu_SettingsController extends Engine_Controller_Admin
{
    /**
     * @var \Admin_Form_Settings|mixed
     */
    public $formBaseUser;
    /**
     * @var BaseUser
     */
    private $baseUser;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->baseUser = $this->getSelectedBaseUser();

        $this->_breadcrumb[] = [
            'label' => $this->view->translate('breadcrumb_cms_page-settings'),
            'url' => $this->view->url(),
        ];
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if ($this->_helper->layout->isEnabled()) {
            // generowanie lewego menu dla modułu
            $this->view->renderToPlaceholder('settings/_contentNav.phtml', 'nav-left');
        }
    }

    public function indexAction()
    {
        $this->baseUserForm();
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Page settings'));
    }

    public function baseUserForm()
    {
        $this->formBaseUser = new Admin_Form_Settings(['model' => $this->getSelectedBaseUser()]);
        $metaData = [
            'name' => $this->baseUser->getName(),
            'metatag_title' => $this->baseUser->getMetatagTitle(),
            'metatag_desc' => $this->baseUser->getMetatagDesc(),
            'metatag_key' => $this->baseUser->getMetatagKey(),
        ];
        // var_dump($metaData);exit;

        $this->formBaseUser->populate($metaData);

        if ($this->_request->isPost() && $this->formBaseUser->isValid($this->_request->getPost())) {
            $this->baseUser->setName($this->formBaseUser->header->getValue('name'));
            $this->baseUser->setMetatagTitle($this->formBaseUser->seo->getValue('metatag_title'));
            $this->baseUser->setMetatagDesc($this->formBaseUser->seo->getValue('metatag_desc'));
            $this->baseUser->setMetatagKey($this->formBaseUser->seo->getValue('metatag_key'));
            if (isset($_FILES['image']) && 0 === $_FILES['image']['error']) {
                $image = Service_Image::createImage(
                    $this->baseUser,
                    [
                        'type' => $_FILES['image']['type'],
                        'name' => $_FILES['image']['name'],
                        'source' => $_FILES['image']['tmp_name'], ]
                );

                $image->save();
                $this->baseUser->id_image = $image->getId();
            }
            $this->baseUser->save();
            Variable::setVariable(Variable::USE_SSL, $this->formBaseUser->ssl->getValue('use_ssl'));
            $this->_flash->success->addMessage('Settings saved');
            $this->_redirector->gotoRouteAndExit([], 'admin_settings-seo');
        }

        $this->view->formBaseUser = $this->formBaseUser;
        $this->_helper->viewRenderer->setRender('_commponents/form', null, true);
        $this->view->placeholder('section-class')->set('tpl-form');
    }

    public function variableAction()
    {
        $email_cron = null;
        $variables = $this->getSelectedBaseUser()->getVariables();
        $smtp = $this->getSelectedBaseUser()->getSettingsSmtp();

        var_dump($smtp);
        var_dump('++++++++++++++');
        var_dump($email_cron);
    }
}
