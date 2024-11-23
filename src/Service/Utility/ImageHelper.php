<?php

namespace App\Service\Utility;

use \Exception;

class ImageHelper
{
    public static function isHeic(string $path): bool
    {
        try {
            $h = fopen($path, 'rb');
            $f = fread($h, 12);
            fclose($h);
            $magicNumber = strtolower(trim(substr($f, 8)));

            $heicMagicNumbers = [
                'heic', // official
                'mif1', // unofficial but can be found in the wild
                'ftyp', // 10bit images, or anything that uses h265 with range extension
                'hevc', // brands for image sequences
                'hevx', // brands for image sequences
                'heim', // multiview
                'heis', // scalable
                'hevm', // multiview sequence
                'hevs', // multiview sequence
            ];

            if (in_array($magicNumber, $heicMagicNumbers)) {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    public static function isWebp(string $path): bool
    {
        try {
            $h = fopen($path, 'rb');
            $f = fread($h, 12);
            fclose($h);
            $magicNumber = strtolower(trim(substr($f, 8)));

            $webpMagicNumbers = [
                'webp', // official
            ];

            if (in_array($magicNumber, $webpMagicNumbers)) {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    public static function isJpg(string $path): bool
    {
        return exif_imagetype($path) == IMAGETYPE_JPEG;
    }

    public static function isPng(string $path): bool
    {
        try {
            $h = fopen($path, 'rb');
            $f = fread($h, 12);
            fclose($h);
            $magicNumber = strtolower(trim(substr($f, 8)));
            if (!$magicNumber) {
                $magicNumber = strtolower(trim(str_replace(["\n", "\r", ""], '', substr($f, 1))));
            }

            $pngMagicNumbers = [
                'png', // official
            ];

            if (in_array($magicNumber, $pngMagicNumbers)) {
                return true;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }

    public static function convertImageToJpg(string $url): mixed
    {
        $rawFile = self::fileGetContents($url);
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $tmpFile = '/tmp/'.self::generateFileName($ext);
        file_put_contents($tmpFile, $rawFile);

        if (ImageHelper::isWebp($url)) {
            $im = imagecreatefromwebp($tmpFile);
            $fileJpg = '/tmp/'.self::generateFileName('jpg');
            // Convert it to a jpeg file with 100% quality
            imagejpeg($im, $fileJpg, 100);
            imagedestroy($im);
            $file = $fileJpg;
        } elseif (ImageHelper::isJpg($url)) {
            $file = '/tmp/'.self::generateFileName('jpg');
            file_put_contents($file, $rawFile);
        } elseif (ImageHelper::isPng($url)) {
            $im = imagecreatefrompng($tmpFile);
            $fileJpg = '/tmp/'.self::generateFileName('jpg');
            // Convert it to a jpeg file with 100% quality
            imagejpeg($im, $fileJpg, 100);
            imagedestroy($im);
            $file = $fileJpg;
        } elseif (ImageHelper::isHeic($url)) {
            $fileJpg = '/tmp/'.self::generateFileName('jpg');
            $command = sprintf('heif-convert %s %s', $tmpFile, $fileJpg);
            $resExec = exec($command);

            $file = $fileJpg;
        } else {
            $file = $tmpFile;
        }

        $rawFile = self::fileGetContents($file);
        unlink($tmpFile);
        unlink($file);

        return $rawFile;
    }

    public static function generateFileName(
        string $format,
    ): string
    {
        return sprintf('%s.%s', md5(time().rand(0,1000000)), $format);
    }

    public static function fileGetContents(string $url): mixed
    {
        ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)');

        return file_get_contents($url);
    }

    public static function correctImageOrientation(string $filename): void {
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($filename);
            if($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if($orientation != 1){
                    $img = imagecreatefromjpeg($filename);
                    $deg = 0;
                    switch ($orientation) {
                        case 3:
                            $deg = 180;
                            break;
                        case 6:
                            $deg = 270;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) {
                        $img = imagerotate($img, $deg, 0);
                    }
                    // then rewrite the rotated image back to the disk as $filename
                    imagejpeg($img, $filename, 95);
                } // if there is some rotation necessary
            } // if have the exif orientation info
        } // if function exists
    }

}