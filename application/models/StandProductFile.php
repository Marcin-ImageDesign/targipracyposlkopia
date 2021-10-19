<?php

/**
 * StandProductFile.
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @author     ##NAME## <##EMAIL##>
 *
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class StandProductFile extends Table_StandProductFile
{
    const DIRECTORY = 'stand';

    const DIRECTORY_PUBLIC = '_db/stand/file';

    public function getFileName()
    {
        return 'file_' . $this->hash . '.' . $this->getFileExt();
    }

    /**
     * Metoda zwraca ścieżkę zapisu
     * Jeśli katalogu nie ma próbuje go utworzyć.
     *
     * @param bool $createDir
     *
     * @return string
     */
    public function getAbsolutePath($createDir = true)
    {
        $relativePath = $this->StandProduct->ExhibStand->BaseUser->getPrivateFilesPath($createDir);
        if ($createDir) {
            $utils = Engine_Utils::getInstance();
            $relativePath = $utils->createDirWithPath($relativePath, self::DIRECTORY, '/');
        } else {
            $relativePath = $relativePath . DS . self::DIRECTORY;
        }

        return $relativePath;
    }

    public function getBrowserPath()
    {
        return $this->StandProduct->ExhibStand->BaseUser->getPublicBrowserPath() . '/' . self::DIRECTORY_PUBLIC;
    }

    public function getBrowserFile()
    {
        return $this->getBrowserPath() . '/' . $this->getFileName();
    }

    public function getRelativeFile()
    {
        return $this->getAbsolutePath() . DS . $this->getFileName();
    }

    public function fileExists()
    {
        if (!empty($this->file_ext) && file_exists($this->getRelativeFile())) {
            return true;
        }

        return false;
    }

    public function getDownloadFile()
    {
        return $this->getBrowserPath() . '/' . $this->getFileName();
    }

    public function deleteFile()
    {
        if ($this->fileExists()) {
            unlink($this->getRelativeFile());
        }
        $this->setFileExt('');
    }

    public function postDelete($event)
    {
        $this->deleteFile();
    }
}
