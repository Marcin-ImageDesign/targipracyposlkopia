<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Robert Rogiński <rroginski@voxcommerce.pl>
 * Date: 20.03.13
 * Time: 10:29.
 */
class Developers_AdminController extends Engine_Controller_Admin
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!DEBUG) {
            $this->forward403Unless('Access only for developers');
        }
    }

    public function indexAction()
    {
        $version = '';
        $version_filename = ROOT_PATH . DS . 'version.txt';
        if (file_exists($version_filename)) {
            $version = file_get_contents($version_filename);
        }

        if ($this->hasParam('goToAbsoluteUri')) {
            $this->_redirector->setUseAbsoluteUri(true);
            $this->_redirector->gotoUrlAndExit($this->view->url() . '?redirectToAbsoluteUriDone=1');
        }

        $file = APPLICATION_PATH . '/settings/database.php';
        $db_conn = include $file;
        $this->view->db_conn = $db_conn;
        $this->view->version = $version;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_developers_settings'));
    }

    public function adminerAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        include __DIR__ . '/adminer.php';
        exit();
    }

    public function changelogAction()
    {
        $changelog = '';
        $filename = ROOT_PATH . DS . 'CHANGELOG.md';
        if (file_exists($filename)) {
            $changelog = file_get_contents($filename);
        }

        $this->view->changelog = $changelog;
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_developers_settings'));
    }

    public function clearCacheAction()
    {
        $files_removed = [];

        $cache_dir = APPLICATION_TMP . DS . 'cache';
        $open_dir = new DirectoryIterator(APPLICATION_TMP . DS . 'cache');
        foreach ($open_dir as $file) {
            $filename = (string) $file;
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }

            if (false !== mb_strpos($filename, 'zend_cache')) {
                $files_removed[] = $filename;
                unlink($cache_dir . DS . $filename);
            }
        }

        $this->view->files_removed = $files_removed;
    }

    public function logsAction()
    {
        $this->view->placeholder('headling_1_content')->set($this->view->translate('h1_cms_developers_logs'));
    }

    public function getFileAction()
    {
        $file = $this->getParam('file');
        $display = $this->getParam('display');
        $this->forward404Unless($file);

        $filename = ROOT_PATH . DS . $file;
        if (file_exists($filename)) {
            if (empty($display)) {
                $this->_helper->layout()->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);

                header('Cache-control: private');
                header('Content-Type: application/json; charset=utf-8');
                header('Content-Length: ' . filesize($filename));
                header('Content-Transfer-Encoding: binary');
                header('Content-Disposition: attachment; filename="' . $file . '"');
            } else {
                $this->_helper->viewRenderer('logs');
            }

            $limit = $this->getParam('limit', 1);
            $handle = fopen($filename, 'r');
            $display_buffer = '';
            if (!empty($limit)) {
                fseek($handle, ftell($handle) - ($limit * 1024 * 1024), SEEK_END);
            }
            if ($handle) {
                while (false !== ($buffer = fgets($handle, 4096))) {
                    if (!empty($display)) {
                        echo $buffer;
                    } else {
                        $display_buffer .= $buffer;
                    }
                }
                if (!feof($handle)) {
                    echo "Error: unexpected fgets() fail\n";
                }
                fclose($handle);
            }
            $this->view->display_buffer = $display_buffer;
        }

        $this->view->file = $file;
        $this->view->filename = $filename;
    }

    public function generateHallMapAction()
    {
        $hall_map = ['attribs' => [], 'items' => []];

        //ustawienie atrbutów
        $hall_map['attribs'] = ['id' => 'mymap', 'name' => 'mymap'];

        //items
        $hall_map['items'] = [
            [
                'shape' => 'poly',
                'alt' => 'www.eko-serwis.pl',
                'title' => 'Eko serwis',
                'coords' => '241,401,256,481,418,416,406,338',
                'href' => 'http://www.eko-serwis.pl/',
                'target' => '_blank',
            ],
            [
                'shape' => 'poly',
                'alt' => 'www.osadadopiewiec.pl',
                'title' => 'Osada Dopiewiec',
                'coords' => '461,313,475,394,644,328,636,246',
                'href' => 'http://www.osadadopiewiec.pl/',
                'target' => '_blank',
            ],
        ];

        $save = $this->_getParam('save') ? $this->_getParam('save') : '';
        $id_map = $this->_getParam('id') ? $this->_getParam('id') : '';
        $this->view->map_saved = false;
        $this->view->error = false;

        if (1 === $save && !empty($id_map)) {
            $eventHallMap = EventHallMap::find($id_map);
            if ($eventHallMap) {
                $eventHallMap->setHallMap($hall_map);
                $eventHallMap->save();
                $this->view->map_saved = true;
            }
            $this->view->error = true;
        } else {
            $this->view->json_hall_map = json_encode($hall_map);
            $this->view->hall_map = $hall_map;
        }
    }

    public function generateSponsorsAction()
    {
        $sponsors = [];
        $names = [];
        $sponsors = [
            [
                'link' => 'http://www.hotelgdanski.pl/',
                'target' => '_blank',
                'image' => '/private/base_user/_expo/_repository/Targi_Turystyczne/Sponsorzy/gdtlogo-35.jpg',
                'order' => '0',
                'attr' => [
                    'style' => 'margin-top:3px;margin-left:50px;',
                ],
            ],
            [
                'link' => 'http://www.termyuniejow.pl',
                'target' => '',
                'image' => '/private/base_user/_expo/_repository/Targi_Turystyczne/Sponsorzy/termy-uniejow-logo-35.jpg',
                'order' => '1',
                'attr' => [
                    'style' => 'margin-top:3px;margin-left:20px;',
                ],
            ],
            [
                'link' => 'http://www.mccomp.pl/',
                'target' => '_blank',
                'image' => '/private/base_user/_expo/_repository/Targi_Turystyczne/Sponsorzy/mc-comp-logo-35.png',
                'order' => '2',
                'attr' => [
                    'style' => 'margin-top:3px;margin-left:20px;',
                ],
            ],
            [
                'link' => 'http://blablacar.pl',
                'target' => '',
                'image' => '/private/base_user/_expo/_repository/Targi_Turystyczne/Sponsorzy/blablacar-35.png',
                'order' => '3',
                'attr' => [
                    'style' => 'margin-top:3px;margin-left:20px;',
                ],
            ],
            [
                'link' => 'http://podroze.onet.pl',
                'target' => '',
                'image' => '/private/base_user/_expo/_repository/Targi_Turystyczne/Sponsorzy/onetpodroze-logo1-35.png',
                'order' => '4',
                'attr' => [
                    'style' => 'margin-top:3px;margin-left:20px;',
                ],
            ],
            //            array(
            //                'link' => 'http://www.nauka.gov.pl/',
            //                'target' => '_blank',
            //                'image' => '/private/base_user/_expo/_repository/Targi_Edukacja/Sponsorzy/mnisw.png',
            //                'order' => '5',
            //                'attr' => array(
            //                    'style' => 'margin-top:3px;'
            //                )
            //            ),
            // array(
            //     'link' => 'http://ladnydom.pl',
            //     'target' => '_blank',
            //     'image' => '/_images/frontend/default/image_test/ladny-dom_logo.png',
            //     'order' => '6',
            //     'attr' => array(
            //         'style' => 'margin-top:3px;'
            //     )
            // )
        ];

        $names = [
            ['name' => 'Długie Ogrody'],
            ['name' => 'Termy Uniejów'],
            ['name' => 'Mc COMP'],
            ['name' => 'Bla Bla Car'],
            ['name' => 'Onet Podróże'],
            //            array('name' => 'Ministerstwo Nauki i Szkolnictwa Wyższego'),
            // array('name' => 'Ładny Dom budowa i remont'),
        ];

        $save = $this->_getParam('save') ? $this->_getParam('save') : '';
        $id_event = $this->_getParam('id_event') ? $this->_getParam('id_event') : '';
        $this->view->map_saved = false;
        $this->view->error = false;

        if (1 === $save && !empty($id_event)) {
            $event = Event::find($id_event, $this->getSelectedLanguage());
            $event->setMapSponsors($sponsors);
            $event->Translation->setMapSponsors($names);
            $event->save();
            $this->view->map_saved = true;
            $this->view->error = true;
        } else {
            $this->view->json_sponsors = json_encode($sponsors);
            $this->view->json_names = json_encode($names);
            $this->view->sponsors = $sponsors;
            $this->view->names = $names;
        }
    }

    // public function generateHomePageAction()
    // {
    //     $home_page = array(
    //         'box' => array(),
    //         'sponsors' => array(),
    //     );

    //     $home_page['box']['2'] = array(
    //         'class' => '',
    //         'title' => 'Wejście',
    //         'link' => '/wydarzenie/targi-dom-i-ogrod/artykuł/czy-oplaca-sie-nam-posrednictwo-biur-nieruchomosci',
    //         'target' => '',
    //         'desc' => 'Czy opłaca się nam pośrednictwo biur nieruchomości?',
    //         'html' => '',
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'html_wrap' => array(
    //             'tag' => 'div',
    //             'attr' => array('class' => 'article', 'style' => "background:url('/_images/frontend/default/article1.jpg') no-repeat scroll center center"),

    //         ),
    //         'attr' => array(),
    //         'attr_a' => array(),
    //     );

    //     $home_page['sponsors']['1'] = array(
    //         'class' => 'general',
    //         'title' => 'Sponsor',
    //         'link' => false,
    //         'target' => '',
    //         'html' => '<a title="" class="ladnydom" target="_blank" href="http://ladnydom.pl"></a><a title="" class="eogrody" target="_blank" href="http://e-ogrody.pl"></a>',
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'html_wrap' => array(
    //             'tag' => 'div',
    //             'attr' => array('class' => 'big_box_main round', 'style' => "background-color:#fff"),

    //         ),
    //         'attr' => array(),
    //         'attr_a' => array(),
    //     );

    //     $home_page['sponsors']['2'] = array(
    //         'class' => 'middle_aligned',
    //         'title' => 'Biznes.pl',
    //         'link' => 'http://biznes.pl/',
    //         'target' => '_blank',
    //         'html' => "<img style='width:93px;height:45px;margin-top:12%' src='/_images/frontend/default/biznespl.jpg' />",
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'attr' => array(),
    //         'attr_a' => array(),
    //     );

    //     $home_page['sponsors']['3'] = array(
    //         'class' => 'middle_aligned',
    //         'title' => 'Towarzystwo Ubezpieczeń Wzajemnych',
    //         'link' => '/wydarzenie/targi-dom-i-ogrod/stoisko/34deb1543115857a88c8969b74e27896',
    //         'target' => '',
    //         'html' => '<img style="width:181px;height:45px;margin-top:12%;" src="/_images/frontend/default/tuw.jpg" />',
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'attr' => array(),
    //         'attr_a' => array(),
    //     );

    //     $home_page['sponsors']['4'] = array(
    //         'class' => 'middle_aligned',
    //         'title' => 'Hormann',
    //         'link' => '/wydarzenie/targi-dom-i-ogrod/stoisko/af1fa4d18fdb743d6b6da0a9679a85ad',
    //         'target' => '',
    //         'html' => '<img style="width:181px;height:45px;margin-top:12%;" src="/_images/frontend/default/hormann.jpg" />',
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'attr' => array(),
    //         'attr_a' => array('style' => 'background:#263C6D;'),
    //     );

    //     $home_page['sponsors']['5'] = array(
    //         'class' => 'middle_aligned',
    //         'title' => 'Solgaz',
    //         'link' => '/wydarzenie/targi-dom-i-ogrod/stoisko/e53fe27cf8964fbf379cb63b61957c2c',
    //         'target' => '',
    //         'html' => '<img style="width:181px;height:45px;margin-top:12%;" src="/_images/frontend/default/solgaz.jpg" />',
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'attr' => array(),
    //         'attr_a' => array(),
    //     );

    //     $home_page['sponsors']['6'] = array(
    //         'class' => 'middle_aligned',
    //         'title' => 'oknoplus',
    //         'link' => '/wydarzenie/targi-dom-i-ogrod/stoisko/9742afb85e235340bf920077745a5c3e',
    //         'target' => '',
    //         'html' => '<img style="width:181px;height:45px;margin-top:12%;" src="/_images/frontend/default/oknoplus.jpg" />',
    //         'image' => array(
    //             'id' => null,
    //             'width' => null,
    //             'height' => null,
    //         ),
    //         'attr' => array(),
    //         'attr_a' => array(),
    //     );

    //     $page_save = false;

    //     if ($this->hasParam('save') && $this->hasParam('id_home_page')){
    //         $homePage = HomePage::find($this->getParam('id_home_page'), $this->getSelectedLanguage());
    //         if ($homePage){
    //             $homePage->setPageData($home_page);
    //             $homePage->save();
    //             $page_save = true;
    //         }
    //     }

    //     $this->view->home_page = $home_page;
    //     $this->view->page_save = $page_save;
    // }

    public function generateHomePageAction()
    {
        $home_page = [
            'box' => [],
            'sponsors' => [],
        ];

        $home_page['box']['1'] = [
            'class' => '',
            'title' => 'Wejście',
            'link' => '/wydarzenie/targi-turystyka-i-hobby/hala-targowa',
            'target' => '_blank',
            'html' => "<div class='enter'></div>",
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'attr' => [],
            'attr_a' => ['style' => ''],
        ];

        $home_page['box']['2'] = [
            'class' => '',
            'title' => 'Jak wybrać biuro podróży?',
            'link' => '#',
            'target' => '',
            'desc' => 'Jak wybrać biuro podróży?',
            'html' => '',
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'html_wrap' => [
                'tag' => 'div',
                'attr' => ['class' => 'article', 'style' => "background:url('/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/article.png')"],
            ],
            'attr' => [],
            'attr_a' => [],
        ];

        $home_page['sponsors']['1'] = [
            'class' => 'general',

            'title' => 'Sponsor',
            'link' => false,
            'target' => '',
            'html' => "<a style='float:left;margin-right:30px;margin-top:21%;height:65px;' href='/wydarzenie/targi-turystyka-i-hobby/stoisko/gdanski-dom-turystyczny-dlugi-ogrody'><img src='/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/gdtlogo.jpg' /></a>",
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'html_wrap' => [
                'tag' => 'div',
                'attr' => ['class' => 'big_box_main round', 'style' => 'background-color:#fff;padding:0 30px'],
            ],
            'attr' => [],
            'attr_a' => [],
        ];

        $home_page['sponsors']['2'] = [
            'class' => '',
            'title' => 'Termy Uniejów',
            'link' => false,
            'target' => '_blank',
            'html' => "<div class='big_box_main round'><a href='http://www.termyuniejow.pl/' class=''><img style='' src='/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/termy-uniejow-logo.jpg' /></a></div>",
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'attr' => [],
            'attr_a' => ['class' => 'round', 'style' => 'height: 208px;'],
        ];

        $home_page['sponsors']['3'] = [
            'class' => '',
            'title' => 'MC Comp',
            'link' => 'http://www.mccomp.pl/',
            'target' => '_blank',
            'html' => "<img style='width:auto; margin-top: 15px;' src='/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/mccomp_logo.png' />",
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'attr' => [],
            'attr_a' => ['style' => 'background:#44c1e2;'],
        ];

        $home_page['sponsors']['4'] = [
            'class' => '',
            'title' => 'BlaBlaCar',
            'link' => 'http://blablacar.pl',
            'target' => '_blank',
            'html' => '<img style="width:auto;margin-top:20px;" src="/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/blablacar--logo.jpeg" />',
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'attr' => [],
            'attr_a' => ['class' => 'round'],
        ];

        $home_page['sponsors']['5'] = [
            'class' => '',
            'title' => 'Onet Podróże',
            'link' => 'http://podroze.onet.pl/',
            'target' => '_blank',
            'html' => '<img style="width:auto; margin-top:2px;" src="/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/onetpodroze-logo1.png" />',
            'image' => [
                'id' => null,
                'width' => null,
                'height' => null,
            ],
            'attr' => [],
            'attr_a' => ['class' => 'round', 'style' => 'background-color:#DE9900;'],
        ];

//        $home_page['sponsors']['6'] = array(
//            'class' => '',
//            'title' => 'Onet',
//            'link' => 'http://onet.pl/',
//            'target' => '_blank',
//            'html' => '<img style="width:auto" src="/private/base_user/_expo/_repository/Targi_Turystyczne/Strona_glowna/onet-logo.png" />',
//            'image' => array(
//                'id' => null,
//                'width' => null,
//                'height' => null,
//            ),
//            'attr' => array(),
//            'attr_a' => array(),
//        );

        $page_save = false;
        if ($this->hasParam('save') && $this->hasParam('id_home_page')) {
            $homePage = HomePage::find($this->getParam('id_home_page'), $this->getSelectedLanguage());
            if ($homePage) {
                $homePage->setPageData($home_page);
                $homePage->save();
                $page_save = true;
            }
        }

        $this->view->home_page = $home_page;
        $this->view->page_save = $page_save;
    }

    public function ftpAction()
    {
        // nowy obiekt FTP
        $ftp = new Engine_Ftp();

        // formularz do wysyłki katalogów przez FTP
        $formFtp = new Developers_Form_Ftp();

        // walidacja formularza
        if ($this->_request->isPost() && $formFtp->isValid($this->_request->getPost())) {
            // połączenie z serwerem i logowanie
            $ftp->establishConnection();
            $ftp->sendDirectoryList($this->_request->getParam('directorylist'));
            $this->view->copiedFiles = $ftp->getCopiedFiles();
            // $this->_redirector->gotoRouteAndExit(array(),'developers_admin_ftp');
        }

        $this->view->ftp = $ftp;
        $this->view->formFtp = $formFtp;
    }

    public function variablesAction()
    {
        $editableVariables = $this->_getEditableVariables();

        $editableVariablesForm = new Developers_Form_EditableVariables(['variablesList' => $editableVariables]);
        $editableVariablesForm->populate($this->_getVariablesValues());

        if ($this->_request->isPost() && $editableVariablesForm->isValid($this->_request->getPost())) {
            $this->_flash->succes->addMessage('Save successfully completed');
            $this->_redirector->gotoRouteAndExit([], 'developers_admin_variables');
        }

        $this->view->editableVariablesForm = $editableVariablesForm;
    }

    public function setStandIconsAction()
    {
        $stand = ExhibStandViewImage::findOneByHash('4b08c78f46c46464f47a79ae425b3287');
        // var_dump($stand->toArray());

        //vip
        // $data_icon = array(
        //     'info' => array(
        //         'x' => $stand->icon_info_x,
        //         'y' => $stand->icon_info_y,
        //         'name' => 'label_event_stand_index_info',
        //         'route' => 'event_stand_info',
        //         'class' => 'pBargain',
        //     ),
        //     'contact' => array(
        //         'x' => $stand->icon_contact_x,
        //         'y' => $stand->icon_contact_y,
        //         'name' => 'label_event_stand_index_contact',
        //         'route' => 'event_stand_contact',
        //         'class' => 'pContact',
        //     ),
        //     'bargains' => array(
        //         'x' => $stand->icon_bargains_x,
        //         'y' => $stand->icon_bargains_y,
        //         'name' => 'label_event_stand_index_bargains',
        //         'route' => 'event_stand-offer_index_promoted',
        //         'class' => 'pBargains',
        //     ),
        //     'products' => array(
        //         'x' => $stand->icon_products_x,
        //         'y' => $stand->icon_products_y,
        //         'name' => 'label_event_stand_index_products',
        //         'route' => 'event_stand-offer_index',
        //         'class' => 'pProducts',
        //     ),
        //     'video' => array(
        //         'x' => $stand->icon_video_x,
        //         'y' => $stand->icon_video_y,
        //         'name' => 'label_event_stand_index_video',
        //         'route' => 'event_stand-video_index',
        //         'class' => 'pVideo',
        //     ),
        //     'catalogue' => array(
        //         'x' => $stand->icon_catalogue_x,
        //         'y' => $stand->icon_catalogue_y,
        //         'name' => 'label_event_stand_index_download-files',
        //         'route' => 'event_stand_files',
        //         'class' => 'pFiles',
        //     ),
        // );

        // $data_banner = array(
        //     'top' => array(
        //         'x' => 376,
        //         'y' => 75,
        //         'width' => null,
        //         'height' => null,
        //         'perspective' => null,
        //         'crop' => null,
        //         'style' => '',
        //     ),
        //     'desk' => array(
        //         'x' => 455,
        //         'y' => 393,
        //         'width' => null,
        //         'height' => null,
        //         'perspective' => null,
        //         'crop' => null,
        //         'style' => 'z-index:7;'
        //     ),
        // );

        // $data_stand = array(
        //     'hostess' => array(
        //         'x' => $stand->stand_hostess_pos_x,
        //         'y' => $stand->stand_hostess_pos_y,
        //         'style' => 'height:203px;'
        //     ),
        //     'desk' => array(
        //         'x' => 420,
        //         'y' => 318,
        //         'src' => '/_images/frontend/default/stand-elems/desk2-1.png',
        //         'style' => 'width:181px;height:147px;z-index: 6;'
        //     ),
        //     'tv_desk' => array(
        //         'x' => 435,
        //         'y' => 172,
        //         'src' => '/_images/frontend/default/stand-elems/tv2-1.png',
        //         'style' => 'width:158px;height:147px;z-index: 3;'
        //     ),
        //     'tv' => array(
        //         'x' => 446,
        //         'y' => 191,
        //         'style' => ' width: 136px;height: 70px;'
        //     ),
        // );

        //regional
        // $data_icon = array(
        //     'info' => array(
        //         'x' => 276,
        //         'y' => 349,
        //         'name' => 'label_event_stand_index_info',
        //         'route' => 'event_stand_info',
        //         'class' => 'pBargain',
        //     ),
        //     'contact' => array(
        //         'x' => 406,
        //         'y' => 349,
        //         'name' => 'label_event_stand_index_contact',
        //         'route' => 'event_stand_contact',
        //         'class' => 'pContact',
        //     ),
        //     'bargains' => array(
        //         'x' => 690,
        //         'y' => 255,
        //         'name' => 'label_event_stand_index_bargains',
        //         'route' => 'event_stand-offer_index_promoted',
        //         'class' => 'pBargains',
        //     ),
        //     'products' => array(
        //         'x' => 690,
        //         'y' => 370,
        //         'name' => 'label_event_stand_index_products',
        //         'route' => 'event_stand-offer_index',
        //         'class' => 'pProducts',
        //     ),
        //     'video' => array(
        //         'x' => 275,
        //         'y' => 235,
        //         'name' => 'label_event_stand_index_video',
        //         'route' => 'event_stand-video_index',
        //         'class' => 'pVideo',
        //     ),
        //     'catalogue' => array(
        //         'x' => 765,
        //         'y' => 325,
        //         'name' => 'label_event_stand_index_download-files',
        //         'route' => 'event_stand_files',
        //         'class' => 'pFiles',
        //     ),
        // );

        // $data_banner = array(
        //     'top' => array(
        //         'x' => 280,
        //         'y' => 110,
        //         'width' => 440,
        //         'height' => 90,
        //         'perspective' => null,
        //         'crop' => null,
        //         'style' => '',
        //     ),
        //     'desk' => array(
        //         'x' => 324,
        //         'y' => 410,
        //         'width' => 67,
        //         'height' => 38,
        //         'perspective' => null,
        //         'crop' => null,
        //         'style' => 'z-index:4;'
        //     ),
        // );

        // $data_stand = array(
        //     'hostess' => array(
        //         'x' => 345,
        //         'y' => 310,
        //         'style' => 'height:180px;',
        //         'container_style' => 'height:150px;'
        //     ),
        //     'transparent' => array(
        //         'style' => 'margin-left:330px;margin-top:309px;width:75px;height:85px;',
        //     ),
        //     'talk' => array(
        //         'style' => 'margin-left:380px;margin-top:230px;',
        //     ),
        //     'desk' => array(
        //         'x' => 286,
        //         'y' => 363,
        //         'src' => '/_images/frontend/default/stand-elems/desk3.png',
        //         'style' => ' width:151px;height:106px;z-index: 4;'
        //     ),
        //     'tv' => array(
        //         'x' => 311,
        //         'y' => 265,
        //         'style' => 'z-index: 3;width: 100px;height: 51px;'
        //     ),
        // );

        // standard
        // $data_icon = array(
        //     'info' => array(
        //         'x' => 658,
        //         'y' => 323,
        //         'name' => 'label_event_stand_index_info',
        //         'route' => 'event_stand_info',
        //         'class' => 'pBargain',
        //     ),
        //     'contact' => array(
        //         'x' => 785,
        //         'y' => 323,
        //         'name' => 'label_event_stand_index_contact',
        //         'route' => 'event_stand_contact',
        //         'class' => 'pContact',
        //     ),
        //     'bargains' => array(
        //         'x' => 230,
        //         'y' => 310,
        //         'name' => 'label_event_stand_index_bargains',
        //         'route' => 'event_stand-offer_index_promoted',
        //         'class' => 'pBargains',
        //     ),
        //     'products' => array(
        //         'x' => 160,
        //         'y' => 370,
        //         'name' => 'label_event_stand_index_products',
        //         'route' => 'event_stand-offer_index',
        //         'class' => 'pProducts',
        //     ),
        //     'video' => array(
        //         'x' => 572,
        //         'y' => 222,
        //         'name' => 'label_event_stand_index_video',
        //         'route' => 'event_stand-video_index',
        //         'class' => 'pVideo',
        //     ),
        //     'catalogue' => array(
        //         'x' =>230,
        //         'y' => 420,
        //         'name' => 'label_event_stand_index_download-files',
        //         'route' => 'event_stand_files',
        //         'class' => 'pFiles',
        //     ),
        // );

        // $data_banner = array(
        //     'top' => array(
        //         'x' => 255,
        //         'y' => 80,
        //         'width' => 520,
        //         'height' => 100,
        //         'perspective' => null,
        //         'crop' => null,
        //         'style' => '',
        //     ),
        //     'desk' => array(
        //         'x' => 690,
        //         'y' => 390,
        //         'width' => 115,
        //         'height' => 65,
        //         'perspective' => null,
        //         'crop' => null,
        //         'style' => 'z-index: 4;'
        //     ),
        // );

        // $data_stand = array(
        //     'hostess' => array(
        //         'x' => 722,
        //         'y' => 280,
        //         'style' => 'height:203px;'
        //     ),
        //     'transparent' => array(
        //         'style' => 'margin-left:708px;margin-top:283px;width:77px;height:85px;',
        //     ),
        //     'talk' => array(
        //         'style' => 'margin-left:740px;',
        //     ),
        //     'desk' => array(
        //         'x' => 666,
        //         'y' => 328,
        //         'src' => '/_images/frontend/default/stand-elems/desk1-flat.png',
        //         'style' => 'width:168px;height:151px;z-index: 4;'
        //     ),
        //     'tv' => array(
        //         'x' => 468,
        //         'y' => 256,
        //         'style' => 'z-index: 4;width: 110px;height: 58px;'
        //     ),
        // );
        //
        // var_dump($data_icon, $data_banner, $data_stand);

        // $stand->setDataIcon($data_icon);
        // $stand->setDataBanner($data_banner);
        // $stand->setDataStand($data_stand);
        // $stand->save();

        // var_dump($data_icon, $data_banner, $data_stand);die;
    }

    public function addIconToStandAction()
    {
        $id_from = $this->_getParam('id_from');
        $id_to = $this->_getParam('id_to');
        $x = $this->_getParam('x');
        $y = $this->_getParam('y');

        $standLevelViewQuery = Doctrine_Query::create()
            ->from('ExhibStandViewImage esvi')
            ->where('esvi.id_exhib_stand_view_image BETWEEN ? AND ?', [$id_from, $id_to])
        ;

        $standLevelViewList = $standLevelViewQuery->execute();

        $set_data_icon = [
            'x' => $x,
            'y' => $y,
            'name' => 'label_event_stand_index_www-site',
            'route' => 'event_stand_www-site',
            'class' => 'pWww contentLoad',
        ];

        foreach ($standLevelViewList as $key => $standLevelView) {
            $data_icon = json_decode($standLevelView->data_icon, true);
            $data_icon['site-www'] = $set_data_icon;
            $data_icon = json_encode($data_icon);
            $standLevelView->data_icon = $data_icon;
            $standLevelView->save();
        }
    }

    public function updateAclResourcesAction()
    {
        $currentResources = Doctrine::getTable('AuthResource')->findAll()->toKeyValueArray('name', 'name');
        $foundResources = $this->scanForResources();

        $this->view->missingFromDb = array_diff(array_keys($foundResources), $currentResources);
        $this->view->missingFromApp = array_diff($currentResources, array_keys($foundResources));
    }

    public function addMissingResourcesAction()
    {
        $currentResources = Doctrine::getTable('AuthResource')->findAll()->toKeyValueArray('name', 'name');
        $foundResources = $this->scanForResources();

        $missingFromDb = array_diff(array_keys($foundResources), $currentResources);
        // add missing
        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        $conn->beginTransaction();

        try {
            foreach ($missingFromDb as $name) {
                $authResource = new AuthResource();
                $authResource->name = $name;
                $parent = $foundResources[$name];
                if ($parent) {
                    $authResource->id_parent = AuthResource::findOneByName($parent)->id_auth_resource;
                }
                $authResource->save();
                echo 'Addedd missing resource ' . $name . (($parent) ? ' with parent ' . $parent : '') . ' to DB.<br/>';
            }
        } catch (Exception $e) {
            $conn->rollback();

            throw $e;
        }
        $conn->commit();
    }

    public function removeMissingResourcesAction()
    {
        $currentResources = Doctrine::getTable('AuthResource')->findAll()->toKeyValueArray('id_auth_resource', 'name');
        $foundResources = $this->scanForResources();
        $missingFromApp = array_diff($currentResources, array_keys($foundResources));

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        $conn->beginTransaction();

        try {
            foreach ($missingFromApp as $id => $name) {
                // remove permissions
                $permissions = Doctrine::getTable('AuthPermission')->findBy('id_auth_resource', $id);
                foreach ($permissions as $row) {
                    $row->delete();
                }

                Doctrine::getTable('AuthResource')->findOneBy('id_auth_resource', $id)->delete();
                echo 'Removed resource ' . $name . ' from DB.<br/>';
            }
        } catch (Exception $e) {
            $conn->rollback();

            throw $e;
        }
        $conn->commit();
    }

    private function _getVariablesValues()
    {
        // pobranie wszystkich dostępnych zmiennych z bazy danych - default
        $variablesAll = [];
        $variablesAll += Engine_Variable::getInstance()->getVariables();

        // pobranie zmiennych dla baseUser = 9 (chat Rhino)
        $variablesAll += Engine_Variable::getInstance()->getVariables(9);

        return $variablesAll;
    }

    private function _getEditableVariables()
    {
        // lista zmiennych, które mają być dostepne do edycji w admin'ie
        return [
            'ftp_group' => [
                [
                    'name' => 'ftp_server',
                    'type' => 'text',
                ],
                [
                    'name' => 'ftp_user',
                    'type' => 'text',
                ],
                [
                    'name' => 'ftp_password',
                    'type' => 'text',
                ],
                [
                    'name' => 'ftp_port',
                    'type' => 'text',
                ],
                [
                    'name' => 'ftp_directory_list',
                    'type' => 'textarea',
                ],
                [
                    'name' => 'ftp_destination_prefix',
                    'type' => 'text',
                ],
            ],
            'chat_group' => [
                [
                    'name' => 'rhino_url',
                    'type' => 'text',
                    'required' => true,
                    'allowEmpty' => false,
                    'validators' => [],
                ],
            ],
            'google_analytics_group' => [
                [
                    'name' => 'google_analytics',
                    'type' => 'textarea',
                ],
            ],
            'email_group' => [
                [
                    'name' => 'email_send_on',
                    'type' => 'checkbox',
                ],
                [
                    'name' => 'email_smtp_on',
                    'type' => 'checkbox',
                ],
                [
                    'name' => 'email_transporter',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_user',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_password',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_from',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_to',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_name',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_cc',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_bcc',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_port',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_ssl',
                    'type' => 'text',
                ],
                [
                    'name' => 'email_cron_on',
                    'type' => 'checkbox',
                ],
            ],
            'robots_group' => [
                [
                    'name' => 'robots',
                    'type' => 'textarea',
                ],
            ],
        ];
    }

    private function scanForResources()
    {
        $utils = Engine_Utils::getInstance();
        $frontController = Zend_Controller_Front::getInstance();
        $modulesDir = $frontController->getModuleDirectory('default') . '/../';

        $foundResources = [];
        foreach (scandir($modulesDir) as $moduleName) {
            if ('.' === $moduleName || '..' === $moduleName) {
                continue;
            }
            $foundResources[$moduleName] = null;

            $controllerPath = $frontController->getControllerDirectory($moduleName);
            foreach (scandir($controllerPath) as $file) {
                if (mb_strpos($file, '.php') === mb_strlen($file) - 4) {
                    $controller = mb_substr($file, 0, -14);
                    $controller = mb_strtolower(mb_substr($controller, 0, 1)) . mb_substr($controller, 1);
                    $controller = $utils->camelToDash($controller);
                    $foundResources[$moduleName . '_' . $controller] = $moduleName;

                    require_once $controllerPath . DIRECTORY_SEPARATOR . $file;
                    $class_name = ('default' === $moduleName ? '' : $moduleName . '_') . mb_substr($file, 0, -4);
                    foreach (get_class_methods($class_name) as $method) {
                        if (false !== mb_strpos($method, 'Action')) {
                            $action = $utils->camelToDash(mb_substr($method, 0, -6));
                            $foundResources[$moduleName . '_' . $controller . '_' . $action] = $moduleName . '_' . $controller;
                        }
                    }
                }
            }
        }

        return $foundResources;
    }
}
