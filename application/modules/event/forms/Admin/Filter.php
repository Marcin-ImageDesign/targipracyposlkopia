<?php

class Event_Form_Admin_Filter extends Engine_Form
{
    protected $_tlabel = 'form_filter_event_stand_';
    protected $_event;

    public function init()
    {
        $this->setAction('?page=1');
        $this->setMethod('get');

        $stand_number = $this->createElement('text', 'stand_number', [
            'label' => $this->_tlabel . 'stand_number',
            'allowEmpty' => false,
            'class' => '',
            'filters' => ['StringTrim'],
        ]);

        $stand_name = $this->createElement('text', 'stand_name', [
            'label' => $this->_tlabel . 'stand_name',
            'allowEmpty' => false,
            'class' => ' field-text',
            'filters' => ['StringTrim'],
        ]);

        $standTypeQuery = Doctrine::getTable('StandLevel')->findAll()->toKeyValueArray('id_stand_level', 'name');
        $standTypeList = ['' => ''];

        foreach ($standTypeQuery as $key => $standType) {
            $standTypeList[$key] = 'form_stand_type_' . $standType;
        }

        $stand_type = $this->createElement('select', 'stand_type', [
            'label' => $this->_tlabel . 'stand_type',
            'multiOptions' => $standTypeList,
            'allowEmpty' => false,
            'class' => '',
        ]);

        if ($this->_event) {
            $hall_maps = Doctrine_Query::create()
                ->select('ehm.id_event_hall_map, t.name as name')
                ->from('EventHallMap ehm')
                ->leftJoin('ehm.Translations t WITH t.id_language = ? ', Engine_I18n::getLangId())
                ->where('ehm.id_event = ?', $this->_event->getId())
                ->execute()
                ->toKeyValueArray('id_event_hall_map', 'name')
            ;
        } else {
            $hall_maps = [];
            $hall_maps_query = Doctrine_Query::create()
                // ->select('e.id_event, t.title as title, ehm.id_event_hall_map')
                ->leftJoin('Event e')
                ->leftJoin('e.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
                ->leftJoin('e.EventHallMaps ehm')
                ->leftJoin('ehm.Translations tt WITH tt.id_language = ? ', Engine_I18n::getLangId())
                ->execute()
            ;

            foreach ($hall_maps_query as $event) {
                foreach ($event->EventHallMaps as $map) {
                    $hall_maps[$event->getTitle()][$map->getId()] = $map->getName();
                }
            }
        }

        $hall_level = $this->createElement('select', 'id_event_hall_map', [
            'label' => 'Hall level',
            'multiOptions' => ['' => ''] + $hall_maps,
            'allowEmpty' => true,
        ]);

        $options = [
            '' => '',
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);

        $is_active = $this->createElement('select', 'is_active', [
            'label' => $this->_tlabel . 'is_active',
            'multiOptions' => $listOptions,
            'allowEmpty' => false,
            'class' => 'select-stand-filter',
            'item-class' => 'short_text-inline',
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

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        $this->addDisplayGroup([$stand_number, $stand_name, $stand_type, $hall_level, $is_active, $submit, $clear], 'filter');
        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }

    protected function setEvent($event)
    {
        $this->_event = $event;
    }
}
