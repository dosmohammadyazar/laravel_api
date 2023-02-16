<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign User') }}
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
                @if($errors->any())
                <div>
                <strong>Whoops!</strong> There were some problems with your input.<br><br>

                <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
                
                </ul>
                </div>
                @endif

                <form action="{{ route('assign_user_post',$blog->id)}}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <label>Assign to</label>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                    <select name="assigned_to">
                        <option value="">select </option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" <?php if($blog->assigned_to == $user->id ) echo 'selected' ?> >{{ $user->name}}</option>
                        @endforeach
                    </select>
                    </div>
                    <br>
                    <div class="col-xs-12 col-sm-12 col-md-12" style="text-align: left;">

                                <button type="submit" class="btn btn-primary">Submit</button>

                        </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
