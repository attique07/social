<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Widget;

use Packages\ShaunSocial\Core\Models\StorageFile;

class LandingPageImageWidget extends BaseWidget
{
    public function getPhotoDefault()
    {
        return asset('images/default/bg_landing.png');
    }

    public function getPhoto($params)
    {
        if (! empty($params['photo_id'])) {
            $storageFile = StorageFile::findByField('id', $params['photo_id']);

            if ($storageFile) {
                return $storageFile->getUrl();
            }
        }

        return $this->getPhotoDefault();
    }

    public function getData($request, $params = [])
    {
        return [
            'photo_url' => $this->getPhoto($params)
        ];
    }

    public function saveData($contentId, $paramsOld = [], $params = [])
    {
        if (! empty($paramsOld['photo_id'])) {
            if (empty($params['photo_id']) || $params['photo_id'] != $paramsOld['photo_id']) { 
                $storageFile = StorageFile::findByField('id', $paramsOld['photo_id']);

                if ($storageFile) {
                    return $storageFile->delete();
                }
            }
        }

        if (! empty($params['photo_id'])) {
            $storageFile = StorageFile::findByField('id', $params['photo_id']);

            if ($storageFile && ! $storageFile->parent_file_id) {
                $storageFile->update([
                    'parent_file_id' => $contentId
                ]);
            }
        }
    }
}
