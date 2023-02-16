<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<style type="text/css">
	td{
		text-align: center !important;
	}
</style>
<x-app-layout>
	
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Blogs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                @if(Auth::user()->hasRole(['admin','client']))
                <div class="pull-right">

                <a class="btn btn-success" href="{{ route('blog.create') }}"> Create New Blog</a>
                <br><br>
                </div>
                @endif
                @if ($message = Session::get('success'))

                <div class="alert alert-success">

                <p>{{ $message }}</p>

                </div>

                @endif
                @if ($message = Session::get('fail'))

                <div class="alert alert-danger">

                <p>{{ $message }}</p>

                </div>

                @endif

                    <table id="example" class="display" style="width:100%">
                    	<thead>
                        <tr>

                            <th>Id</th>

                            <th>Title</th>

                            <th>Description</th>

                            <th>Assigned to</th>
                            @if(Auth::user()->hasRole(['admin', 'client']))
                            <th width="280px">Action</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $val)

                        <tr>

                            <td>{{ $val->id }}</td>

                            <td>{{ $val->title }}</td>

                            <td>{{ $val->description }}</td>

                            <td>{{ getname($val->assigned_to) }}</td>
                            @if(Auth::user()->hasRole(['admin', 'client']))
                            <td>
                                <a class="btn btn-primary" href="{{ route('blog.edit',$val->id) }}">Edit</a>
                                /<a class="btn btn-primary" href="{{ route('assign_user',$val->id) }}">Assign User</a>/
                                <form action="{{ route('blog.destroy',$val->id) }}" method="POST">
                                 @csrf
                                 @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                                </form>

                            </td>
                            @endif
                        </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
	$(document).ready( function () {
	$('#example').DataTable();
} );
</script>