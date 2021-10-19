<?php
/**
 * Description of Form.
 *
 * @author marcin
 */
class Company_Plugin_Address_Form
{
    /**
     * @var Company
     */
    private $model;
    /**
     * @var Engine_Form
     */
    private $form;
    /**
     * @var Zend_Controller_Request_Abstract
     */
    private $request;

    public function __construct($model, $form)
    {
        $this->model = $model;
        $this->form = $form;
        $this->request = Zend_Controller_Front::getInstance()->getRequest();

        $this->getSubForm();

        if ($this->request->isPost() !== null) {
            $this->getAddressFromRequest();
        }
    }

    public function getSubForm()
    {
        $vStringCity = new Zend_Validate_StringLength(['min' => 3, 'max' => 255]);
        $vPostCode = new Zend_Validate_PostCode(['format' => '([0-9]{2}\-[0-9]{3})']);
        $city = $this->form->createElement('text', 'city', [
            'label' => 'City',
            'required' => true,
            'class' => 'field-text field-text-small',
            'allowEmpty' => false,
            'value' => $this->model->Address->AddressCity->getName(),
            'decorators' => $this->form->elementDecoratorsCenturion,
            'filters' => ['StringTrim'],
        ]);

        $city->addValidator($vStringCity);

        $provinces = Doctrine_Query::create()
            ->from('AddressProvince')
            ->execute()
            ->toKeyValueArray('id_address_province', 'name');

        $province = $this->form->createElement('select', 'province', [
            'label' => 'Province',
            'required' => true,
            'class' => 'field-text field-text-small',
            'filters' => ['StringTrim'],
            'value' => $this->model->Address->AddressProvince->getId(),
            'validators' => [['InArray', false, [array_keys($provinces)]]],
            'multiOptions' => $provinces,
        ]);

        $street = $this->form->createElement('text', 'street', [
            'label' => 'Street',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->model->Address->street,
            'class' => 'field-text field-text-small',
            'decorators' => $this->form->elementDecoratorsCenturion,
        ]);
        $street->addValidator($vStringCity);

        $number = $this->form->createElement('text', 'number', [
            'label' => 'Number',
            'required' => false,
            'allowEmpty' => true,
            'value' => $this->model->Address->number,
            'class' => 'field-text field-text-small',
            'decorators' => $this->form->elementDecoratorsCenturion,
        ]);

        $zip_code = $this->form->createElement('text', 'zip_code', [
            'label' => 'Post code',
            'required' => false,
            'alowEmpty' => false,
            'value' => $this->model->Address->zip_code,
            'class' => 'field-text field-text-small',
            'decorators' => $this->form->elementDecoratorsCenturion,
        ]);

        $zip_code->addValidator($vPostCode);

        $address = new Zend_Form_SubForm();
        $address->setDisableLoadDefaultDecorators(false);
        $address->setDecorators($this->form->subFormDecoratorsCenturion);
        $address->addDisplayGroup([$city, $province, $street, $number, $zip_code], 'companyAddress_plugin_form');
        $group = $address->getDisplayGroup('companyAddress_plugin_form');
        $group->clearDecorators();
        $group->setLegend('Address');
        $group->addDecorators([
            'FormElements',
            ['Fieldset', ['class' => 'form-group']],
        ]);

        $address->addAttribs(['class' => 'form-main']);

        $this->form->addSubForm($address, 'companyAddress_plugin_form');
    }

    public function getAddressFromRequest()
    {
        $this->form->companyAddress_plugin_form->populate($this->request->getParams());
    }

    public function preSave()
    {
        $provinceId = $this->form->companyAddress_plugin_form->province->getValue();
        $addressProvince = AddressProvince::find($provinceId);

        $cityName = $this->form->companyAddress_plugin_form->city->getValue();
        $addressCity = AddressCity::findOneByProvinceAndName($addressProvince, $cityName);

        if (false === $addressCity) {
            $addressCity = new AddressCity();
            $addressCity->AddressProvince = $addressProvince;
            $addressCity->save();
        }

        $addressCity->setName($cityName);

        $street = $this->form->companyAddress_plugin_form->street->getValue();
        $number = $this->form->companyAddress_plugin_form->number->getValue();
        $zip_code = $this->form->companyAddress_plugin_form->zip_code->getValue();
        $address = $this->model->Address;
        if (!$address) {
            $address = new Address();
        }

        $address->AddressCity = $addressCity;
        $address->AddressProvince = $addressProvince;
        $address->zip_code = $zip_code;
        $address->street = $street;
        $address->number = $number;
        $this->model->Address = $address;
        $this->model->Address->save();
    }
}
