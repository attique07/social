<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Traits\Utility;

class StorePostValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $types = Post::getTypes();
        $rules = [
            'type' => ['required', Rule::in($types)],
        ];

        $viewer = $this->user();
        $type = $this->input('type');

        switch ($this->input('type')) {
            case 'text':
                $rules['content'] = ['required'];
                if (setting('feature.post_character_max')) {
                    $rules['content'][] = 'max:'.setting('feature.post_character_max');
                }
                $this->merge(['items' => [],'parent_id' => 0]);

                break;
            case 'file':
            case 'photo':
            case 'link':
            case 'video':
                $this->merge(['parent_id' => 0]);
                $rules['items'] = [
                    'required',
                    function ($attribute, $items, $fail) use ($viewer, $type) {
                        switch ($type) {
                            case 'file':
                            case 'photo':
                                $subjectType = 'storage_files';
                                break;
                            case 'link':
                                $subjectType = 'links';
                                break;
                            case 'video':
                                $subjectType = 'videos';
                                break;
                        }

                        if (! is_array($items)) {
                            return $fail(__('The items are not in the list.'));
                        }

                        if (! count($items)) {
                            return $fail(__('The item is not exist.'));
                        }

                        foreach ($items as $itemId) {
                            $item = PostItem::findByField('id', $itemId);
                            if (! $item || ! $item->canStore($viewer->id, $subjectType)) {
                                return $fail(__('The item is not exist.'));
                            }
                        }

                        if (setting('feature.post_photo_max') && $type == 'photo' && count($items) > setting('feature.post_photo_max')) {
                            return $fail(__('You can only share :number photos at a time.',['number' => setting('feature.post_photo_max')]));
                        }

                        if (in_array($type, ['link', 'video']) && count($items) > 1) {  
                            return $fail(__('The item is not exist.'));
                        }

                        if (setting('post.post_file_max') && $type == 'file' && count($items) > setting('post.post_file_max')) {
                            return $fail(__('You can only upload :number files at a time.',['number' => setting('post.post_file_max')]));
                        }
                    },
                ];
                if (setting('feature.post_character_max')) {
                    $rules['content'] = 'max:'.setting('feature.post_character_max');
                }
                break;
            case 'share':
                $rules['parent_id'] = [
                    'required',
                    function ($attribute, $parentId, $fail) use ($viewer, $type) {
                        $this->merge(['items' => []]);

                        $post = Post::findByField('id', $parentId);
                        if (!($post)) {
                            return $fail(__('The post does not exist.'));
                        }

                        if (!$post->canShare($viewer->id)) {
                            return $fail(__('You cannot share this post.'));
                        }
                    },
                ];
                if (setting('feature.post_character_max')) {
                    $rules['content'] = 'max:'.setting('feature.post_character_max');
                }
                break;
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                
                $data = $validator->getData();

                $this->checkPermissionHaveValue('post.character_max', strlen($data['content']), $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.'),
            'content.required' => __('The content is required.'),
            'photos.required' => __('Photos is required.'),
            'parent_id.required' => __('Parent is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('feature.post_character_max')])
        ];
    }
}
