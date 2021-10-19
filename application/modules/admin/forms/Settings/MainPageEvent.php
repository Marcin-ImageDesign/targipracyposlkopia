<?php

class Admin_Form_Settings_MainPageEvent extends Engine_Form
{
    /**
     * @var array
     */
    protected $eventList;

    protected $_belong_to = 'AdminFormSettingsMain';

    protected $_tlabel = 'form_admin_form_settings_main_';

    public function init()
    {
        $this->setEvents();

        $event = $this->createElement(
            'select',
            'event',
            [
                'label' => $this->_tlabel . 'event-name',
                'multiOptions' => $this->eventList,
                'allowEmpty' => false,
                'validators' => [
                    ['NotEmpty', true],
                    ['InArray', true, [array_keys($this->eventList)]],
                ],
            ]
        );

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');

        $this->addDisplayGroup(
            [$event],
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'event-main-page',
            ]
        );
    }

    public function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        // zapis zmiennej do bazy
        Variable::setVariable('home_page_event_id', $data['event']);

        return true;
    }

    private function setEvents()
    {
        $events = null;
        $eventsList = Doctrine_Query::create()
            ->from('Event e')
            ->where('e.is_active = 1')
            ->execute()
        ;

        $events[''] = $this->translate($this->_tlabel . 'event-name');

        foreach ($eventsList as $event) {
            $events[$event->getId()] = $event->getTitle();
        }

        $this->eventList = $events;
    }
}
