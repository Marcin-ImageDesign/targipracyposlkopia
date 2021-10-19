<?php
/**
 * Description of Event.
 *
 * @author marcin
 */
class Event_Form_Search_Event extends Engine_Form
{
    public function init()
    {
        $this->addElement('text', 'title', [
            'filter' => 'StringTrim',
            'Label' => 'Event name',
        ]);
        if (Zend_Auth::getInstance()->getIdentity()) {
            $this->addElement('checkbox', 'own', [
                'label' => 'Show own events',
                'class' => 'field',
                'allowEmpty' => true,
            ]);
        }
        $this->addElement(
            'submit',
            'submit',
            [
                'Label' => 'Search',
            ]
        );
        $this->addElement(
            'submit',
            'clear',
            [
                'Label' => 'Clear',
            ]
        );

        $this->addDisplayGroup(['submit', 'clear'], 'event_search_display_group');
    }
}
