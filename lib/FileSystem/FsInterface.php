<?php

/**
 * @author      Laurent Jouanneau
 * @contributor
 *
 * @copyright   2012-2015 Laurent Jouanneau
 *
 * @link        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\BuildTools\FileSystem;

interface FsInterface
{
    /**
     * set the root path of all other operations. For vcs, it should be the
     * directory of source code.
     */
    public function setRootPath($rootPath);

    /**
     * create the given directory.
     *
     * @param string $dir Path of the dir to create relative to root path
     *
     * @return bool false if the directory already exists
     */
    public function createDir($dir);

    /**
     * copy a file into the repository.
     *
     * @param string $fileSource the full path of the source file
     * @param string $fileTarget the relative path of the target file to the root path
     *
     * @return bool true if it has been copied
     */
    public function copyFile($fileSource, $fileTarget);

    /**
     * set the content to a file (and create file if needed).
     *
     * @param string $file    Path of the file, relative to root path
     * @param string $content content of the file
     */
    public function setFileContent($file, $content);

    /**
     * remove the given file.
     *
     * @param string $file Path of the file to remove, relative to root path
     *
     * @return bool true if it has been removed
     */
    public function removeFile($file);

    /**
     * remove the given directory.
     *
     * @param string $dir Path of the dir to remove relative to root path
     *
     * @return bool true if it has been removed
     */
    public function removeDir($dir);

    /**
     * return revision of the files inside the given path.
     * Only for versionning file system.
     *
     * @return string empty string if irrelevant
     */
    public static function revision($path = '.');
}
