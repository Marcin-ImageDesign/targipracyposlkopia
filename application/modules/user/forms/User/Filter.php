<?php

class User_Form_User_Filter extends Engine_Form
{
    protected $_tlabel = 'form_user-admin-filer_';

    public function init()
    {
        $translate = new Zend_View_Helper_Translate();
        $this->setAction('?page=1');
        $this->setMethod('get');

        $email = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $first_name = $this->createElement('text', 'first_name', [
            'label' => $this->_tlabel . 'name',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $last_name = $this->createElement('text', 'last_name', [
            'label' => $this->_tlabel . 'surename',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $position = $this->createElement('select', 'position', [
            'label' => $this->_tlabel . 'role',
            'allowEmpty' => true,
            'multiOptions' => BaseUser::getPositionsForBaseUser($translate),
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'search',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => $this->_tlabel . 'clear',
            'type' => 'submit',
            'ignore' => true,
        ]);

        $listOptionsNoYes = ['' => '', '0' => 'label_form_no', '1' => 'label_form_yes'];
        $isActive = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is-active',
            'multiOptions' => $listOptionsNoYes,
            'allowEmpty' => false,
            'item-class' => 'short_text-inline',
            'class' => 'select-user-filter',
        ]);

        $isBanned = $this->createElement('select', 'is_banned', [
            'label' => $this->_tlabel . 'is-banned',
            'multiOptions' => $listOptionsNoYes,
            //            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'item-class' => 'short_text-inline',
            'class' => 'select-user-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);
        $elements = [$email, $first_name, $last_name,  $position, $isActive, $isBanned, $submit, $clear];
        $baseUser = Zend_Auth::getInstance()->getIdentity()->BaseUser;
        if ($baseUser->isSuperAdmin()) {
            $baseUserListWithEmpty = ['' => ''];
            $baseUserList = Doctrine_Query::create()
                ->from('BaseUser bu')
                ->execute()
                ->toKeyValueArray('id_base_user', 'name')
            ;
            foreach ($baseUserList as $key => $value) {
                $baseUserListWithEmpty[$key] = $value;
            }

            $base_user = $this->createElement('select', 'base_user', [
                'label' => 'Base user',
                'allowEmpty' => true,
                'multiOptions' => $baseUserListWithEmpty,
                //                'decorators'=>$this->elementDecoratorsCenturion
            ]);
            $elements = [$email, $first_name, $last_name, $position, $base_user, $isActive, $isBanned, $submit, $clear];
        }

        $this->addDisplayGroup($elements, 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName($this->_tlabel . 'group');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
