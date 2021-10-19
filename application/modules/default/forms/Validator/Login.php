<?php

class Form_Validator_Login extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notmatch';
    const DB_INVALID = 'databaseinvalid';

    /**
     * Db Adapter.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter;

    /**
     * Table name.
     *
     * @var string
     */
    protected $_tableName;

    /**
     * Login column.
     *
     * @var string
     */
    protected $_loginColumn;

    /**
     * Password column.
     *
     * @var string
     */
    protected $_passwordColumn;

    /**
     * Salting mechanism.
     *
     * @var string
     */
    protected $_saltingMechanism;

    /**
     * Name of the alternative checked column.
     *
     * @var string
     */
    protected $_checkColumn;

    /**
     * Auth adapter name.
     *
     * @var string
     */
    protected $_authAdapter = 'Engine_Auth_Adapter_DbTable';

    /**
     * Array of validation failure messages.
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::NOT_MATCH => 'Błędny login lub hasło',
        self::DB_INVALID => 'Błędny login lub hasło',
    ];

    /**
     * Constructor.
     *
     * @param array $params Params
     */
    public function __construct(array $params)
    {
        foreach ($params as $paramName => $paramValue) {
            $paramName = '_' . $paramName;
            if (property_exists($this, $paramName)) {
                $this->{$paramName} = $paramValue;
            }
        }
    }

    public function isValid($value, $context = null)
    {
        $auth = Zend_Auth::getInstance();
        $authAdapter = new $this->_authAdapter(
            $this->_tableName,
            $this->_loginColumn,
            $this->_passwordColumn
        );

        $authAdapter->setIdentity($context['login']);
        $authAdapter->setCredential($context['password']);

        try {
            $result = $auth->authenticate($authAdapter);
        } catch (Zend_Auth_Exception $e) {
            $this->_error(self::DB_INVALID);

            return false;
        }

        if ($result->isValid() && $auth->hasIdentity()) {
            $resultsObject = $authAdapter->getResultRowObject();
            $auth->getStorage()->write($resultsObject);

            // usunięcie innych
            $sessionOld = Doctrine_Query::create()
                ->from('Session s')
                ->where('s.id_user = ?', $resultsObject->getId())
                ->execute()
            ;
            $sessionOld->delete();

            // zapis zalogowanego usera do bazy
            $session = Session::find(session_id());
            if (!$session) {
                $session = new Session();
            }
            $session->id_user = $resultsObject->getId();
            $session->save();

            return true;
        }

        $this->_error(self::NOT_MATCH);

        return false;
    }
}
