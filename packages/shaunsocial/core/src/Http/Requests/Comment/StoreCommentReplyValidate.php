<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Comment;

class StoreCommentReplyValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        $rules = [
            'comment_id' => [
                'required',
                'alpha_num',
                function ($attribute, $commentId, $fail) use ($viewer) {
                    $isAdmin = $viewer ? $viewer->isModerator() : false;
                    $comment = Comment::findByField('id', $commentId);

                    if (! $comment) {
                        return $fail(__('The comment is not found.'));
                    }

                    $subject = $comment->getSubject();
                    if (! $subject) {
                        return $fail(__('The subject is not found.'));
                    }

                    if (method_exists($subject, 'canComment') && ! $subject->canComment($viewer->id) && !$isAdmin) {
                        return $fail(__('The subject cannot comment.'));
                    }
                },
            ],
            'content' => ['required', 'string'],
        ];

        if (setting('feature.comment_character_max')) {
            $rules['content'][] = 'max:'.setting('feature.comment_character_max');
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'comment_id.required' => __('The comment is required.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('feature.comment_character_max')])
        ];
    }
}
