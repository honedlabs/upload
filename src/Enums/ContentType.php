<?php

namespace Conquest\Upload\Enums;

use Illuminate\Database\Eloquent\Casts\Json;

enum ContentType: string
{
    /** Type sets */
    const Image = 'image';
    const Video = 'video';
    const Application = 'application';
    const Text = 'text';
    const Audio = 'audio';
    
    /** Microsoft types */
    const Word = 'word';
    const Powerpoint = 'ppt';
    const Excel = 'excel';

    /** Image types */
    const Jpeg = 'jpeg';
    const Png = 'png';
    const Gif = 'gif';
    const Webp = 'webp';
    const Svg = 'svg';
    const Avif = 'avif';
    
    /** Video types */
    const Mp4 = 'video/mp4';
    const Mpeg = 'video/mpeg';
    
    /** Audio types */
    const Mp3 = 'audio/mp3';
    const Wav = 'audio/wav';
    const Avi = 'avi';


    /** Other */
    const Zip = 'zip';
    const Pdf = 'pdf';
    const Json = 'json';
    const Csv = 'csv';

    public function getMimeTypes(): array
    {
        return match ($this) {
            self::Image => ['image/*'],
            self::Video => ['video/*'],
            self::Audio => ['audio/*'],
            self::Application => ['application/*'],
            self::Text => ['text/*'],
            self::Word => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            self::Powerpoint => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            self::Excel => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            self::Zip => ['application/zip', 'application/x-zip-compressed'],
            self::Webp => ['image/webp'],
            self::Jpeg => ['image/jpeg'],
            self::Png => ['image/png'],
            self::Gif => ['image/gif'],
            self::Pdf => ['application/pdf'],
            self::Mp4 => ['video/mp4'],
            self::Mpeg => ['video/mpeg'],
            self::Json => ['application/json'],
            self::Csv => ['text/csv'],
            self::Mp3 => ['audio/mp3'],
            self::Wav => ['audio/wav'],
            self::Svg => ['image/svg+xml'],
            self::Avi => ['video/x-msvideo'],
            self::Avif => ['image/avif'],


            default => []
        };
    }

}