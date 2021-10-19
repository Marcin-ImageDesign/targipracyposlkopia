<?php

class Event_Form_StandProduct_Search extends Engine_Form
{
    private $event;

    public function init()
    {
        $this->setAttrib('class', 'filtersDataTable search_form');

        $this->setAction($this->getView()->url());

        $this->setMethod('GET');

        $search_field = $this->createElement(
            'text',
            'search_field',
            [
                'label' => '',
                //            'decorators' => $this->elementDecoratorsCenturion,
                'allowEmpty' => true,
                'class' => ' field-text',
                'filters' => ['StringTrim'],
            ]
        );

        $offer_city = $this->createElement(
            'text',
            'offer_city',
            [
                'label' => '',
                'allowEmpty' => true,
                'class' => ' field-text',
                'filters' => ['StringTrim'],
            ]
        );

        $submit = $this->createElement('submit', 'submit', [
            'label' => 'Search',
            //            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $clear = $this->createElement('submit', 'clear', [
            'label' => 'Clear',
            //            'decorators' => $this->buttonDecoratorsCenturion,
            'type' => 'submit',
            'ignore' => true,
        ]);

        $isPromotion = $this->createElement(
            'checkbox',
            'is_promotion',
            [
                'label' => 'Is promotion',
                'uncheckedValue' => '0',
            ]
        );

        $exhibitors = $this->createElement(
            'select',
            'id_exhib_stand',
            [
                // 'label' => 'Exhibitors',
                //            'decorators' => $this->elementDecoratorsCenturion,
                'allowEmpty' => false,
                // 'class' => 'select-event-filter'
            ]
        );

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);

        if ('promocjeDev' !== $this->event->short_name) {
            $this->addDisplayGroup([$isPromotion, $search_field, $exhibitors, $submit], 'search');
        } else {
            $this->addDisplayGroup([$isPromotion, $exhibitors, $offer_city, $submit], 'search');
        }
        $group = $this->getDisplayGroup('search');
        $group->setName('Search');
    }

    public function setExhibitors()
    {
        $exhibitors = null;
        $exhibitorsList = Doctrine_Query::create()
            ->from('ExhibStand es')
            ->innerJoin('es.Translations t WITH t.id_language = ?', Engine_I18n::getLangId())
            ->leftJoin('es.AddressProvince ap')
            ->where('es.is_active = 1 AND es.id_exhib_stand_type = ?', ExhibStandType::STANDARD)
            ->addWhere('es.id_event = ?', $this->event->id_event)
            ->orderBy('t.name ASC')
            ->execute();

        $exhibitors[''] = $this->translate('Select exhibitor');

        foreach ($exhibitorsList as $exhibitor) {
            $exhibitors[$exhibitor->getId()] = $exhibitor->getName();
        }

        $this->getElement('id_exhib_stand')->setMultiOptions($exhibitors);
    }

    protected function setEvent($event)
    {
        $this->event = $event;
    }
}
