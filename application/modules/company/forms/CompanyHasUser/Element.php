<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Element.
 *
 * @author marcin
 */
class Company_Form_CompanyHasUser_Element extends Engine_FormAdmin
{
    /**
     * @var CompanyHasUser
     */
    private $companyHasUser;

    public function __construct($companyHasUser, $options = null)
    {
        parent::__construct($options);
        $this->companyHasUser = $companyHasUser;

        $userAuth = Zend_Auth::getInstance()->getIdentity();

        $usersListQuery = Doctrine_Query::create()
            ->from('User u')
            ->where('u.id_base_user = ?', $userAuth->BaseUser->getId())
        ;

        if (!$userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $usersListQuery->addWhere('u.id_user = ? OR u.id_user_created = ?', [
                $userAuth->getId(),
                $userAuth->getId(),
            ]);
        }

        $usersListQuery->orderBy('u.last_name ASC,u.first_name ASC');

        $usersList = new Doctrine_Collection('User', 'hash');
        $usersList->merge($usersListQuery->execute());
        $usersHashMap = [];
        foreach ($usersList as $hash => $user) {
            $usersHashMap[$hash] = $user->getName() . ', ' . $user->getEmail();
        }

        $user = $this->createElement('select', 'user', [
            'label' => 'User',
            'class' => 'field-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'multiOptions' => $usersHashMap,
            'value' => $this->companyHasUser->User->getHash(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($usersHashMap)], ],
            ],
        ]);

        $user->setDescription('User');

        $companiesQuery = Doctrine_Query::create()
            ->from('Company c')
            ->where('c.id_base_user = ?', $userAuth->BaseUser->getId())
        ;
        if (!$userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $companiesQuery->addWhere('c.id_user_created = ?', $userAuth->getId());
        }
        $companiesCollection = new Doctrine_Collection('Company', 'hash');
        $companiesCollection->merge($companiesQuery->execute());
        $companiesHashMap = $companiesCollection->toKeyValueArray('hash', 'name');
        $company = $this->createElement('select', 'company', [
            'label' => 'Company',
            'class' => 'field-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'multiOptions' => $companiesHashMap,
            'value' => $this->companyHasUser->Company->getHash(),
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($companiesHashMap)], ],
            ],
        ]);

        $company->setDescription('Company');

        $translate = new Zend_View_Helper_Translate();

        $listOptions = [
            '0' => 'No',
            '1' => 'Yes',
        ];

        foreach ($listOptions as $key => $value) {
            $listOptions[$key] = $translate->translate($value);
        }

        $companyPositions = Doctrine_Query::create()
            ->from('CompanyPosition cp')
            ->where('cp.id_base_user IS NULL OR cp.id_base_user = ?', $this
            ->companyHasUser
            ->BaseUser
            ->getId())
            ->execute()
            ->toKeyValueArray('id_company_position', 'name')
        ;
        $position = $this->createElement('select', 'position', [
            'label' => 'Position',
            'required' => true,
            'multiOptions' => $companyPositions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'value' => (int) $companyHasUser->id_company_position,
            'class' => 'field-text-small',
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($companyPositions)], ],
            ],
        ]);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => 'Czy aktywny',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'value' => (int) $companyHasUser->is_active,
            'class' => 'field-switcher',
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($listOptions)], ],
            ],
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);
        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$user, $company, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        // prawa strona
        $aside = new Zend_Form_SubForm();
        $aside->setDisableLoadDefaultDecorators(false);
        $aside->setDecorators($this->subFormDecoratorsCenturion);
        $aside->addDisplayGroup([$is_active], 'aside');

        $groupAside = $aside->getDisplayGroup('aside');
        $groupAside->clearDecorators();
        $groupAside->setLegend('Options');
        $groupAside->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $aside->addAttribs(['class' => 'form-aside right_aside']);

        $main = new Zend_Form_SubForm();
        $main->setDisableLoadDefaultDecorators(false);
        $main->setDecorators($this->subFormDecoratorsCenturion);
        $main->addDisplayGroup([$position], 'content');

        $group = $main->getDisplayGroup('content');
        $group->clearDecorators();
        $group->setLegend('Position');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $main->addAttribs(['class' => 'form-main']);

        $this->addSubForm($header, 'header');
        $this->addSubForm($aside, 'aside');
        $this->addSubForm($main, 'main');
    }

    public function isValid($values)
    {
        $user = $values['header']['user'];
        $company = $values['header']['company'];

        $ret = parent::isValid($values);

        if (!empty($user) && !empty($company)) {
            $userObject = User::findOneByHash($user);
            $companyObject = Company::findOneByHash($company);
            $query = Doctrine_Query::create()
                ->from('CompanyHasUser chu')
                ->where('chu.id_user = ?', $userObject->getId())
                ->addWhere('chu.id_company = ?', $companyObject->getId())
            ;
            if (!$this->companyHasUser->isNew()) {
                $query->addWhere('chu.id_company_has_user != ?', $this->companyHasUser->getId());
            }

            $count = $query->execute()->count();
            if ($count > 0) {
                $this->header->user->addError('User already in company');

                return false;
            }
        }

        return $ret;
    }
}
