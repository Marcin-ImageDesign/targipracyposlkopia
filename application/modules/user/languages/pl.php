<?php

return [
    //USER
    // 'Register' => 'Rejestracja',
    // 'Events' => 'Wydarzenia',
    // 'First name' => 'Imię',
    // 'Last name' => 'Nazwisko',
    // 'Company' => 'Firma',
    // 'Is active' => 'Czy aktywny',
    // 'Is confirm' => 'Czy potwierdzony',
    // 'Is blocked' => 'Czy zablokowany',
    // 'Taking company liabilities' => 'Zaciąganie zobowiązań firmy',
    // 'Phone' => 'Telefon',
    // 'Position' => 'Stanowisko',
    // 'Department' => 'Dział',
    // 'City' => 'Miasto',
    // 'Post code' => 'Kod pocztowy',
    // 'Street' => 'Ulica',
    // 'Bookkeeping email' => 'E-mail księgowości',
    // 'Company email' => 'E-mail firmy',
    // 'Company phone' => 'Telefon firmy',
    // 'Submit' => 'Zapisz',
    // 'This field is required' => 'Podanie pola jest wymagane',
    // 'Please input correct email address' => 'Proszę podać poprawny adres email',
    // 'Field can contain onlu numbers' => 'Pole może zaiwerać jedynie cyfry',
    // 'User settings' => 'Informacje o użytkowniku',
    // 'Detailed information' => 'Informacje szczegółowe',
    // 'Attributes' => 'Atrybuty',
    // 'Role' => 'Rola',
    // 'Insert new password to change' => 'Podaj nowe hasło, aby zmienić',
    // 'The two given tokens do not match' => 'Podane pola nie są identyczne.',
    // 'User managment'=>'Zarządzanie użytkownikami',
    // 'Surename'=>'Nazwisko',
    // 'Name'=>'Imię',
    // 'Company'=>'Firma',
    // 'Created'=>'Utworzony',
    // 'Is active'=>'Czy aktywny',
    // 'Client'=>'Klient',
    // 'Are you sure you want to delete this user?'=>'Czy na pewno chcesz usunąć tego użytkownika?',
    // 'Field required'=>'Pole wymagane',
    // 'Is banned'=>'Czy zablokowany',
    // 'Position'=>'Stanowisko',
    // 'Role'=>'Rola',
    // 'user'=>'Użytkownik',
    // 'admin'=>'Administrator',
    // 'organizer'=>'Organizator',
    // 'Add new user'=>'Dodaj użytkownika',
    // 'Select'=>'Wybierz',

    // USER - Frontent Account
    // 'Account' => 'Konto',
    // 'My account' => 'Moje konto',
    // 'Edit account' => 'Edycja konta',
    // 'Change password' => 'Zmiana hasła',
    // 'My calendar' => 'Mój kalendarz',
    // 'Password' => 'Hasło',
    // 'Repeat password' => 'Potwierdź hasło',
    // 'Base user'=>'Klient',

    // 'Required field' => 'Pole wymagane',

    'label_admin_user_role_' . UserRole::ROLE_USER => 'Zwiedzający',
    'label_admin_user_role_' . UserRole::ROLE_ADMIN => 'Gospodarz',
    'label_admin_user_role_' . UserRole::ROLE_EVENT_ORGANIZER => 'Organizator',
    'label_admin_user_role_' . UserRole::ROLE_ORGANIZER => 'Organizator',
    'label_admin_user_role_' . UserRole::ROLE_EXHIBITOR => 'Wystawca',
    'label_admin_user_role_' . UserRole::ROLE_MODERATOR => 'Moderator',

    'label_user_register_mail_subject' => 'Potwierdzenie rejestracji na stronie',
    'label_user_register_mail_title' => 'Potwierdzenie rejestracji na stronie',
    'label_user_register_mail_content' => 'Dziękujemy za rejestrację! Aby potwierdzić udział w wydarzeniu proszę kliknąć na poniższy link',
    'label_user_register_mail_link' => 'potwierdzam rejestrację &raquo;',

    'label_user_register_activate_account_error' => 'Podano błędny link aktywacyjny. Proszę kliknąć na link aktywacyjny otrzymany w e-mailu.',
    'label_user_register_activate_account_allready' => 'Konto zostało już wcześniej aktywowane.',
    'label_user_register_activate_account_ok' => 'Konto zostało aktywowane.',

    // 'route_activate_account' => 'aktywacja-konta',

    // 'breadcrumb_cms_admin-list' => 'Lista użytkowników',
    // 'breadcrumb_cms_admin-new' => 'Nowy użytkownik',
    // 'breadcrumb_cms_admin-edit' => 'Edycja użytkownika',

    'label_user_register_index_register' => 'Rejestracja',
    'label_user_register_index_required-field' => 'Pole wymagane',
    'label_user_register_thx_register' => 'Rejestracja',
    'label_user_register_thx_thank-you' => 'Dziękujemy',
    'label_user_register_thx_comm-registration-complete' => 'Dziękujemy, proces rejestracji został pomyślnie zakończony',
    'label_user_register_thx_comm-wait-for' => 'Czekaj',
    'label_user_register_thx_comm-you-account-activation' => 'na aktywację twojego konta',
    'label_user_register_thx_home-page' => 'Strona główna',
    'label_user_register_mail_admin_user-registration' => 'Rejestracja użytkownika na stronie',
    'label_user_register_mail_admin_date' => 'Data',
    'label_user_register_mail_admin_name-surname' => 'Imię i nazwisko',
    'label_user_register_mail_admin_email' => 'E-mail',

    //forularz rejestracji
    'form_user_register_first-name' => 'Imię',
    'form_user_register_last-name' => 'Nazwisko',
    'form_user_register_phone' => 'Telefon',
    'form_user_register_email' => 'E-mail',
    'form_user_register_password' => 'Hasło',
    'form_user_register_repeat-password' => 'Powtórz hasło',
    'form_user_register_com_prersonal-data' => 'Zgodnie z ustawą z dnia 29 sierpnia 1997 r. o ochronie danych osobowych
        (Dz.U. 1997r. Nr 133 poz. 833), wyrażam zgodę na przetważanie moich danych osobowych przez ...',
    'form_user_register_submit' => 'Wyślij',
    'label_user_account_edit-data' => 'Edytuj dane',
    'label_user_account_change-password' => 'Zmień hasło',
    'label_user_account_logout' => 'Wyloguj',
    'form_user_account_first-name' => 'Imię',
    'form_user_account_last-name' => 'Nazwisko',
    'form_user_account_phone' => 'Telefon',
    'form_user_account_submit' => 'Wyślij',
    'form_user_change-pass_pass-actual' => 'Obecne hasło',
    'form_user_change-pass_pass' => 'Nowe hasło',
    'form_user_change-pass_pass-repeat' => 'Powtórz hasło',
    'form_user_change-pass_submit' => 'Wyślij',
    'label_user_account_edit_edit-account' => 'Edycja konta',
    'label_user_account_change-pass_change-pass' => 'Zmiana hasła',
    'label_user_register_activate-account_link-inactive' => 'Ten link aktywacyjny został już wyskorzystany',
    'label_user_register_activate-account_user-banned' => 'Użytkownik został zbanowany',
];
