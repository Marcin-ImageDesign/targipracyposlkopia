<?php
/**
 * Created by PhpStorm.
 * User: marek
 * Date: 25.10.13
 * Time: 11:12.
 */
class Shop_Form_Location_Element extends Engine_Form
{
    protected $_belong_to = 'ShopFormLocationElement';
    protected $_tlabel = 'shop_form_location_element_';

    /**
     * @var ShopLocation
     */
    protected $_model;

    public function init()
    {
        $fields = [];

        $fields['name'] = $this->createElement('text', 'name', [
            'label' => $this->_tlabel . 'name',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_model->getName(),
        ]);

        $eventsArr = [];
        $eventList = Doctrine_Query::create()
            ->from('Event e INDEX BY e.id_event')
            ->execute()
        ;
        foreach ($eventList as $event) { // @var $event Event
            $eventsArr[$event->getId()] = $event->getTitle();
        }

        $fields['id_event'] = $this->createElement('select', 'id_event', [
            'label' => $this->_tlabel . 'id_event',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'multiOptions' => $eventsArr,
            'validators' => [
                ['NotEmpty', true],
                ['InArray', true, [array_keys($eventsArr)]],
            ],
            'value' => $this->_model->getIdEvent(),
        ]);

        $fields['email'] = $this->createElement('text', 'email', [
            'label' => $this->_tlabel . 'email',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
                ['EmailAddress', true],
            ],
            'value' => $this->_model->getEmail(),
        ]);

        $fields['country'] = $this->createElement('text', 'country', [
            'label' => $this->_tlabel . 'country',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_model->getCountry(),
        ]);

        $fields['city'] = $this->createElement('text', 'city', [
            'label' => $this->_tlabel . 'city',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_model->getCity(),
        ]);

        $fields['post_code'] = $this->createElement('text', 'post_code', [
            'label' => $this->_tlabel . 'post_code',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_model->getPostCode(),
        ]);

        $fields['street'] = $this->createElement('text', 'street', [
            'label' => $this->_tlabel . 'street',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_model->getStreet(),
        ]);

        $fields['description'] = $this->createElement('textarea', 'description', [
            'label' => $this->_tlabel . 'description',
            'allowEmpty' => false,
            'required' => true,
            'filters' => ['StringTrim'],
            'validators' => [
                ['NotEmpty', true],
            ],
            'value' => $this->_model->getDescription(),
        ]);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
            'type' => 'submit',
        ]);

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'legend' => $this->_tlabel . 'main-info',
            ]
        );

        $this->addDisplayGroup([$submit], 'buttons');
    }

    /**
     * Set model.
     *
     * @param $model
     */
    protected function setModel($model)
    {
        $this->_model = $model;
    }
}
