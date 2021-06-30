<?php

namespace App\Utils;

/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 23.03.2021
 * Time: 01:24
 */

class ImageCompress
{
    public $maxSize = 100 * 1024;
    public $maxWidth = 800;
    public $maxHeight = 800;

    private $_file = '';
    private $_size = 0;
    private $_type = 0;
    private $_mime = '';
    private $_width = 0;
    private $_height = 0;
    private $_error = '';

    public function __construct($file)
    {
        if (!file_exists($file)) {
            $this->setError('Datei wurde nicht gefunden: ' . $file);
            return;
        }
        $info = getimagesize($file);
        if (empty($info)) {
            $this->setError('Unbekanntes Grafikformat: ' . $file);
            return;
        }
        $this->_size = filesize($file);
        if (empty($this->_size)) {
            $this->setError('Bilddatei enthält keine Daten: ' . $file);
            return;
        }
        $this->_file = $file;
        $this->_type = $info[2];
        $this->_width = $info[0];
        $this->_height = $info[1];

        if ($this->_type) {
            $this->_mime = image_type_to_mime_type($this->_type);

            switch($this->_type) {
                case IMG_JPEG:
                case IMG_PNG:
                case IMG_GIF:
                    break;

                default:
                    $this->setError(
                        'Unzulässiges Bildformat ('
                        . $this->_type . ' => ' . $this->_mime . '): '
                        . $file);
            }
        }
    }

    public function isValid(): bool {
        return empty($this->_error);
    }

    public function getError(): string {
        return $this->_error;
    }

    public function file(): string {
        return $this->_file;
    }

    public function width(): int {
        return $this->_width;
    }

    public function height(): int {
        return $this->_height;
    }

    public function size(): int {
        return $this->_size;
    }

    public function type(): int {
        return $this->_type;
    }

    public function mime(): string {
        return image_type_to_mime_type($this->_type);
    }

    public function getGdImage() {
        $f = $this->_file;
        switch($this->_type) {
            case IMG_GIF:
                return imagecreatefromgif($f);

            case IMG_PNG:
                return imagecreatefrompng($f);

            case IMG_JPEG:
                return imagecreatefromjpeg($f);

            default:
                $this->setError('Leerer oder unbekannter Bild-Typ ' . $this->_type);
                return null;
        }
    }

    function getCompressedFile()
    {
        if ($this->_type === IMG_JPEG
            && $this->_size < $this->maxSize
            && $this->_width < $this->maxWidth
            && $this->_height < $this->maxHeight
        ) {
            return $this->_file;
        }

        $wf = min(1, $this->maxWidth / $this->_width);
        $hf = min(1, $this->maxHeight / $this->_height);

        $f = min($wf, $hf);

        $w = $this->_width * $f;
        $h = $this->_height * $f;

        $src = $this->getGdImage();
        if (!$src) {
            return null;
        }

        $dst = imagecreate($w, $h);

        $tmpDir = sys_get_temp_dir();
        $tmpFile = tempnam($tmpDir, 'IMG');

        if (!imagecopyresampled( $dst, $src , 0, 0, 0, 0, $w, $h, $this->_width, $this->_height)) {
            $this->setError('Grafik ' . $this->_file . ' konnte nicht optimiert werden!');
            return null;
        }
        $nextQ = .7;
        do {
            $quality = $nextQ;
            if (!imagejpeg($dst, $tmpFile, $quality)) {
                $this->setError('Grafik-Komprimierung konnte nicht gespeichert werden!');
                return null;
            }
            $nextQ-= .05;
        } while(filesize($tmpFile) > $this->maxSize && $quality >= .5);

        $this->_file = $tmpFile;
        $this->_width = $w;
        $this->_height = $h;
        $this->_size = filesize($tmpFile);
        $this->_type = IMG_JPEG;

        return $tmpFile;
    }

    private function setError(string $error) {
        $this->_error = $error;
    }
}
