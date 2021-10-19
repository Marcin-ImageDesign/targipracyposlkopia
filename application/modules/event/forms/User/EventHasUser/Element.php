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
class Event_Form_User_EventHasUser_Element extends Engine_FormAdmin
{
    protected $eventHasUser;

    public function __construct($eventHasUser, $selectedLanguage, $options)
    {
        parent::__construct($options);
        $this->eventHasUser = $eventHasUser;

        $userAuth = Zend_Auth::getInstance()->getIdentity();
        $usersListQuery = Doctrine_Query::create()
            ->from('User u')
            ->where('u.id_base_user = ?', $userAuth->BaseUser->getId())
        ;

        if (!$userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $usersListQuery->addWhere(
                ' u.id_user_created = ?',
                $userAuth->getId()
            );
        }
        $usersList = [];
        $usersList[$userAuth->getId()] = $userAuth->getName();
        $usersListCol = $usersListQuery->execute();
        foreach ($usersListCol as $user) {
            $usersList[$user->getId()] = $user->getName();
        }

        $user = $this->createElement('select', 'user', [
            'label' => 'User',
            'required' => true,
            'allowEmpty' => false,
            'class' => 'field-text-big',
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'multiOptions' => $usersList,
            'value' => $eventHasUser->id_user,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($usersList)], ],
            ],
        ]);
        $user->setDescription('User');

        $eventListQuery = Doctrine_Query::create()
            ->from('Event e')
            ->where('e.id_base_user = ?', $userAuth->BaseUser->getId())
        ;
        if (!$userAuth->hasAccess(AuthPermission::EVENT_ACCESS_TO_ALL)) {
            $eventListQuery->addWhere('e.id_user_created = ?', $userAuth->getId());
        }

        if (Language::DEFAULT_LANGUAGE_CODE !== $selectedLanguage->code && isset($selectedLanguage->BaseUserLanguageOne)) {
            $eventListQuery->leftJoin('e.EventLanguageOne elo WITH elo.id_base_user_language = ?', $selectedLanguage->BaseUserLanguageOne->getId());
        }

        $eventsList = $eventListQuery->execute()->toKeyValueArray('id_event', 'title');

        $event = $this->createElement('select', 'event', [
            'label' => 'Event',
            'required' => true,
            'allowEmpty' => false,
            'multiOptions' => $eventsList,
            'class' => 'field-text-big',
            'value' => $eventHasUser->id_event,
            'decorators' => $this->elementDecoratorsCenturionHeader,
            'validators' => [
                ['InArray',
                    false,
                    [array_keys($eventsList)], ],
            ],
        ]);
        $event->setDescription('Event');

        $options = [
            '0' => 'No',
            '1' => 'Yes',
        ];
        $listOptions = $this->translate($options);

        $isConfirm = $this->createElement('select', 'is_confirm', [
            'label' => 'Is confirm',
            'multiOptions' => $listOptions,
            'decorators' => $this->elementDecoratorsCenturion,
            'value' => (int) $eventHasUser->is_confirm,
            'allowEmpty' => false,
            'class' => 'field-switcher',
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Save',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
            'class' => 'ui-button ui-button-bg-white ui-button-text-red ui-button-text-only ui-button ui-button-nicy ui-button-text',
        ]);

        // nagłówek
        $header = new Zend_Form_SubForm();
        $header->setDisableLoadDefaultDecorators(false);
        $header->setDecorators($this->subFormDecoratorsCenturion);
        $header->addElements([$user, $event, $submit]);
        $header->addAttribs(['class' => 'form-header']);

        // prawa strona
        $aside = new Zend_Form_SubForm();
        $aside->setDisableLoadDefaultDecorators(false);
        $aside->setDecorators($this->subFormDecoratorsCenturion);
        $aside->addDisplayGroup([$isConfirm], 'aside');

        $groupAside = $aside->getDisplayGroup('aside');
        $groupAside->clearDecorators();
        $groupAside->setLegend('Options');
        $groupAside->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $aside->addAttribs(['class' => 'form-aside right_aside']);

        $this->addSubForm($header, 'header');
        $this->addSubForm($aside, 'aside');
    }

    public function isValid($options)
    {
        $ret = parent::isValid($options);
        $user = $this->header->user->getValue();
        $event = $this->header->event->getValue();
        if (!empty($user) && !empty($event)) {
            $eventHasUserCount = Doctrine_Query::create()
                ->from('EventHasUser ehu')
                ->where('ehu.id_user = ?', $user)
                ->addWhere('ehu.id_event =?', $event)
            ;
            if (!$this->eventHasUser->isNew()) {
                $eventHasUserCount->addWhere('ehu.hash != ?', $this->eventHasUser->hash);
            }
            $count = $eventHasUserCount->execute()
                ->count()
          ;

            if ($count > 0) {
                $this->header->user->addError('User already in event');

                return false;
            }
        }

        return $ret;
    }
}
