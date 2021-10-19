<?php

/**
 * @author Piotr Wasilewski <pwasilewski@voxoft.com>
 */
class Developers_Form_Ftp extends Engine_FormAdmin
{
    protected $_belong_to = 'DevelopersFormFtp';

    protected $_tlabel = 'form_developers-ftp_';

    protected $_directoryList;

    public function init()
    {
        $this->_getDirectoryList();

        $submit = $this->createElement('submit', 'submit', [
            'label' => $this->_tlabel . 'send',
        ]);

        $this->addDisplayGroup([$submit], 'buttons');
        $this->addMainGroup();
    }

    public function addMainGroup()
    {
        $fields = null;
        $fields['directory_list'] = $this->createElement('multiCheckbox', 'directory-list', [
            'label' => $this->_tlabel . 'directory-list',
            'required' => true,
            'allowEmpty' => false,
            'multiOptions' => $this->_directoryList,
            'validators' => [
                ['NotEmpty', true],
            ],
        ]);

        $this->addDisplayGroup(
            $fields,
            'main',
            [
                'class' => 'group-wrapper group-main',
                'legend' => $this->_tlabel . 'group_info',
            ]
        );
    }

    protected function postIsValid($data)
    {
        $data = $data[$this->_belong_to];

        return true;
    }

    private function _getDirectoryList()
    {
        $directoriesRaw = json_decode(Engine_Variable::getInstance()->getVariable('ftp_directory_list'));

        // trzeba pozmieniac indeksy, bo inaczej Zend_Form_Element_MultiCheckbox przypisze wartosci
        // automatycznie nadanych indeksow
        foreach ($directoriesRaw as $directory) {
            $this->_directoryList[$directory] = $directory;
        }
    }
}
