<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marek
 * Date: 22.10.13
 * Time: 11:30
 * To change this template use File | Settings | File Templates.
 */
class Shop_Form_Filter extends Engine_FormAdmin
{
    protected $_tlabel = 'shop_form_filter_';

    public function init()
    {
        $fields = [];

        $this->setAction('?page=1');
        $this->setMethod('get');

        $fields['date_start'] = $this->createElement('DatePicker', 'date_start', [
            'label' => 'Date start',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                ['Date', true, ['format' => 'YYYY-MM-dd']],
            ],
        ]);

        $fields['date_end'] = $this->createElement('DatePicker', 'date_end', [
            'label' => 'Date end',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
            'validators' => [
                ['Date', true, ['format' => 'YYYY-MM-dd']],
            ],
        ]);

        $fields['price_start'] = $this->createElement('text', 'price_start', [
            'label' => $this->_tlabel . 'price_start',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
        ]);

        $fields['price_end'] = $this->createElement('text', 'price_end', [
            'label' => $this->_tlabel . 'price_end',
            'allowEmpty' => true,
            'required' => false,
            'filters' => ['StringTrim'],
        ]);

        $options = [
            '' => '',
            '0' => 'No',
            '1' => 'Yes',
        ];

        $listOptions = $this->translate($options);

        $fields['is_read'] = $this->createElement('select', 'is_read', [
            'label' => $this->_tlabel . 'is_read',
            'required' => false,
            'class' => 'select-is-read-filter',
            'multiOptions' => $listOptions,
            'validators' => [
                ['InArray', true, [array_keys($listOptions)]],
            ],
        ]);

        $fields['submit'] = $this->createElement('submit', 'submit', [
            'label' => 'Search',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $fields['clear'] = $this->createElement('submit', 'clear', [
            'label' => 'Clear',
            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $this->addDisplayGroup(
            $fields,
            'filter'
        );

        $group = $this->getDisplayGroup('filter');
        $group->setName('Filtr');
        $gDecorator = $group->getDecorator('row');
        $gDecorator->setOption('class', 'box grid-filter');
    }

    public function populate(array $values)
    {
        $date_start = $values['date_start'];
        $date_end = $values['date_end'];
        if ((strtotime($date_start) > strtotime($date_end)) && (!empty($date_start) && !empty($date_end))) {
            $tmp = $date_start;
            $date_start = $date_end;
            $date_end = $tmp;
        }
        $values['date_start'] = $date_start;
        $values['date_end'] = $date_end;

        return parent::populate($values);
    }
}
