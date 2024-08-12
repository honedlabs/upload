<?php

namespace Conquest\Upload\Enums;

enum ContentType: string
{
    /** Type sets */
    case Image = 'image';
    case Video = 'video';
    case Application = 'application';
    case Text = 'text';
    case Audio = 'audio';

    /** Microsoft types */
    case Word = 'word';
    case Powerpoint = 'ppt';
    case Excel = 'excel';

    /** Image types */
    case Jpeg = 'jpeg';
    case Png = 'png';
    case Gif = 'gif';
    case Webp = 'webp';
    case Svg = 'svg';
    case Avif = 'avif';

    /** Video types */
    case Mp4 = 'mp4';
    case Mpeg = 'mpeg';

    /** Audio types */
    case Mp3 = 'mp3';
    case Wav = 'wav';
    case Avi = 'avi';

    /** Other */
    case Zip = 'zip';
    case Pdf = 'pdf';
    case Json = 'json';
    case Csv = 'csv';

    /**
     * Retrieve the mime types for the given content type.
     *
     * @return array<string>
     */
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
        };
    }
}
