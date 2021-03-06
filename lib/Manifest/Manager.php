<?php

/**
 * @author      Laurent Jouanneau
 * @contributor Kévin Lepeltier
 *
 * @copyright   2006-2015 Laurent Jouanneau
 * @copyright   2008 Kévin Lepeltier
 *
 * @link        http://jelix.org
 * @licence     GNU General Public Licence see LICENCE file or http://www.gnu.org/licenses/gpl.html
 */

namespace Jelix\BuildTools\Manifest;

use Jelix\BuildTools\FileSystem as Fs;

/**
 * jManifest copy files indicated in a 'manifest' file, to a specific directory
 * in order to generate a set of PHP files ready to be executed. It can do
 * pre-processing on these files during the copying, strip comments and
 * compress whitespaces, so sources will take less disk spaces and it will
 * improve performances a bit.
 *
 * jManifest supports also VCS like Subversion or Mercurial, so when it detect
 * that new files are added, it will call the VCS to add these files in the repository.
 */
class Manager
{
    /**
     * @var bool true if you want to strip comment and compress whitespaces
     */
    public static $stripComment = false;

    /**
     * @var bool true if you want more messages during the copy
     */
    public static $verbose = false;

    public static $sourcePropertiesFilesDefaultCharset = 'utf-8';

    public static $targetPropertiesFilesCharset = 'utf-8';

    /**
     * when compressing whitespaces, Manager will replace indentation made with spaces
     * by a tab character.
     *
     * @var int the number of spaces for indentation used in your sources
     */
    public static $indentation = 4;

    // the file system object to use
    protected static $fs = null;

    public static function setFileSystem($fsName)
    {
        switch ($fsName) {
            case 'subversion':
            case 'svn':
                self::$fs = new Fs\Subversion();
                break;
            case 'git':
                self::$fs = new Fs\Git();
                break;
            case 'mercurial':
            case 'hg':
                self::$fs = new Fs\Mercurial();
                break;
            default:
                self::$fs = new Fs\Os();
        }
    }

    public static function getFileSystem($rootPath)
    {
        if (self::$fs === null) {
            self::$fs = new Fs\Os();
        }
        self::$fs->setRootPath($rootPath);

        return self::$fs;
    }

    /**
     * read the given manifest file and copy files.
     *
     * @param string $ficlist    manifest file name
     * @param string $sourcepath main directory where it reads files
     * @param string $distpath   main directory were files are copied
     */
    public static function process($ficlist, $sourcepath, $distpath, $preprocvars, $preprocmanifest = false)
    {
        $manifest = new Reader($ficlist, $sourcepath, $distpath);
        $manifest->setVerbose(self::$verbose);
        $manifest->setStripComment(self::$stripComment);
        $manifest->setTargetCharset(self::$targetPropertiesFilesCharset);
        $manifest->setSourceCharset(self::$sourcePropertiesFilesDefaultCharset);
        $manifest->setIndentation(self::$indentation);
        $manifest->process($preprocvars, $preprocmanifest);
    }

    /**
     * delete files indicated in the given manifest file, from the indicated target
     * directory.
     *
     * @param string $ficlist  manifest file name
     * @param string $distpath directory were files are copied
     */
    public static function removeFiles($ficlist, $distpath)
    {
        $distdir = Fs\DirUtils::normalizeDir($distpath);

        $fs = self::getFileSystem($distdir);

        $script = file($ficlist);

        $currentdestdir = '';

        foreach ($script as $nbline => $line) {
            ++$nbline;
            if (preg_match(';^(cd|rmd)?\s+([a-zA-Z0-9\/.\-_]+)\s*$;m', $line, $m)) {
                if ($m[1] == 'rmd') {
                    $fs->removeDir(Fs\DirUtils::normalizeDir($m[2]));
                } elseif ($m[1] == 'cd') {
                    $currentdestdir = Fs\DirUtils::normalizeDir($m[2]);
                } else {
                    if ($m[2] == '') {
                        throw new \Exception("$ficlist : file required on line $nbline \n");
                    }

                    $destfile = $currentdestdir.$m[2];
                    if (!file_exists($distdir.$destfile)) {
                        if (self::$verbose) {
                            echo "cannot remove $destfile. It doesn't exist anymore.\n";
                        }
                        continue;
                    }
                    if (self::$verbose) {
                        echo 'remove  '.$destfile."\n";
                    }
                    if (!$fs->removeFile($destfile)) {
                        throw new \Exception(" $ficlist: cannot remove file ".$m[2].", line $nbline \n");
                    }
                }
            } elseif (preg_match("!^\s*(\#.*)?$!", $line)) {
                // we ignore comments
            } else {
                throw new \Exception("$ficlist : syntax error on line $nbline \n");
            }
        }
    }
}
