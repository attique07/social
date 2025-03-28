<?php


namespace Packages\ShaunSocial\Core\Services;

use GuzzleHttp\Client;
use Packages\ShaunSocial\Core\Models\Link;
use Symfony\Component\HttpFoundation\File\File as FileCore;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Core\Models\Language;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\Video;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Packages\ShaunSocial\Core\Traits\Utility as TraitsUtility;

class Utility
{
    use TraitsUtility;

    protected $imageTypes = [
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp'
    ];

    public function getContent($url)
    {
        $options = getClientOptions();
        
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,  $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $options['headers']['User-Agent']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);

        $data = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        
        if(isset($info['http_code']) && $info['http_code'] == 302 && !empty($info['redirect_url'])){
            return $this->getContent($info['redirect_url']);
        }else{
            return trim($data);
        }
    }

    function parseLink($url, $viewerId)
    {
        // check link youtube
        $youtubeId = getYoutubeEmbedUrl($url);
        if ($youtubeId)
        {
            $video = Cache::remember('parse_link_youtube_'.$url, config('shaun_core.cache.time.link'), function () use ($youtubeId) {
                $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('https://www.youtube.com/oembed?format=json&url='.urlencode('https://www.youtube.com/watch?v='.$youtubeId));
                return $response->json();
            });
            if ($video) {
                return $this->createLink([
                    'title' => $video['title'],
                    'url' => $url,
                    'description' => '',
                    'image' => isset($video['thumbnail_url']) ? $video['thumbnail_url'] : '',
                    'user_id' => $viewerId,
                ]);
            }
        }

        // check link vimeo
        if ( strpos( $url, 'vimeo.com' ) !== false ){
			preg_match('/(\d+)/', $url, $matches);

            if (!empty($matches[0])) {
                $id = $matches[0];

                $body = Cache::remember('parse_link_vimeo_'.$url, config('shaun_core.cache.time.link'), function () use ($id){
                    $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('http://vimeo.com/api/v2/video/' . $id . '.php');
                    return $response->body();
                });

                if (!strstr($body, 'not found')){
                    $video = unserialize($body);
                    if ($video) {
                        return $this->createLink([
                            'title' => $video[0]['title'],
                            'url' => $url,
                            'description' => strip_tags(str_replace('<br />', '', $video[0]['description'])),
                            'image' => $video[0]['thumbnail_large'],
                            'user_id' => $viewerId
                        ]);
                    }
                }
            }
        }

        //check link tiktok
        if(strpos( $url, 'https://vt.tiktok.com' ) !== false){
            $url = $this->getFinalDestinationURL($url);
        }
        if ( strpos( $url, 'https://www.tiktok.com' ) !== false ){
            $body = Cache::remember('parse_link_tiktok_'.$url, config('shaun_core.cache.time.link'), function () use ($url){
                $response = Http::timeout(config('shaun_core.core.http_timeout'))->get('https://www.tiktok.com/oembed?url='. $url);
                return $response->body();
            });

            $video = json_decode($body, true);
            if (is_array($video) && !empty($video['title'])) {
                return $this->createLink([
                    'title' => $video['title'],
                    'url' => $url,
                    'description' => '',
                    'image' => ! empty($video['thumbnail_url']) ? $video['thumbnail_url'] : '',
                    'user_id' => $viewerId
                ]);
            }
        }

        //check ignore domain
        foreach (config('shaun_core.core.check_fetch_domain') as $domain) {
            if (strpos($url, $domain) === 0) {
                return null;
            }
        }

        try {
            $data = Cache::get('parse_link_'.$url);
            if (! $data) {
                $client = new Client();
                $options = getClientOptions();
                $options['curl'][CURLOPT_NOBODY] = true;

                $responseHeader = $client->get($url, $options);
                $statusCodeHeader = $responseHeader->getStatusCode();
                if ($statusCodeHeader == 200) {
                    $data = null;
                    //header image
                    $header = $responseHeader->getHeader('Content-Type');
                    if (isset($header[0])) {
                        $header = $header[0];
                    }

                    if (in_array($header,$this->imageTypes)) {
                        $data = [
                            'title' => '',
                            'url' => $url,
                            'description' => '',
                            'image' => $url
                        ];
                    } elseif (strpos($header,'application') !== false) {
                        $data = [
                            'title' => $url,
                            'url' => $url,
                            'description' => '',
                            'image' => ''
                        ];
                    } else {
                        $content = $this->getContent($url);
                        if ($content) {
                            $metaTags =$this->getMetaTags($content);
                            $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $content, $matches) ? $matches[1] : null;

                            $description = '';
                            if (! empty($metaTags['description'])) {
                                $description = $metaTags['description'];
                            } elseif (! empty($metaTags['og:description'])) {
                                $description = $metaTags['og:description'];
                            } elseif (! empty($metaTags['twitter:description'])) {
                                $description = $metaTags['twitter:description'];
                            } 

                            $image = '';
                            if (! empty($metaTags['image'])) {
                                $image = $metaTags['image'];
                            } elseif (! empty($metaTags['og:image'])) {
                                $image = $metaTags['og:image'];
                            } elseif (! empty($metaTags['twitter:image'])) {
                                $image = $metaTags['twitter:image'];
                            }
        
                            $data = [
                                'title' => $title ? html_entity_decode($title) : $url,
                                'url' => $url,
                                'description' => html_entity_decode($description),
                                'image' => $image
                            ];
                        }
                    }
                    if ($data) {
                        Cache::add('parse_link_'.$url, $data, config('shaun_core.cache.time.link'));
                    }
                }
            
            }
            if ($data) {
                $data['user_id'] = $viewerId;
                return $this->createLink($data);
            }
        } catch (Exception $e) {
            
        }

        return null;
    }

    function createLink($data)
    {
        $link = Link::create($data);

        if (!empty($data['image'])) {
            $photoPath = File::downloadPhoto($data['image']);
            if ($photoPath) {
                $storageFile = File::storePhoto($photoPath, [
                    'parent_type' => 'link',
                    'parent_id' => $link->id,
                    'user_id' => $link->user_id,
                ]);

                $link->update([
                    'photo_file_id' => $storageFile->id,
                ]);

                $link->setFile('photo_file_id', $storageFile);
            }
        }

        return $link;
    }

    function getMetaTags($str)
    {
        $pattern = '
            ~<\s*meta\s

            # using lookahead to capture type to $1
                (?=[^>]*?
                \b(?:name|property|http-equiv)\s*=\s*
                (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
                ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
            )

            # capture content to $2
            [^>]*?\bcontent\s*=\s*
                (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
                ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
            [^>]*>

            ~ix';

        if (preg_match_all($pattern, $str, $out)) {
            return array_combine($out[1], $out[2]);
        }
        
        return array();
    }

    function storeVideo($file, $viewerId, $isConverted = false, $convertNow = false)
    {
        $ffmpeg = getFFMpeg();
        $result = ['status' => false, 'message' => __('There was an error while uploading your video.')];
        if ($ffmpeg) {
            //validate video
            $duration = $ffmpeg->getFFProbe()->isValid($file->getRealPath());
            if (! $duration) {   
                $result['message'] = __('Video is not validate');
                return $result;
            }

            //check duration
            if (setting('feature.video_max_duration') && $duration > setting('feature.video_max_duration')) {
                $result['message'] = __('The video must not be longer than :second seconds.', ['second' => setting('feature.video_max_duration')]);
                return $result;
            }

            //check permission
            $user = User::findByField('id', $viewerId);
            $this->checkPermissionHaveValue('post.video_max_duration', $duration, $user);

            $converted = false;
            if ($duration <= config('shaun_core.video.limit_duration_convert_now')) {
                $converted = true;
            }

            if ($convertNow) {
                if ($duration > config('shaun_core.video.limit_duration_convert_now')) {
                    $result['message'] = __('The video must not be longer than :second seconds.', ['second' => config('shaun_core.video.limit_duration_convert_now')]);
                    return $result;
                }
            }

            $video = Video::create([
                'is_converted' => $converted || $isConverted,
                'user_id' => $viewerId
            ]);
            //get thumb
            $thumb = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.jpg';
            $videoConvert = $ffmpeg->open($file->getRealPath());
            $fileVideo = $file->getRealPath();
            $videoExtension = $file->getExtension();
            $videoName = $file->getFilename();
            if ($file instanceof UploadedFile) {
                $videoExtension = $file->getClientOriginalExtension();
                $videoName = $file->getClientOriginalName();
            }
            
            if ($converted && !$isConverted) {
                $fileVideo = $this->convertVideo($videoConvert);
                $info = pathinfo($fileVideo);
                $videoExtension = $info['extension'];
                unlink($file->getRealPath());
                $videoConvert = $ffmpeg->open($fileVideo);
                $videoConvert->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                    ->save($thumb);
            } else {
                $videoConvert->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                    ->save($thumb);
            }

            $storageThumbFile = File::storePhoto($thumb, [
                'parent_type' => 'video_thumb',
                'parent_id' => $video->id,
                'user_id' => $viewerId,
            ]);

            $video->setFile('thumb_file_id', $storageThumbFile);

            //store file
            
            $storageVideoFile = File::store(new FileCore($fileVideo), [
                'parent_type' => 'video',
                'parent_id' => $video->id,
                'user_id' => $viewerId,
                'extension' => $videoExtension,
                'name' => $videoName
            ]);

            $video->setFile('file_id', $storageVideoFile);

            $video->update([
                'thumb_file_id' => $storageThumbFile->id,
                'file_id' => $storageVideoFile->id
            ]);
            $result['status'] = true;
            $result['video'] = $video;
        }

        return $result;
    }

    public function convertVideo($videoConvert)
    {
        $videoConvert->filters()->resize(new \FFMpeg\Coordinate\Dimension(1280, 720), \FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET, true)->synchronize();
        $file = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.mp4';
        $format = new \FFMpeg\Format\Video\X264('aac');
        $format->setInitialParameters(array('-noautorotate'));
        $format->setAdditionalParameters(array('-preset','veryfast', '-strict', 'experimental'));
        $videoConvert->save($format, $file);

        return $file;
    }

    public function convertVideoFromVideoModel($video)
    {
        $ffmpeg = getFFMpeg();
        if ($ffmpeg) {
            $file = $video->getFile('file_id');
            $path = storage_path('app/public/'.$file->storage_path);
            if (! file_exists($path)) {
                $url = $file->getUrl();               
                $path = File::downloadFile($url);
            }

            $videoConvert = $ffmpeg->open($path);
            $fileVideo = $this->convertVideo($videoConvert);
            $videoConvert = $ffmpeg->open($fileVideo);
            $thumb = storage_path('tmp').DIRECTORY_SEPARATOR.Str::uuid().'.jpg';
            $videoConvert->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
                ->save($thumb);

            $storageThumbFile = File::storePhoto($thumb, [
                'parent_type' => 'video_thumb',
                'parent_id' => $video->id,
                'user_id' => $video->user_id,
            ]);

            //store file
            $storageVideoFile = File::store(new FileCore($fileVideo), [
                'parent_type' => 'video',
                'parent_id' => $video->id,
                'user_id' => $video->user_id,
            ]);

            $video->update([
                'is_converted' => true,
                'file_id' => $storageVideoFile->id,
                'thumb_file_id' =>  $storageThumbFile->id
            ]);

            return true;
        }

        return false;
    }

    public function getFinalDestinationURL($url) 
    {
        $headers = get_headers($url, 1);
    
        // If "Location" header is set, it contains the final destination URL
        if (isset($headers['Location'])) {
            // If "Location" header is an array, get the last element
            if (is_array($headers['Location'])) {
                return end($headers['Location']);
            } else {
                return $headers['Location'];
            }
        }
    
        // If "Location" header is not set, return the original URL
        return $url;
    }

    public function updateLanguagesExist()
    {
        $languages = Language::getAll();
        $serverInstallPhrases = getServerLanguageArray('install');
        $clientInstallPhrases = getClientLanguageArray('install');

        foreach ($languages as $language) {
            $serverPhrases = getServerLanguageArray($language->key);
            $clientPhrases = getClientLanguageArray($language->key);

            $arrayDiff = array_diff($serverInstallPhrases, $serverPhrases);
            $serverPhrases = $serverPhrases + $arrayDiff;

            $arrayDiff = array_diff($clientInstallPhrases, $clientPhrases);
            $clientPhrases = $clientPhrases + $arrayDiff;

            writeFileLanguageJson(getServerLanguagePath($language->key), $serverPhrases);
            writeFileLanguageJson(getClientLanguagePath($language->key), $clientPhrases);
        }
    }
}
