<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 9:47
 */

namespace app\spl\controller;


use barcodegen\BCGcode128;
use barcodegen\BCGColor;
use barcodegen\BCGDrawing;

class Test extends Base{


    public function index(){
        return $this->bcgCode128();
    }

    public function bcgCode128(){

        //$font = new BCGFontFile('extend/barcodegen/font/Arial.ttf', 18);
        $colorFront = new BCGColor(0, 0, 0);
        $colorBack = new BCGColor(255, 255, 255);

        // Barcode Part
        $code = new BCGcode128();
        $code->setScale(2);
        $code->setThickness(30);
        $code->setForegroundColor($colorFront);
        $code->setBackgroundColor($colorBack);
        //$code->setFont($font);
        $code->setStart(NULL);
        $code->setTilde(true);
        $code->parse('PO01707200005');

        // Drawing Part
        $drawing = new BCGDrawing('', $colorBack);
        $drawing->setBarcode($code);
        $drawing->draw();
        $drawing->setFilename('test.png');
        header('Content-Type: image/png');
        $drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
        exit();
    }

}