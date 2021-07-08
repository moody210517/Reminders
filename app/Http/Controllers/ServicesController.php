<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;

class ServicesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getView($msg = null, $msg_type)
    {

        $services = Service::all();
        $data = [
            'category_name' => 'services',
            'page_name' => 'view_services',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            'services' =>$services,
            'msg'=>$msg,
            'msg_type' => $msg_type
        ];
        // $pageName = 'analytics';
        return view('pages.services.lists')->with($data);        
    }

    public function getCreate($id = '', Request $request)
    {
        $msg = null;
        $msg_type = 1;
        if($request->name){
            $data = $request->all();
            $check = Service::where('name', $request->name)->first();
            if($check){
                $msg = "Same Name exist";
                $msg_type = 0;
            }else{
                Service::create($data);
                return $this->getView("Success", 1);
            }                        
        }
        
        $service = null;
        if($id != null){
            $service = Service::find($id);
        }

        $data = [
            'category_name' => 'services',
            'page_name' => 'create_service',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            'service' =>$service,
            'msg' => $msg,
            'msg_type' =>$msg_type
        ];
        return view('pages.services.create')->with($data);     
        
    }

    public function getUpdate($id = '', Request $request)
    {
                
        $service  = Service::findOrFail($request->id);
        $input = $request->all();
        $service->fill($input)->save();

        return $this->getView("Success" , 1 );
    }

    public function getDelete($id = '', Request $request)
    {
        if($id != null){
            Service::where('id', $id)->delete();
        }else{
            $id = $request->id;
            Service::where('id', $id)->delete();
        }
        return $this->getView("Success" , 1 );
    }

    
}
