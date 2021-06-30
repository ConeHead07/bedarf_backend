<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 11.04.2020
 * Time: 16:28
 */

namespace App\Utils\Barcodes;


class WebFontLibreBarcode128
{
    private $keys = <<<EOT
 !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
EOT;
    private $startCode = 104;
    private $startCodeAsci = 204;
    private $startCodeChar; // Ì
    private $stopCode = 106;
    private $stopCodeAsci = 206;
    private $stopCodeChar; // Î
    private $keys_func = [];
    private $aCodeToAsci = [];

    public function __construct()
    {
        $this->initKeysFunc();
        $this->intiCodeToAsci();

        $this->startCodeChar = chr( $this->startCodeAsci );
        $this->stopCodeChar = chr( $this->stopCodeAsci );
    }

    private function intiCodeToAsci() {
        $len = strlen($this->keys);
        for($i = 0; $i < $len; $i++) {
            $this->aCodeToAsci[$i] = ord( $this->keys[$i] );
        }
        for($i = 95; $i < 107; $i++) {
            $this->aCodeToAsci[$i] = $i + 100;
        }
    }


    private function initKeysFunc()
    {
        for($i = 95; $i < 107; $i++) {
            $this->keys_func[$i] = chr($i + 100);
        }
    }

    private function getHtmlEntityByChr(string $char): string {
        return '&#' . ord( $char[0] ) . ';';
    }

    private function getHtmlEntityByCode(int $code): string {
        return '&#' . $this->aCodeToAsci[ $code ] . ';';
    }

    private function getHtmlEntityByAsci(int $asci): string {
        return '&#' . $asci . ';';
    }

    public function getCheckCode($barcode): int
    {
        $pruefSum = 0;
        $len = strlen($barcode);
        for($i = 0; $i < $len; $i++) {
            $bcOrd = strpos($this->keys, $barcode[$i] );
            if ($bcOrd === false) {
                error_log('Ungültiges Zeichen ' . $barcode[$i] . '(ASCI-Code: ' . ord($barcode[$i]) . ')  im Barcode: ' . $barcode . '!');
                return '';
            }
            $pruefSum+= ($i+1) * $bcOrd;
        }

        return ($this->startCode + $pruefSum) % 103;
    }

    public function getCodeSequence($barcodeNr, bool $withCtrlChars = true): array {
        $aSequence = $withCtrlChars ? [$this->startCode] : [];

        $len = strlen($barcodeNr);
        for($i = 0; $i < $len; $i++) {
            $_chr = $barcodeNr[$i];
            $_codePosition = strpos( $this->keys, $_chr);
            $aSequence[] = $_codePosition;
        }

        if ($withCtrlChars) {
            $aSequence[] = $this->getCheckCode($barcodeNr);
            $aSequence[] = $this->stopCode;
        }

        return $aSequence;
    }

    public function getHtmlEntitiesBySequence(array $aSequence): string {
        $entities = '';
        foreach($aSequence as $_barValue) {
            $entities.= $this->getHtmlEntityByCode( $_barValue );
        }
        return $entities;
    }

    public function get($barcodeNr): string {

//        $aSequence = $this->getCodeSequence( $barcodeNr );
//        return $this->getHtmlEntitiesBySequence( $aSequence );

        $pruefSum = $this->getCheckCode($barcodeNr);

        return $this->getHtmlEntityByAsci( $this->startCodeAsci )
            . $barcodeNr
            . $this->getHtmlEntityByCode( $pruefSum )
            . $this->getHtmlEntityByAsci( $this->stopCodeAsci );
    }

}
