<?php

class Service_Image
{
    /**
     * @param int $idImage
     *
     * @return null|Image
     */
    public static function getImage($idImage)
    {
        return Doctrine_Core::getTable('Image')->find($idImage);
    }

    /**
     * @param array $options limit, hydrate, field
     *
     * @return Doctrine_Collection
     */
    public static function getAll(Engine_Doctrine_Record_IdentifiableInterface $object, $options = [])
    {
        if (null === $object) {
            throw new Exception('Object not found.');
        }

        $hydrate = $options['hydrate'] ?? Doctrine_Core::HYDRATE_RECORD
        ;
        $field = array_key_exists('field', $options)
            ? $options['field'] . ''
            : '*'
        ;
        $limit = array_key_exists('limit', $options)
            ? (int) $options['limit'] . ''
            : null
        ;

        $ids = [
            __METHOD__,
            $object->getId(),
            get_class($object),
            $hydrate,
            $field,
            $limit,
        ];

        $key = md5(implode('-', $ids));

        if (!($data = Service_Cache::getCache('managed')->load($key))) {
            $query = Doctrine_Core::getTable('Image')
                ->createQuery('i')
                ->select("i.{$field}")
                ->where('i.object_id = ?', $object->getId())
                ->addWhere('i.class_name = ?', get_class($object))
                ->setHydrationMode($hydrate)
            ;
            if ($limit) {
                $query->limit($limit);
            }

            $query->orderBy('i.order ASC');
            $data = $query->execute();
            Service_Cache::getCache('managed')->save($data, $key, ['images', 'object_id_' . $object->getId(), 'object_class_' . get_class($object)]);
        }

        return $data;
    }

    /**
     * Plik można przekazać jako ścieżka do pliku lub zawartość pliku.
     *
     * source - absolutna ścieżka do pliku
     * data - zawartość pliku
     *
     * @param Engine_Doctrine_Record $object
     * @param array                  $fileInfo Opcje 'name', 'source', 'type', 'data'
     *
     * @return Image
     */
    public static function createImage($object, $fileInfo)
    {
        $utils = Engine_Utils::getInstance();
        Doctrine_Manager::connection()->beginTransaction();

        $imageTypes = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/x-png' => 'png',
        ];

        $extension = isset($imageTypes[$fileInfo['type']])
            ? $imageTypes[$fileInfo['type']]
            : 'jpg'
        ;

        if (isset($fileInfo['data'])) {
            $targetTmpDir = ROOT_PATH . '/data/images/tmp';
            $utils->createDir($targetTmpDir);

            $source = $targetTmpDir . '/' . time() . '.jpg';
            file_put_contents($source, $fileInfo['data']);

            $result = @getimagesize($source);
            if (false === $result) {
                unlink($source);

                throw new Exception('To nie jest poprawny plik graficzny.');
            }

            $fileInfo['source'] = $source;
        }

        if (!file_exists($fileInfo['source'])) {
            throw new Exception('Plik "' . $fileInfo['source'] . '" nie istnieje.');
        }

        $subDirName = date('Y-m');

        $targetDir = "/data/images/{$subDirName}";

        $utils->createDir(ROOT_PATH . $targetDir);

        $newName = $utils->getUniqueFileName($object->getId(), $fileInfo['name'], $extension);
        $filePath = $targetDir . '/' . $newName;

        $rename = new Zend_Filter_File_Rename(['target' => ROOT_PATH . $filePath]);
        $rename->filter($fileInfo['source']);
        chmod(ROOT_PATH . $filePath, FILE_PRIVILIGES);

        $image = new Image();
        $image->type = $fileInfo['type'];
        $image->key = $utils->randString(5);
        $image->file_path = $filePath;
        $image->class_name = get_class($object);
        $image->hash = md5_file(ROOT_PATH . $filePath);
        $image->order = 1;

        if (null !== $object->getId()) {
            $image->order = Doctrine_Query::create()
                ->select('MAX(i.order) as value')
                ->from('Image i')
                ->andWhere('object_id = ?', $object->getId())
                ->andWhere('class_name = ?', get_class($object))
                ->fetchOne()
                ->value + 1;

            $image->object_id = $object->getId();
            $image->save();
        }

        Doctrine_Manager::connection()->commit();

        Service_EventManager::trigger('imageAdd', ['object' => $image]);

        return $image;
    }

    public static function saveImage(Image $img)
    {
        $img->save();
        Service_EventManager::trigger('imageUpdate', ['object' => $img]);
    }

    public static function cleanCache(Image $img)
    {
        Service_EventManager::trigger('imageDelete', ['object' => $img]);
    }

    public static function delete(Image $img)
    {
//    public static function removeImage(Image $img) {

        self::cleanCache($img);

        @unlink(ROOT_PATH . $img->file_path);

        $img->delete();
    }

    /**
     * Zwraca adres url do zdjęcia. Sposób wywołania
     * 1) Service_Image::getUrl($id, $width, $height, $resize, $format)
     * 2) Service_Image::getUrl($id, array('width' => 100, 'height' => 100, 'format' => 'png')).
     *
     * @param int    $id
     * @param int    $width
     * @param int    $height
     * @param string $resize
     * @param int    $id
     * @param array  $params array( 'width' => 100, 'height' => 100 )
     *
     * @return string
     */
    public static function getUrl()
    {
        $options = [
            'id' => null,
            'resize' => 'b',
            'width' => 100,
            'height' => 100,
            'format' => 'jpg',
            'params' => [],
        ];

        $args = $params = func_get_args();
        $options['id'] = array_shift($params);
        $params = array_shift($params);

        if (!is_array($params)) {
            if (isset($args[1])) {
                $options['width'] = $args[1];
            }
            if (isset($args[2])) {
                $options['height'] = $args[2];
            }
            if (isset($args[3])) {
                $options['resize'] = $args[3];
            }
            if (isset($args[4])) {
                $options['format'] = $args[4];
            }
        } else {
            $options = array_merge($options, $params);

            if (isset($params[Image::PARAM_PERSPECTIVE])) {
                $options['params'][] = Image::PARAM_PERSPECTIVE . $params[Image::PARAM_PERSPECTIVE];
                unset($options[Image::PARAM_PERSPECTIVE]);
            }

            if (isset($params[Image::PARAM_CROP])) {
                if (is_array($params[Image::PARAM_CROP])) {
                    $params[Image::PARAM_CROP] =
                        $params[Image::PARAM_CROP]['x'] . ',' . $params[Image::PARAM_CROP]['y'] . ',' .
                        $params[Image::PARAM_CROP]['width'] . ',' . $params[Image::PARAM_CROP]['height'];
                }
                $options['params'][] = Image::PARAM_CROP . $params[Image::PARAM_CROP];
                unset($options[Image::PARAM_CROP]);
            }
        }

        $options['params'] = join('-', $options['params']);
        $options['key'] = Image::getHashKey($options);

        $url = Zend_Controller_Action_HelperBroker::getStaticHelper('Url');

        return CDN_DOMAIN . $url->url($options, 'cdn', true, false);
    }
}
