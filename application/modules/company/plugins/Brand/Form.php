<?php
/**
 * Description of Form.
 *
 * @author marcin
 */
class Company_Plugin_Brand_Form
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
            $this->getBrandFromRequest();
        }
    }

    public function getSubForm()
    {
        $engineVariable = Engine_Variable::getInstance();
        $baseUserId = Zend_Auth::getInstance()->getIdentity()->BaseUser->getId();
        $brandMax = $engineVariable->getVariable(Variable::BRAND_MAX, $baseUserId);
        $brandsCollection = Doctrine_Query::create()
            ->from('Brand b')
            ->where('b.id_brand_parent IS NULL')
            ->execute()
        ;
        $brands = [];
        foreach ($brandsCollection as $brand) {
            $brands[$brand->getHash()] = $brand->getNameWithDashes();
            if ($brand->ChildBrands->count() > 0) {
                $brand->addChildrenToArrayHashed($brands);
            }
        }
        $brandsIn = [];
        if (!$this->model->isNew()) {
            $brandHasCompany = Doctrine_Query::create()
                ->from('BrandHasCompany bhc')
                ->where('bhc.id_company = ?', $this->model->getId())
                ->execute()
            ;
            foreach ($brandHasCompany as $brandHasCompanyElement) {
                $brandsIn[] = $brandHasCompanyElement->Brand->getHash();
            }
        }

        $elements = [];
        if ($brandMax > 0) {
            $iterator = 0;
            while ($iterator < $brandMax) {
                $key = 'brand_' . $iterator;
                $value = (isset($brandsIn[$iterator]) && !empty($brandsIn[$iterator])) ? $brandsIn[$iterator] : array_shift(array_values($brands));

                $element = $this->form->createElement('select', $key, [
                    'label' => 'Brand',
                    'required' => false,
                    'allowEmpty' => false,
                    'multiOptions' => $brands,
                    'value' => $value,
                    'class' => 'field-text field-text-small',
                    'decorators' => $this->form->elementDecoratorsCenturion,
                    'validators' => [['InArray', false, [
                        array_keys($brands),
                    ]]],
                ]);
                $elements[] = $element;
                ++$iterator;
            }

            $brand = new Zend_Form_SubForm();
            $brand->setDisableLoadDefaultDecorators(false);
            $brand->setDecorators($this->form->subFormDecoratorsCenturion);
            $brand->addDisplayGroup($elements, 'companyBrand_plugin_form');
            $group = $brand->getDisplayGroup('companyBrand_plugin_form');
            $group->clearDecorators();
            $group->setLegend('Brands');
            $group->addDecorators([
                'FormElements',
                ['Fieldset', ['class' => 'form-group']],
            ]);

            $brand->addAttribs(['class' => 'form-main']);

            $this->form->addSubForm($brand, 'companyBrand_plugin_form');
        }
    }

    public function getBrandFromRequest()
    {
        $this->form->companyBrand_plugin_form->populate($this->request->getParams());
    }

    public function preSave()
    {
        $engineVariable = Engine_Variable::getInstance();
        $baseUserId = Zend_Auth::getInstance()->getIdentity()->BaseUser->getId();
        $brandMax = $engineVariable->getVariable(Variable::BRAND_MAX, $baseUserId);
        $elements = [];
        $elementCollection = new Doctrine_Collection('BrandHasCompany', 'id_brand');
        $brandsForModel = $this->model->BrandHasCompany;
        $elementsToSave = new Doctrine_Collection('BrandHasCompany');
        $brandsForModel->merge($this->model->BrandHasCompany);
        if ($brandMax > 0) {
            $iterator = 0;
            while ($iterator < $brandMax) {
                $key = 'brand_' . $iterator;
                $elements[] = $key;
                ++$iterator;
            }
        }
        //tablica hashy branż z tablicy $_POST
        $valuesFromPost = [];
        foreach ($elements as $element) {
            $valuesFromPost[] = $this->form->companyBrand_plugin_form->{$element}->getValue();
        }

        $valuesFromPost = array_unique($valuesFromPost);

        foreach ($brandsForModel as $key => $brandHasCompany) {
            if (!in_array($brandHasCompany->Brand->getHash(), $valuesFromPost, true)) {
                $brandHasCompany->delete();
                unset($brandsForModel[$key]);
            }
        }
        $leftModelBrands = [];
        foreach ($brandsForModel as $key => $brandHasCompany) {
            $leftModelBrands[] = $brandHasCompany->Brand->getHash();
        }
        foreach ($valuesFromPost as $brandHash) {
            if (!in_array($brandHash, $leftModelBrands, true)) {
                $brandHasCompany = new BrandHasCompany();
                $brandHasCompany->Company = $this->model;
                $brandHasCompany->hash = Engine_Utils::getInstance()->getHash();
                $brandHasCompany->UserCreated = Zend_Auth::getInstance()->getIdentity();
                $brand = Brand::findOneByHash($brandHash);
                $brandHasCompany->Brand = $brand;
                $brandHasCompany->save();
                $brandsForModel->add($brandHasCompany);
            }
        }

        $this->model->save();
    }
}
