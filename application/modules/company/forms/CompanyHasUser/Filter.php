<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Filter.
 *
 * @author marcin
 */
class Company_Form_CompanyHasUser_Filter extends Engine_FormAdmin
{
    public function init()
    {
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
        $usersHashMap = ['' => ''];
        foreach ($usersList as $hash => $user) {
            $usersHashMap[$hash] = $user->getName();
        }

        $user = $this->createElement('select', 'user', [
            'label' => 'User',
            'class' => 'form-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'multiOptions' => $usersHashMap,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($usersHashMap)], ],
            ],
        ]);

        $companiesQuery = Doctrine_Query::create()
            ->from('Company c')
            ->where('c.id_base_user = ?', $userAuth->BaseUser->getId())
        ;
        if (!$userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $companiesQuery->addWhere('c.id_user_created = ?', $userAuth->getId());
        }

        $positionsList = Doctrine_Query::create()
            ->from('CompanyPosition cp')
            ->where('cp.id_base_user IS NULL OR cp.id_base_user=?', $userAuth->BaseUser->getId())
            ->execute()
            ->toKeyValueArray('id_company_position', 'name')
        ;
        $positionsListTmp = ['' => ''];
        foreach ($positionsList as $key => $value) {
            $positionsListTmp[$key] = $value;
        }

        $positionsList = $positionsListTmp;

        $companiesCollection = new Doctrine_Collection('Company', 'hash');
        $companiesCollection->merge($companiesQuery->execute());
        $companiesCollection = $companiesCollection->toKeyValueArray('hash', 'name');
        $companiesHashMap = ['' => ''];
        foreach ($companiesCollection as $hash => $name) {
            $companiesHashMap[$hash] = $name;
        }
        $company = $this->createElement('select', 'company', [
            'label' => 'Company',
            'class' => 'form-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'multiOptions' => $companiesHashMap,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($companiesHashMap)], ],
            ],
        ]);

        $position = $this->createElement('select', 'position', [
            'label' => 'Position',
            'class' => 'form-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'multiOptions' => $positionsList,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($positionsList)], ],
            ],
        ]);

        $translate = new Zend_View_Helper_Translate();
        $listOptions = [
            '0' => 'No',
            '1' => 'Yes',
        ];

        foreach ($listOptions as $key => $value) {
            $listOptions[$key] = $translate->translate($value);
        }

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Search',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => 'Clear',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $isActive = $this->createElement('select', 'is_active', [
            'label' => 'Czy aktywny',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'allowEmpty' => false,
            'class' => 'select-event-filter',
        ]);

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$user, $company, $position, $isActive, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }
}
