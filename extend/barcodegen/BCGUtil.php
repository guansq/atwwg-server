<?php
/**
 *--------------------------------------------------------------------
 *
 * Interface for a font.
 *
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://www.barcodephp.com
 */

namespace barcodegen;

class BCGUtil{

    public static function generateCode($codeStr, $fileName = ''){

        if(empty($fileName)){
            $dir = RUNTIME_PATH."barcode/";
            if(!is_dir($dir)){
                @mkdir($dir, 0777);
            }
            $fileName =  "$dir$codeStr.png";
        }

        //$font = new BCGFontFile('extend/barcodegen/font/Arial.ttf', 18);
        $colorFront = new BCGColor(0, 0, 0);
        $colorBack = new BCGColor(255, 255, 255);

        // Barcode Part
        $barcode = new BCGcode128();
        $barcode->setScale(2);
        $barcode->setThickness(30);
        $barcode->setForegroundColor($colorFront);
        $barcode->setBackgroundColor($colorBack);
        //$code->setFont($font);
        $barcode->setStart(NULL);
        $barcode->setTilde(true);
        $barcode->parse($codeStr);

        // Drawing Part
        $drawing = new BCGDrawing('', $colorBack);
        $drawing->setBarcode($barcode);
        $drawing->draw();
        $drawing->setFilename($fileName);
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
        return $fileName;
    }
}

?>