<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentResource;
use Packages\ShaunSocial\Core\Http\Resources\Comment\CommentReplyResource;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\History;
use Packages\ShaunSocial\Core\Traits\HasUserList;

class CommentRepository
{
    use HasUserList;

    public function store($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        $comment = Comment::create($data);
        
        // send notify
        $comment->sendMentionNotification($comment->getUSer());

        $subject = findByTypeId($data['subject_type'], $data['subject_id']);
        $subject->sendCommentNotification($viewer, $comment);

        return new CommentResource($comment);
    }

    public function store_reply($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        $reply = CommentReply::create($data);
        
        // send notify
        $reply->sendMentionNotification($reply->getUSer());

        $comment = Comment::findByField('id', $data['comment_id']);
        $comment->sendReplyNotification($viewer, $reply);

        return new CommentReplyResource($reply);
    }

    public function get($subjectType, $subjectId, $page, $viewer)
    {
        $subject = findByTypeId($subjectType, $subjectId);

        $comments = Comment::getCachePagination('comment_'.$subjectType.'_'.$subjectId, Comment::where('subject_type', $subjectType)->where('subject_id', $subjectId)->orderBy('id', 'DESC'), $page);

        return [
            'items' => CommentResource::collection($this->filterUserList($comments, $viewer)),
            'has_next_page' => checkNextPage($subject->comment_count, count($comments), $page)
        ];
    }

    public function get_reply($commentId, $page, $viewer)
    {
        $comment = Comment::findByField('id', $commentId);

        $replies = CommentReply::getCachePagination('reply_'.$commentId, CommentReply::where('comment_id', $commentId)->orderBy('id', 'DESC'), $page);

        return [
            'items' => CommentReplyResource::collection($this->filterUserList($replies, $viewer)),
            'has_next_page' => checkNextPage($comment->reply_count, count($replies), $page)
        ];
    }

    public function delete($commentId)
    {
        $comment = Comment::findByField('id', $commentId);
        $comment->delete();
    }

    public function delete_reply($replyId)
    {
        $reply = CommentReply::findByField('id', $replyId); 
        $reply->delete();
    }

    public function get_single($commentId, $replyId, $viewer)
    {
        $comment = Comment::findByField('id', $commentId);

        $result = ['comment' => new CommentResource($comment)];
        if ($replyId) {
            $reply = CommentReply::findByField('id', $replyId);
            $result['reply'] = new CommentReplyResource($reply);
        }

        return $result;
    }

    public function store_edit($id, $content, $viewer)
    {
        $comment = Comment::findByField('id', $id);
        $mentions = $comment->mentions;

        History::create([
            'user_id' => $viewer->id,
            'content' => $comment->getMentionContent(null),
            'mentions' => $comment->mentions,
            'subject_type' => $comment->getSubjectType(),
            'subject_id' => $comment->id,
        ]);

        $comment->update([
            'content' => $content,
            'is_edited' => true
        ]);
        
        $comment->updateMention();
        $comment->sendMentionNotificationWhenEdit($mentions);

        return new CommentResource($comment);
    }

    public function store_reply_edit($id, $content, $viewer)
    {
        $reply = CommentReply::findByField('id', $id);
        $mentions = $reply->mentions;

        History::create([
            'user_id' => $viewer->id,
            'content' => $reply->getMentionContent(null),
            'mentions' => $reply->mentions,
            'subject_type' => $reply->getSubjectType(),
            'subject_id' => $reply->id,
        ]);

        $reply->update([
            'content' => $content,
            'is_edited' => true
        ]);
        
        $reply->updateMention();
        $reply->sendMentionNotificationWhenEdit($mentions);

        return new CommentReplyResource($reply);
    }
}
