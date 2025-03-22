<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreCommentValidate extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num',
            'content' => ['required', 'string'],
        ];

        if (setting('feature.comment_character_max')) {
            $rules['content'][] = 'max:'.setting('feature.comment_character_max');
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewerId = $this->user()->id;
                $isAdmin = $this->user()->isModerator();
                $data = $validator->getData();

                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportComment')) {
                    return $validator->errors()->add('subject', __('The subject does not support comment.'));
                }

                if (method_exists($subject, 'canComment') && ! $subject->canComment($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot comment.'));
                }
            });
        }
    }

    public function messages()
    {
        return [
            'subject_type.required' => __('The subject is required.'),
            'subject_id.required' => __('The subject id is required.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('feature.comment_character_max')])
        ];
    }
}
