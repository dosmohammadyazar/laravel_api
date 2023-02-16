<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Blog') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                <div class="pull-right">

                <a class="btn btn-success" href="{{ route('blog.index') }}"> Back</a>
                <br><br>
                </div>
                @if ($errors->any())

                    <div class="alert alert-danger">

                        <strong>Whoops!</strong> There were some problems with your input.<br><br>

                        <ul>

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif
                <form action="{{ route('blog.update',$blog->id) }}" method="POST">

                    @csrf
                    @method('PUT')
                  

                     <div class="row">

                        <div class="col-xs-12 col-sm-12 col-md-12">

                            <div class="form-group">

                                <strong>Title:</strong>

                                <input type="text" name="title" value="{{ $blog->title }}" class="form-control" placeholder="Title">

                            </div>

                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12">

                            <div class="form-group">

                                <strong>Description:</strong>

                                <textarea class="form-control" style="height:150px" name="description" placeholder="Description">{{ $blog->description }}</textarea>

                            </div>

                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 text-center">

                                <button type="submit" class="btn btn-primary">Submit</button>

                        </div>

                    </div>

                   

                </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>