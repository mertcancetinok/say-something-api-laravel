<?php

namespace App\Http\Controllers;

use App\Models\PostComment;
use Illuminate\Http\Request;

class PostCommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        return response()->json([PostComment::all()]);
    }

    public function store(Request $request)
    {
        $postComment = PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => auth()->user()->id,
            'comment' => $request->comment,
        ]);

        return response()->json($postComment);
    }

    public function show($lang,$id)
    {
        $postComment = PostComment::find($id);
        if (is_null($postComment)) {
            return response()->json(['message' => 'Record not found'], 404);
        }
        return response()->json(PostComment::findOrFail($id));
    }

    public function update(Request $request, $lang,$id)
    {
        $postComment = PostComment::find($id);
        if (is_null($postComment)) {
            return response()->json(['message' => 'Record not found'], 404);
        }
        $postComment->update($request->all());

        return response()->json($postComment);
    }

    public function destroy($lang,$id)
    {
        $postComment = PostComment::find($id);

        if (is_null($postComment)) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $postComment->delete();

        return response()->json(['message' => 'PostComment deleted successfully']);
    }



}
