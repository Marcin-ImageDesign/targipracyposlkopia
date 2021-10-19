<?php

/**
 * Invoice.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Invoice extends Table_Invoice
{
    const DIRECTORY = 'invoice';

    public function getId()
    {
        return $this->id;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getPriceNet()
    {
        return $this->price_net;
    }

    public function getPriceGross()
    {
        return $this->price_gross;
    }

    public function getPriceVat()
    {
        return $this->price_vat;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getStatusId()
    {
        return $this->status_id;
    }

    public function getFileName()
    {
        return $this->file_name;
    }

    public function getFilePath()
    {
        return $this->file_path;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setHash($value, $id_language = null)
    {
        parent::setHash($value, $id_language);
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function setPriceNet($price_net)
    {
        $this->price_net = $price_net;
    }

    public function setPriceGross($price_gross)
    {
        $this->price_gross = $price_gross;
    }

    public function setPriceVat($price_vat)
    {
        $this->price_vat = $price_vat;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setStatusId($status_id)
    {
        $this->status_id = $status_id;
    }

    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    }

    public function setFilePath($file_path)
    {
        $this->file_path = $file_path;
    }

    public function setFileExt($file_ext)
    {
        $this->file_ext = $file_ext;
    }

    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public static function findOneByHash($hash)
    {
        return Doctrine::getTable('Invoice')->findOneByHash($hash);
    }

    public function getRelativePath($createDir = true)
    {
        $relativePath = $this->ExhibParticipation->BaseUser->getPrivateFilesPath($createDir);
        if ($createDir) {
            $utils = Engine_Utils::getInstance();
            $relativePath = $utils->createDirWithPath($relativePath, self::DIRECTORY, '/');
        } else {
            $relativePath = $relativePath . DS . self::DIRECTORY;
        }

        return $relativePath;
    }

    public function getRelativeFile($createDir = true)
    {
        return $this->getRelativePath($createDir) . DS . $this->getRealFileName();
    }

    public function getRealFileName()
    {
        return $this->getId() . '.' . $this->file_ext;
    }

    public function fileExists()
    {
        if (!empty($this->file_ext) && file_exists($this->getRelativeFile())) {
            return true;
        }

        return false;
    }

    public function deleteFile()
    {
        if ($this->fileExists()) {
            unlink($this->getRelativeFile());
        }
        $this->setFileExt('');
    }
}