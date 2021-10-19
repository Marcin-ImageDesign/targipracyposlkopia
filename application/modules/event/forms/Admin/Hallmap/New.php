<?php

class Event_Form_Admin_Hallmap_New extends Engine_Form
{
    protected $_belong_to = 'EventFormAdminHallmapNew';

    protected $_tlabel = 'form_event-admin-hallmap_';

    /**
     * @var EventHallMap
     */
    protected $hallmap;

    /**
     * @var Event
     */
    protected $event;

    protected $_hallMapsUrls = [];

    public function init()
    {
        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');
        $this->addMainGroup();
    }

    public function addMainGroup()
    {
        $fields = null;
        $view = Zend_Registry::get('Zend_View');

        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'required' => true,
            'allowEmpty' => false,
            'filters' => ['StringTrim'],
            'value' => $this->hallmap->getName(),
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $hall_templates_query = Doctrine_Query::create()
            ->select('ehm.id_event_hall_map, t.name as name, ehm.uri')
            ->from('EventHallMap ehm INDEXBY ehm.id_event_hall_map')
            ->leftJoin('ehm.Translations t WITH t.id_language = ? ', Engine_I18n::getLangId())
            ->where('ehm.is_template = 1')
            ->execute([], Doctrine::HYDRATE_ARRAY)
        ;

        $templates_array = [];
        $uris = [];

        foreach ($hall_templates_query as $key => $template) {
            $templates_array[$template['uri']] = $template['name'];
            $this->_hallMapsUrls[$template['uri']] = $view->url(['event_hash' => $this->event->getHash(), 'hall_uri' => $template['uri'], 'is_template' => 1], 'event_admin-hall-stands-preview-iframe');
        }

        $fields['hall_template'] = $this->createElement('select', 'hall_template', [
            'label' => 'Hall template',
            'required' => true,
            'allowEmpty' => false,
            'multiOptions' => $templates_array,
            'data-urls' => json_encode($this->_hallMapsUrls),
            'validators' => [
                ['InArray', true, [array_keys($templates_array)]],
            ],
        ]);

        $fields['hall_template']->clearDecorators();
        $fields['hall_template']
            ->addDecorator('ViewHelper')
            ->addDecorator('Description', ['tag' => 'p', 'class' => 'field-description'])
            ->addDecorator('Errors')
            ->addDecorator('HallStandPreview', ['link' => $view->url(['event_hash' => $this->event->getHash(), 'hall_uri' => 'main_hall', 'is_template' => 1], 'event_admin-hall-stands-preview-iframe')])
            ->addDecorator(['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'field-wrapper'])
            ->addDecorator('Label')
            ->addDecorator(['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'form-item'])
        ;

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_info',
            ]
        );
    }

    public function isValid($data)
    {
        return parent::isValid($data);
    }

    protected function setModel($model)
    {
        $this->hallmap = $model;
    }

    protected function setEvent($event)
    {
        $this->event = $event;
    }

    protected function postIsValid($data)
    {
        return true;
    }
}
