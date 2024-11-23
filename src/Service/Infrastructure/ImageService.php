<?php

namespace App\Service\Infrastructure;

use App\Service\Utility\ImageHelper;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class ImageService
{
    public function __construct(
        private FilterManager $filterManager,
        private DataManager $dataManager,
    ) {}

    public const THUMB_AVATAR = 'avatar_thumb_filter';
    public const THUMB_IMAGE_PROFILE = 'image_profile_thumb_filter';

    public function cropImage(string $from, string $toFullPath, string $filter): void
    {
        $image = $this->dataManager->find($filter, $from);
        $filteredBinary = $this->filterManager->applyFilter($image, $filter);
        $thumb = $filteredBinary->getContent();

        file_put_contents($toFullPath, $thumb);
    }

    public function createBlur(
        string $filePathFrom,
        string $filePathTo,
    ): void {
        /* Get original image size */
        [$w, $h] = getimagesize($filePathFrom);

        $image = imagecreatefromjpeg($filePathFrom);

        /* Create array with width and height of down sized images */
        $size = array('sm'=>array('w'=>intval($w/4), 'h'=>intval($h/4)),
                      'md'=>array('w'=>intval($w/2), 'h'=>intval($h/2))
        );

        /* Scale by 25% and apply Gaussian blur */
        $sm = imagecreatetruecolor($size['sm']['w'],$size['sm']['h']);
        imagecopyresampled($sm, $image, 0, 0, 0, 0, $size['sm']['w'], $size['sm']['h'], $w, $h);

        for ($x=1; $x <=70; $x++){
            imagefilter($sm, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }

        imagefilter($sm, IMG_FILTER_SMOOTH,99);
        imagefilter($sm, IMG_FILTER_BRIGHTNESS, 10);

        /* Scale result by 200% and blur again */
        $md = imagecreatetruecolor($size['md']['w'], $size['md']['h']);
        imagecopyresampled($md, $sm, 0, 0, 0, 0, $size['md']['w'], $size['md']['h'], $size['sm']['w'], $size['sm']['h']);
        imagedestroy($sm);

        for ($x=1; $x <=70; $x++){
            imagefilter($md, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }

        imagefilter($md, IMG_FILTER_SMOOTH,99);
        imagefilter($md, IMG_FILTER_BRIGHTNESS, 10);

        /* Scale result back to original size */
        imagecopyresampled($image, $md, 0, 0, 0, 0, $w, $h, $size['md']['w'], $size['md']['h']);
        imagedestroy($md);

        imagejpeg($image, $filePathTo);
    }
}
