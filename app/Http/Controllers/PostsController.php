<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        $data = Post::with('user')
            ->with('commentCount')
            ->with('comments', 'comments.user')
            ->with('category')
            ->orderBy('posts.created_at', 'desc')
            ->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }


        $post = Post::create([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'created_by' => auth()->user()->id,
        ]);
        return response()->json($post, 201);
    }


    public function show($id)
    {
        $post = Post::where("id",$id)->with('commentCount')
            ->with('comments', 'comments.user')
            ->with('category')
            ->with('author')
            ->first();
        if (is_null($post)) {
            return response()->json(['error' => 'Post not found'], 404);
        }
        return response()->json($post);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $post = Post::find($id);
        if (is_null($post)) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $post->update($request->all());
        return response()->json([$post]);
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json([$post]);
    }
}
