<?php

/**
 * AuthPermission.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AuthPermission extends Table_AuthPermission
{
    const EVENT_ACCESS_TO_ALL = 'event_access-to-all';
    const EVENT_HAS_USER_NEW = 'event_admin-user_new';
    const EVENT_HAS_USER_EDIT = 'event_admin-user_edit';
    const EVENT_HAS_USER_LIST = 'event_admin-user_index';

    const ADMIN_USER_FORM_ROLE = 'admin_user_form_role';
    const USER_ADMIN_CAN_EDIT_ALL = 'user-admin_can-edit-all';
}