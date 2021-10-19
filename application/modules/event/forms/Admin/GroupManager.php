<?php

class Event_Form_Admin_GroupManager extends Engine_Form
{
    protected $_belong_to = 'EventFormAdminGroupManager';

    protected $_tlabel = 'form_event-admin-group_manager_';

    protected $_event;

    protected $_groupData;

    protected $_group;
    private $_subFormBanners = [];

    public function init()
    {
        //zaciągamy odpowiednią grupę
        $groups = $this->_event->getDataBannerGroups();

        $this->_groupData = $groups;

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'submit',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');

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
        if (!empty($this->_groupData[$this->_group])) {
            foreach ($this->_groupData[$this->_group] as $k => $v) {
                $this->_subFormBanners[$k] = new Event_Form_Admin_GroupManagerBanner([
                    'data' => $v,
                    'key' => $k,
                ]);
                $this->_subFormBanners[$k]->setElementsBelongTo($this->_group . '[' . $k . ']');
                $this->addSubForm($this->_subFormBanners[$k], 'banner-' . $k);
            }
        }
    }

    public function isValid($data)
    {
        // remove old subforms
        foreach (array_keys($this->_subFormBanners) as $k) {
            $this->removeSubForm('banner-' . $k);
        }
        // add/edit all subforms
        foreach ($data[$this->_belong_to][$this->_group] as $k => $v) {
            $this->_subFormBanners[$k] = new Event_Form_Admin_GroupManagerBanner([
                'data' => $v,
                'key' => $k,
            ]);

            $this->_subFormBanners[$k]->setElementsBelongTo($this->_group . '[' . $k . ']');
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
        $newData = [$this->_group => []];
        foreach ($data[$this->_group] as $k => $v) {
            $newData[$this->_group][$i] = $v;
            if (isset($files['image' . $k]) && '' !== $files['image' . $k]['name'] && 0 === $files['image' . $k]['error']) {
                $image = Service_Image::createImage($this->_event, [
                    'type' => Engine_Utils::_()->getFileExt($upload->getFileName('image' . $k, false)),
                    'name' => $upload->getFileName('image' . $k, false),
                    'source' => $upload->getFileName('image' . $k),
                ]);

                $image->save();
                $value = $image->getId();
                $newData[$this->_group][$i]['image' . $i] = $value;
            } else {
                $value = $this->_groupData[$this->_group][$k]['image' . $k];
                $newData[$this->_group][$i]['image' . $i] = $value;
            }

            ++$i;
        }

        // odczyt wszystkich aktualnie zapisanych grup banerowych
        $actualGroupBanners = $this->_event->getDataBannerGroups();

        // połaczenie obecnie edytowanej grupu z tymi co są zapisane
        $mergeData = array_merge($actualGroupBanners, $newData);

        $this->_event->setDataBannerGroups($mergeData);
        $this->_event->getTranslation();
        // save subforms
        $this->_event->save();

        return true;
    }

    protected function setEvent($event)
    {
        $this->_event = $event;
    }

    protected function setGroup($group)
    {
        $this->_group = $group;
    }
}
