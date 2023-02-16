<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Blog::get();
        return view('blog_list',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->hasRole(['admin','client'])){
        return view('blog_add');
        }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->hasRole(['admin','client'])){
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        Blog::create($request->all());
        return redirect()->route('blog.index')->with('success','Blog Created Successfully');
    }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(Blog $blog)
    {
        if(Auth::user()->hasRole(['admin','client'])){
        return view('blog_edit',compact('blog'));
        }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Blog $blog)
    {
        if(Auth::user()->hasRole(['admin','client'])){
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);
        $blog->update($request->all());
        return redirect()->route('blog.index')->with('success','Blog Updated Successfully');
        }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Blog $blog)
    {
        if(Auth::user()->hasRole(['admin','client'])){
        $blog->delete();
        return redirect()->route('blog.index')->with('success','Blog Deleted Successfully');
        }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }
    public function assign_user($blog_id){
        if(Auth::user()->hasRole(['admin','client'])){
        $blog = Blog::where('id',$blog_id)->first();
        $id = Auth::user()->id;
        $users = User::where('id','!=',$id)->get(); 
        return view('assign_user',compact('blog','users'));

        }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }
    public function assign_user_post(Request $request,$blog_id){
        if(Auth::user()->hasRole(['admin','client'])){
        $request->validate([
            'assigned_to' => 'required',
        ]);
        $data['assigned_to'] = $request->post('assigned_to');
         Blog::where('id',$blog_id)->update($data);
        return redirect()->route('blog.index')->with('success','Assigned User Successfully');

        }else{
            return redirect()->route('blog.index')->with('fail','Sorry you have no permission');
        }
    }
    public function blog_list(){
        $data = Blog::latest()->paginate(5);
        return view('blog_list_new',compact('data'))->with('i',(request()->input('page',1)-1)*5);
    }
}
