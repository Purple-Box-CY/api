<?php

namespace App\Service\Infrastructure;

use App\Service\Utility\FileTypes;
use App\Service\Utility\ImageHelper;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\AdvancedMedia;
use FFMpeg\Media\Audio;
use FFMpeg\Media\Video;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FFMpegService
{
    private FFMpeg $ffmpeg;

    public function __construct(
        string $ffmpegBinariesPath,
        string $ffprobeBinariesPath,
    ) {
        $ffmpegBinariesPath = $ffmpegBinariesPath ?? '/usr/bin/ffmpeg';
        $ffprobeBinariesPath = $ffprobeBinariesPath ?? '/usr/bin/ffprobe';

        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => $ffmpegBinariesPath,
            'ffprobe.binaries' => $ffprobeBinariesPath,
        ]);
    }

    private function getFFMpeg(): FFMpeg
    {
        return $this->ffmpeg;
    }

    public function open(string $pathfile): Audio|Video
    {
        return  $this->getFFMpeg()->open($pathfile);
    }
    public function openAdvanced(string $pathfile): AdvancedMedia
    {
        return  $this->getFFMpeg()->openAdvanced([$pathfile]);
    }

    public function createPreview(UploadedFile $file): ?string
    {
        if (!FileTypes::isVideo($file)) {
            return null;
        }

        $previewFullPath = sprintf('/tmp/%s', ImageHelper::generateFileName('jpg'));
        $filePath = $file->getRealPath();
        $video = $this->open($filePath);

        //create preview with ffmpeg
        $timeCode = TimeCode::fromSeconds(0.01);
        $frame = $video->frame($timeCode);
        $frame->save($previewFullPath);

        return $previewFullPath;
    }
}
