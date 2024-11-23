<?php

namespace App\Service\Media;

use App\Service\Infrastructure\LogService;
use App\Service\Utility\ImageHelper;

class ImageService
{
    public function __construct(
        private LogService $logger,
    ) {
    }

    public function correctImageOrientation(string $filename): void
    {
        if (!function_exists('exif_read_data')) {
            $this->logger->error('exif_read_data not exists');

            return;
        }

        $exif = @exif_read_data($filename);
        if (!$exif) {
            $this->logger->debug('Image hot has exif '.$filename);
        }
        $this->logger->debug('Image exif data: ', $exif);

        if (!isset($exif['Orientation'])) {
            $this->logger->debug('Image hot has orientation '.$filename);
            return;
        }

        $orientation = $exif['Orientation'] ?? null;
        $this->logger->debug(sprintf('Image orientation=%s, image %s', $orientation, $filename));

        if ($orientation == 1) {
            $this->logger->debug(sprintf('Image not need correct orientation, image %s', $filename));

            return;
        }

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

        $this->logger->debug(sprintf('Image need deg orientation %s', $deg));
        if (!$deg) {
            return;
        }

        $img2 = imagecreatefromjpeg($filename);
        imagejpeg($img2, $filename.'.jpg');

        $img = imagecreatefromjpeg($filename);
        $img = imagerotate($img, $deg, 0);
        imagejpeg($img, $filename);
    }

    public function convertImageToJpg(string $url): mixed
    {
        $rawFile = $this->fileGetContents($url);
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $tmpFile = '/tmp/'.ImageHelper::generateFileName($ext);
        file_put_contents($tmpFile, $rawFile);
        $this->logger->debug(sprintf('Tmp file for convert to jpg. Url: %s', $url));

        if ($this->isWebp($url)) {
            $this->logger->debug(sprintf('File is webp. Url: %s', $url));
            $im = imagecreatefromwebp($tmpFile);
            $fileJpg = '/tmp/'.ImageHelper::generateFileName('jpg');
            // Convert it to a jpeg file with 100% quality
            imagejpeg($im, $fileJpg, 100);
            imagedestroy($im);
            $file = $fileJpg;
        } elseif ($this->isJpg($url)) {
            $this->logger->debug(sprintf('File is jpg. Url: %s', $url));
            $file = '/tmp/'.ImageHelper::generateFileName('jpg');
            file_put_contents($file, $rawFile);
        } elseif ($this->isPng($url)) {
            $this->logger->debug(sprintf('File is png. Url: %s', $url));
            $im = imagecreatefrompng($tmpFile);
            $fileJpg = '/tmp/'.ImageHelper::generateFileName('jpg');
            // Convert it to a jpeg file with 100% quality
            imagejpeg($im, $fileJpg, 100);
            imagedestroy($im);
            $file = $fileJpg;
        } elseif ($this->isHeic($url)) {
            $this->logger->debug(sprintf('File is heic. Url: %s', $url));
            $fileJpg = '/tmp/'.ImageHelper::generateFileName('jpg');
            $command = sprintf('heif-convert %s %s', $tmpFile, $fileJpg);
            try {
                $this->logger->debug(sprintf('Try to convert to tmp file %s. Command: %s', $fileJpg, $command));
                $resExec = exec($command);
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Failed to convert heic to jpg. Command: %s. Error: %s', $command, $e->getMessage()));

                throw $e;
            }

            $file = $fileJpg;
            $this->logger->debug('Success convert heic to jpg');
        } else {
            $this->logger->debug(sprintf('File is not webp, jpg, png, heic. Url: %s', $url));
            $file = $tmpFile;
        }

        $this->logger->debug(sprintf('Try get contents from tmp jpg file %s', $file));
        $rawFile = $this->fileGetContents($file);
        unlink($tmpFile);
        unlink($file);

        return $rawFile;
    }

    public function fileGetContents(string $url): mixed
    {
        try {
            ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');
            return file_get_contents($url);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to get contents by url %s. Error: %s', $url, $e->getMessage()));

            throw $e;
        }
    }

    public function isWebp(string $path): bool
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
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to check format webp for file %s. Error: %s', $path, $e->getMessage()));

            return false;
        }

        return false;
    }

    public function isJpg(string $path): bool
    {
        try {
            return exif_imagetype($path) == IMAGETYPE_JPEG;
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to check format jpg for file %s. Error: %s', $path, $e->getMessage()));

            return false;
        }
    }

    public function isPng(string $path): bool
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
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to check format png for file %s. Error: %s', $path, $e->getMessage()));

            return false;
        }

        return false;
    }

    public function isHeic(string $path): bool
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
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to check format heic for file %s. Error: %s', $path, $e->getMessage()));

            return false;
        }

        return false;
    }
}