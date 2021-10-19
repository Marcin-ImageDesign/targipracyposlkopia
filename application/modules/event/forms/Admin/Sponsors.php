<?php

class Event_Form_Admin_Sponsors extends Engine_Form
{
    protected $_belong_to = 'EventFormAdminSponsors';

    protected $_tlabel = 'form_event-admin-sponsors_';

    protected $_event;

    protected $_bannerData;
    private $_subFormBanners = [];

    public function init()
    {
        $eventSponsors = $this->createElement('textarea', 'map_sponsors', [
            'label' => $this->_tlabel . 'map_sponsors',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_event->getMapSponsorsRaw(),
        ]);

        $eventSponsors->setOptions(['cols' => '4', 'rows' => '20']);

        $eventSponsorsTranslation = $this->createElement('textarea', 'map_sponsors_translation', [
            'label' => $this->_tlabel . 'map_sponsors_translation',
            'required' => true,
            'allowEmpty' => false,
            'value' => $this->_event->getTranslation()->getMapSponsorsRaw(),
        ]);

        $eventSponsorsTranslation->setOptions(['cols' => '4', 'rows' => '20']);

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');

        $this->addDisplayGroup(
            [$eventSponsors, $eventSponsorsTranslation],
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'event-main-page',
            ]
        );

        $this->addSponsorsGroup();

        $this->setDecorators([
            'FormElements',
            [['add' => 'OptionButton'], [
                'value' => $this->getView()->translate('form-button-option_add-banner'),
                'class' => 'sponsorsAddBanner',
                'block' => 'sponsorsAddBanner',
            ]],
            'Form',
            ['FormError', ['placement' => 'PREPEND']],
            [['showHide' => 'OptionButton'], [
                'value' => $this->getView()->translate('form-button-option_show-hide-group'),
                'class' => 'hideShowGroup',
            ]],
        ]);
    }

    public function addSponsorsGroup()
    {
        // adding subform
        foreach ($this->_bannerData as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_Sponsors_Banner([
                'data' => $v,
                'key' => $k,
            ]);
            $this->_subFormBanners[$k]->setElementsBelongTo('sponsors[' . $k . ']');
            $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
        }
    }

    public function isValid($data)
    {
        // remove old subforms
        foreach (array_keys($this->_subFormBanners) as $k) {
            $this->removeSubForm('banner-' . $k);
        }

        // add/edit all subforms
        foreach ($data[$this->_belong_to]['sponsors'] as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_Sponsors_Banner([
                'data' => $v,
                'key' => $k,
            ]);

            $this->_subFormBanners[$k]->setElementsBelongTo('sponsors[' . $k . ']');
            $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
        }

        return parent::isValid($data);
    }

    public function postIsValid($data)
    {
        // Upload settting
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        // date belong to
        $data = $this->getValues($this->_belong_to);

        // save an images and add to data array
        $i = 0;
        $newData = ['sponsors' => []];
        foreach ($data['sponsors'] as $k => $v) {
            $newData['sponsors'][$i] = $v;
            if (isset($files['image' . $k]) && '' !== $files['image' . $k]['name'] && 0 === $files['image' . $k]['error']) {
                $image = Service_Image::createImage($this->_event, [
                    'type' => Engine_Utils::_()->getFileExt($upload->getFileName('image' . $k, false)),
                    'name' => $upload->getFileName('image' . $k, false),
                    'source' => $upload->getFileName('image' . $k),
                ]);

                $image->save();
                $value = $image->getId();
                $newData['sponsors'][$i]['image' . $i] = $value;
            } else {
                $value = $this->_bannerData[$k]['image' . $k];
                $newData['sponsors'][$i]['image' . $i] = $value;
            }

            ++$i;
        }

        // zapis do bazy (event)
        $this->_event->setMapSponsorsRaw($data['map_sponsors']);
        $this->_event->getTranslation()->setMapSponsorsRaw($data['map_sponsors_translation']);

        $this->_event->setMapSponsors($newData['sponsors']);

        // save subforms
        $this->_event->save();

        // zapis do bazy (event_translation)
        $this->_event->Translations->save();

        return true;
    }

    protected function setEvent($event)
    {
        $this->_event = $event;
        $this->_bannerData = $this->_event->getMapSponsors();
    }
}
