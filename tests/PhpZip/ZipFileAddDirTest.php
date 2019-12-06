<?php

namespace PhpZip;

use PhpZip\Exception\ZipException;
use PhpZip\Util\Iterator\IgnoreFilesFilterIterator;
use PhpZip\Util\Iterator\IgnoreFilesRecursiveFilterIterator;

/**
 * Test add directory to zip archive.
 *
 * @internal
 *
 * @small
 */
class ZipFileAddDirTest extends ZipTestCase
{
    protected static $files = [
        '.hidden' => 'Hidden file',
        'text file.txt' => 'Text file',
        'Текстовый документ.txt' => 'Текстовый документ',
        'empty dir/' => null,
        'empty dir2/ещё пустой каталог/' => null,
        'catalog/New File' => 'New Catalog File',
        'catalog/New File 2' => 'New Catalog File 2',
        'catalog/Empty Dir/' => null,
        'category/list.txt' => 'Category list',
        'category/Pictures/128x160/Car/01.jpg' => 'File 01.jpg',
        'category/Pictures/128x160/Car/02.jpg' => 'File 02.jpg',
        'category/Pictures/240x320/Car/01.jpg' => 'File 01.jpg',
        'category/Pictures/240x320/Car/02.jpg' => 'File 02.jpg',
    ];

    /**
     * Before test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fillDirectory();
    }

    protected function fillDirectory()
    {
        foreach (self::$files as $name => $content) {
            $fullName = $this->outputDirname . '/' . $name;

            if ($content === null) {
                if (!is_dir($fullName)) {
                    mkdir($fullName, 0755, true);
                }
            } else {
                $dirname = \dirname($fullName);

                if (!is_dir($dirname)) {
                    mkdir($dirname, 0755, true);
                }
                file_put_contents($fullName, $content);
            }
        }
    }

    /**
     * @param ZipFileInterface $zipFile
     * @param array            $actualResultFiles
     * @param string           $localPath
     */
    protected static function assertFilesResult(
        ZipFileInterface $zipFile,
        array $actualResultFiles = [],
        $localPath = '/'
    ) {
        $localPath = rtrim($localPath, '/');
        $localPath = empty($localPath) ? '' : $localPath . '/';
        static::assertCount(\count($zipFile), $actualResultFiles);
        $actualResultFiles = array_flip($actualResultFiles);

        foreach (self::$files as $file => $content) {
            $zipEntryName = $localPath . $file;

            if (isset($actualResultFiles[$file])) {
                static::assertTrue(isset($zipFile[$zipEntryName]));
                static::assertSame($zipFile[$zipEntryName], $content);
                unset($actualResultFiles[$file]);
            } else {
                static::assertFalse(isset($zipFile[$zipEntryName]));
            }
        }
        static::assertEmpty($actualResultFiles);
    }

