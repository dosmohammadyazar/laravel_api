<?php 
use App\Models\User;



function getname($id){
	if($id != ''){
$data = User::where('id',$id)->first();

return	$data->name;
}else{
	return false;
}
}