@extends('layouts.app')

@section('title', 'Testimonials')

@section('content')


<div class="row">
    <div class="col-md-12">
        <div class="portlet light portlet-fit bordered cbi-con-wrap">
            <div class="portlet-title bot-sp-sec">
                <div class="caption">
                   
                    <span class="caption-subject font-red sbold uppercase"><img src="{{ URL::asset('assets/images/Current-plan.png')}}"> Testimonials</span>
                  
                  
                </div>

            </div>
            <div class="portlet-body on-user">
                <div class="row">

                    <div class="col-md-4">
                    
                        <a href="{{ route('testimonial.create') }}" class="btn blue add-btn add-user"><i class="fa fa-plus"></i> Add New Testimonial</a>
                   
                   
                    </div>

                   
                </div>

                <div class="row">
                    <img src="{{asset('assets/images/loader.gif')}}" class="loader_gif" style="display: none;">
                    <div class="col-md-12 append_user">

                        <table class="table table-hover table-bordered data_table" id="list_data_table">

                        <thead>
                            <tr>
                                <th>Title</th>
                                
                                @if(Auth::user()->hasRole('Admin'))
                                <th class="txt-cen">Created by</th>
                                @endif
                                <th class="txt-cen">Created at</th>
                                <th class="txt-cen">Operations</th>

                            </tr>
                        </thead>

                        <tbody>
                           
                            @foreach ($data as $k => $val)
                            <tr>
                                <td class="title-cen">

                                     {{ $val->title }} 
                                 </td>
                                
                                 @if(Auth::user()->hasRole('Admin'))
                                <td class="txt-cen">
                                @if(!empty($val->testimonial_created_by))
                                 {{ ucfirst($val->testimonial_created_by->first_name) }} {{ ucfirst($val->testimonial_created_by->last_name) }} 
                                  @endif
                                </td>
                                @endif
                                <td class="txt-cen">
                                 {{ date('d-M-Y',strtotime($val->created_at)) }} 
                                </td>

                     
                                <td class="txt-cen">

                                <a class="list_edit_icon" href="{{route('testimonial.edit',[Crypt::encrypt($val->id)]) }}" > <img src="{{ URL::asset('assets/images/edit-icon.png') }}"/>
                                     </a>
                                 
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['testimonial.destroy', $val->id] , 'onsubmit' =>'return confirm("Are You Sure?")']) }}
                                    {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
                                    {{ Form::close() }}
                                    
                            </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection




