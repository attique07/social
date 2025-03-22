<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Hashtag\GetHashtagValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\DeletePostItemValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\DeletePostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\EditPostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPostProfileValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\GetPostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\StorePostValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\UploadPhotoValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\UploadVideoValidate;
use Packages\ShaunSocial\Core\Http\Requests\Post\UploadFileValidate;
use Packages\ShaunSocial\Core\Http\Requests\Utility\FetchLinkValidate;
use Packages\ShaunSocial\Core\Repositories\Api\PostRepository;

class PostController extends ApiController
{
    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        parent::__construct();
    }

    public function upload_photo(UploadPhotoValidate $request)
    {
        $result = $this->postRepository->upload_photo($request->file('file'), $request->user()->id);
    
        return $this->successResponse($result);
    }

    public function store(StorePostValidate $request)
    {
        $request->mergeIfMissing([
            'parent_id' => 0,
            'content' => '',
            'items' => [],
        ]);

        $result = $this->postRepository->store($request->only([
            'type', 'content', 'items', 'parent_id'
        ]), $request->user());

        return $this->successResponse($result);
    }

    public function profile(GetPostProfileValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->profile($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function profile_media(GetPostProfileValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->profile_media($request->id, $page, $request->user());

        return $this->successResponse($result);
    }

    public function home(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->home($request->user(), $page, getUniqueFromRequest($request));

        return $this->successResponse($result);
    }

    public function delete(DeletePostValidate $request)
    {
        $this->postRepository->delete($request->id);

        return $this->successResponse();
    }

    public function get(GetPostValidate $request)
    {
        $result = $this->postRepository->get($request->id, $request->user(), $request->ip());

        return $this->successResponse($result);
    }

    public function fetch_link(FetchLinkValidate $request)
    {
        $result = $this->postRepository->fetch_link($request->url, $request->user()->id);
        if ($result !== null) {
            return $this->successResponse($result);
        } else {
            return $this->errorNotFound(__('This link error.'));
        }
    }

    public function delete_item(DeletePostItemValidate $request)
    {
        $this->postRepository->delete_item($request->id);

        return $this->successResponse();
    }

    public function hashtag(GetHashtagValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->postRepository->hashtag($request->hashtag, $page, $request->user());

        return $this->successResponse($result);
    }

    public function discovery(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->discovery($request->user(), $page, getUniqueFromRequest($request));

        return $this->successResponse($result);
    }

    public function watch(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->watch($request->user(), $page, getUniqueFromRequest($request));

        return $this->successResponse($result);
    }

    public function media(Request $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->postRepository->media($request->user(), $page);

        return $this->successResponse($result);
    }

    public function store_edit(EditPostValidate $request)
    {
        $result = $this->postRepository->store_edit($request->id, $request->content, $request->user());
    
        return $this->successResponse($result);
    }

    public function upload_video(UploadVideoValidate $request)
    {
        $result = $this->postRepository->upload_video($request->file('file'), $request->get('is_converted', false), $request->get('convert_now', false),$request->user()->id);
        
        if ($result['status']) {
            return $this->successResponse($result['item']);
        } else {
            return $this->errorNotFound($result['message']);
        }
    }

    public function upload_file(UploadFileValidate $request)
    {
        $result = $this->postRepository->upload_file($request->file('file'), $request->user()->id);

        return $this->successResponse($result); 
    }
}