    /**
     * @throws ZipException
     */
    public function testAddDirWithLocalPath()
    {
        $localPath = 'to/path';

        $zipFile = new ZipFile();
        $zipFile->addDir($this->outputDirname, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                '.hidden',
                'text file.txt',
                'Текстовый документ.txt',
                'empty dir/',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddDirWithoutLocalPath()
    {
        $zipFile = new ZipFile();
        $zipFile->addDir($this->outputDirname);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                '.hidden',
                'text file.txt',
                'Текстовый документ.txt',
                'empty dir/',
            ]
        );
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddFilesFromIterator()
    {
        $localPath = 'to/project';

        $directoryIterator = new \DirectoryIterator($this->outputDirname);

        $zipFile = new ZipFile();
        $zipFile->addFilesFromIterator($directoryIterator, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                '.hidden',
                'text file.txt',
                'Текстовый документ.txt',
                'empty dir/',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddFilesFromIteratorEmptyLocalPath()
    {
        $localPath = '';

        $directoryIterator = new \DirectoryIterator($this->outputDirname);

        $zipFile = new ZipFile();
        $zipFile->addFilesFromIterator($directoryIterator, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                '.hidden',
                'text file.txt',
                'Текстовый документ.txt',
                'empty dir/',
            ]
        );
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddFilesFromRecursiveIterator()
    {
        $localPath = 'to/project';

        $directoryIterator = new \RecursiveDirectoryIterator($this->outputDirname);

        $zipFile = new ZipFile();
        $zipFile->addFilesFromIterator($directoryIterator, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult($zipFile, array_keys(self::$files), $localPath);
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddRecursiveDirWithLocalPath()
    {
        $localPath = 'to/path';

        $zipFile = new ZipFile();
        $zipFile->addDirRecursive($this->outputDirname, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult($zipFile, array_keys(self::$files), $localPath);
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddRecursiveDirWithoutLocalPath()
    {
        $zipFile = new ZipFile();
        $zipFile->addDirRecursive($this->outputDirname);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult($zipFile, array_keys(self::$files));
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddFilesFromIteratorWithIgnoreFiles()
    {
        $localPath = 'to/project';
        $ignoreFiles = [
            'Текстовый документ.txt',
            'empty dir/',
        ];

        $directoryIterator = new \DirectoryIterator($this->outputDirname);
        $ignoreIterator = new IgnoreFilesFilterIterator($directoryIterator, $ignoreFiles);

        $zipFile = new ZipFile();
        $zipFile->addFilesFromIterator($ignoreIterator, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                '.hidden',
                'text file.txt',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testAddFilesFromRecursiveIteratorWithIgnoreFiles()
    {
        $localPath = 'to/project';
        $ignoreFiles = [
            '.hidden',
            'empty dir2/ещё пустой каталог/',
            'list.txt',
            'category/Pictures/240x320',
        ];

        $directoryIterator = new \RecursiveDirectoryIterator($this->outputDirname);
        $ignoreIterator = new IgnoreFilesRecursiveFilterIterator($directoryIterator, $ignoreFiles);

        $zipFile = new ZipFile();
        $zipFile->addFilesFromIterator($ignoreIterator, $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                'text file.txt',
                'Текстовый документ.txt',
                'empty dir/',
                'catalog/New File',
                'catalog/New File 2',
                'catalog/Empty Dir/',
                'category/Pictures/128x160/Car/01.jpg',
                'category/Pictures/128x160/Car/02.jpg',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * Create archive and add files from glob pattern.
     *
     * @throws ZipException
     */
    public function testAddFilesFromGlob()
    {
        $localPath = '/';

        $zipFile = new ZipFile();
        $zipFile->addFilesFromGlob($this->outputDirname, '**.{txt,jpg}', $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                'text file.txt',
                'Текстовый документ.txt',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * Create archive and add recursively files from glob pattern.
     *
     * @throws ZipException
     */
    public function testAddFilesFromGlobRecursive()
    {
        $localPath = '/';

        $zipFile = new ZipFile();
        $zipFile->addFilesFromGlobRecursive($this->outputDirname, '**.{txt,jpg}', $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                'text file.txt',
                'Текстовый документ.txt',
                'category/list.txt',
                'category/Pictures/128x160/Car/01.jpg',
                'category/Pictures/128x160/Car/02.jpg',
                'category/Pictures/240x320/Car/01.jpg',
                'category/Pictures/240x320/Car/02.jpg',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * Create archive and add files from regex pattern.
     *
     * @throws ZipException
     */
    public function testAddFilesFromRegex()
    {
        $localPath = 'path';

        $zipFile = new ZipFile();
        $zipFile->addFilesFromRegex($this->outputDirname, '~\.(txt|jpe?g)$~i', $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                'text file.txt',
                'Текстовый документ.txt',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * Create archive and add files recursively from regex pattern.
     *
     * @throws ZipException
     */
    public function testAddFilesFromRegexRecursive()
    {
        $localPath = '/';

        $zipFile = new ZipFile();
        $zipFile->addFilesFromRegexRecursive($this->outputDirname, '~\.(txt|jpe?g)$~i', $localPath);
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult(
            $zipFile,
            [
                'text file.txt',
                'Текстовый документ.txt',
                'category/list.txt',
                'category/Pictures/128x160/Car/01.jpg',
                'category/Pictures/128x160/Car/02.jpg',
                'category/Pictures/240x320/Car/01.jpg',
                'category/Pictures/240x320/Car/02.jpg',
            ],
            $localPath
        );
        $zipFile->close();
    }

    /**
     * @throws ZipException
     */
    public function testArrayAccessAddDir()
    {
        $localPath = 'path/to';
        $iterator = new \RecursiveDirectoryIterator($this->outputDirname);

        $zipFile = new ZipFile();
        $zipFile[$localPath] = $iterator;
        $zipFile->saveAsFile($this->outputFilename);
        $zipFile->close();

        static::assertCorrectZipArchive($this->outputFilename);

        $zipFile->openFile($this->outputFilename);
        static::assertFilesResult($zipFile, array_keys(self::$files), $localPath);
        $zipFile->close();
    }
}
