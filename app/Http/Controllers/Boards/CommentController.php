<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = Comment::where('post_id', $postId)->get();

        return response()->json($comments);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'post_id' => 'required',
            'content' => 'required',
            'parent_comment_id' => 'nullable|exists:comments,id',
        ]);

        $depth_no = 0;
        $order_no = 1;
        $group_comment_id = null;

        if (!is_null($request->parent_comment_id)) {
            $parentComment = Comment::findOrFail($request->parent_comment_id);
            $depth_no = $parentComment->depth_no + 1;
            $group_comment_id = $parentComment->group_comment_id ?: $parentComment->id;
            $order_no = Comment::where('parent_comment_id', $request->parent_comment_id)
                                ->where('depth_no', $depth_no)
                                ->max('order_no') + 1;
        } else {
            $order_no = Comment::where('post_id', $request->post_id)
                                ->where('depth_no', 0)
                                ->max('order_no') + 1;
        }

        $comment = Comment::create([
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'content' => $request->content,
            'parent_comment_id' => $request->parent_comment_id,
            'group_comment_id' => $group_comment_id,
            'depth_no' => $depth_no,
            'order_no' => $order_no,
        ]);

        return response()->json($comment, 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        $comments = Comment::where('post_id', $postId)->get();

        return response()->json($comments);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => '해당 댓글을 찾을 수 없습니다.'], 404);
        }

        $request->validate([
            'content' => 'required',
        ]);

        $comment->update($request->all());

        return response()->json($comment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => '해당 댓글을 찾을 수 없습니다.'], 404);
        }

        $comment->delete();

        return response()->json(['message' => '댓글이 삭제되었습니다.']);
    }
}
