<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Robert Rogiński <rroginski@voxcommerce.pl>
 * Date: 20.03.13
 * Time: 10:29.
 */
class Developers_MigrationController extends Zend_Controller_Action
{
    private $time_start;

    private $time_end;

    public function preDispatch()
    {
        ob_end_clean();
        header('Content-type: text/html; charset=utf-8');
        set_time_limit(3600);
        $this->time_start = microtime(true);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Controller_Front::getInstance()->setParam('disableOutputBuffering', true);
    }

    public function postDispatch()
    {
        $this->time_end = microtime(true);
        var_dump(memory_get_usage());
        var_dump($this->time_end - $this->time_start);
        exit();
    }

    public function getImagesAction()
    {
        echo 'Start<br>';
        echo '<br>';
        echo '<div id="dis"></div>';

        $this->migrationImageGo();
        echo '<br/>End<br />';
    }

    public static function flush()
    {
        flush();
        ob_flush();
    }

    public function imageBannerV3Action()
    {
        $standList = Doctrine_Query::create()
            ->from('ExhibStand')
            ->where('data_banner = ""')
            ->execute()
        ;

        echo 'Start<br>';
        echo '<br>';
        echo '<div id="dis"></div>';

        echo 'Stand to migrate: ' . $standList->count() . '<br/>';
        self::flush();

        /** @var ExhibStand $stand */
        $i = 0;
        foreach ($standList as $stand) {
            echo "<script>document.getElementById('dis').innerHTML=" . ++$i . ';</script>';
            self::flush();

            $data_banner = [];
            if (!empty($stand->id_image_banner_top)) {
                $data_banner['top'] = ['id_image' => $stand->id_image_banner_top];
            }
            if (!empty($stand->id_image_banner_desk)) {
                $data_banner['desk'] = ['id_image' => $stand->id_image_banner_desk];
            }
            if (!empty($stand->id_image_banner_tv)) {
                $data_banner['tv'] = ['id_image' => $stand->id_image_banner_tv];
            }
            $stand->data_banner = json_encode($data_banner);
            $stand->save();
        }
    }

    /**
     * Migration stand background from static location
     * to image service.
     */
    public function standViewImageAction()
    {
        $standViewImageList = Doctrine_Query::create()
            ->from('ExhibStandViewImage esvi')
            ->execute()
        ;

        echo 'Start<br>';
        echo '<br>';
        echo '<div id="dis"></div>';

        echo 'Stand View Image to migrate: ' . $standViewImageList->count() . '<br/>';
        self::flush();

        $i = 0;
        /** @var ExhibStandViewImage $standViewImage */
        foreach ($standViewImageList as $standViewImage) {
            echo "<script>document.getElementById('dis').innerHTML=" . ++$i . ';</script>';
            self::flush();

            $file = APPLICATION_WEB . $standViewImage->getBrowseStandImageView();
            $file_cp = APPLICATION_WEB . $standViewImage->getBrowseStandImageView() . '.jpg';
            copy($file, $file_cp);

            if (file_exists($file_cp)) {
                $image = Service_Image::createImage($standViewImage, [
                    'type' => 'jpg',
                    'name' => $standViewImage->getName(),
                    'source' => $file_cp,
                ]);

                $image->save();
                $standViewImage->id_image = $image->getId();
                $standViewImage->save();
            } else {
                Zend_Debug::dump('Stand Image File Not exists (' . $file . '): id: ' . $standViewImage->getId());
                self::flush();
            }
        }
    }

    private function migrationImageGo()
    {
        $imageList = Doctrine_Query::create()
            ->from('Image')
            ->orderBy('id_image ASC')
            ->where('id_image > 3773')
            ->execute()
        ;

        echo 'Image to download: ' . $imageList->count() . '<br/>';
        self::flush();

        $i = 0;
        foreach ($imageList as $image) {
            echo "<script>document.getElementById('dis').innerHTML=" . ++$i . ';</script>';
            self::flush();

            $from = 'http://targi.zumi.pl' . $image->getOrginalUrl();
            $img = file_get_contents($from);

            $to = ROOT_PATH . $image->file_path;
            file_put_contents($to, $img);

            if ($i > 0 && 0 === ($i % 100)) {
                sleep(20);
            }
        }
    }
}
