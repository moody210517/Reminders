<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;

class UsersController extends Controller
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
    public function index()
    {
        return view('dashboard');
    }


    public function getView($msg = null, $msg_type = 1)
    {

        $users = User::all();
        $data = [
            'category_name' => 'users',
            'page_name' => 'view_users',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            'users' =>$users,
            'msg'=>$msg,
            'msg_type' =>$msg_type
        ];
        // $pageName = 'analytics';
        return view('pages.users.lists')->with($data);        
    }

    public function getCreate($id = '', Request $request)
    {
        $msg = null;
        if($request->username){

            try{
                $data = $request->all();        
                $user  =   json_decode(json_encode($data), FALSE);
                if( strlen($data['password']) > 20 ){                
                }else{
                    $data['password'] = Hash::make($request->password);
                }       
                User::create($data);
                $msg = "Success";
                return $this->getView($msg, 1);
            }catch(\Exception $e){
                $msg = "Fail";          
                $data = [
                    'category_name' => 'users',
                    'page_name' => 'create_user',
                    'has_scrollspy' => 0,
                    'scrollspy_offset' => '',
                    'user' =>$user,
                    'msg' =>$msg
                ];   
            }                        

        }else{ 
            // edit part
            $user = null;
            if($id != null){
                $user = User::find($id);
            }
            $data = [
                'category_name' => 'users',
                'page_name' => 'create_user',
                'has_scrollspy' => 0,
                'scrollspy_offset' => '',
                'user' =>$user,
                'msg' =>$msg,
                'msg' => 1
            ];   
        }
                
            
        return view('pages.users.create')->with($data);    


    }

    public function getUpdate($id = '', Request $request)
    {
                
        $user  = User::findOrFail($request->id);
        $input = $request->all();
        $user->fill($input)->save();
        return $this->getView('Successful', 1);
    }


    public function getReset($id = '' , Request $request){
        
        if($request->new_password){
            if($request->new_password == $request->confirm_password){
                       
                User::where('id', $request->id)->update(['password' => Hash::make($request->confirm_password) ]);

                $data = [
                    'category_name' => 'users',
                    'page_name' => 'create_user',
                    'has_scrollspy' => 0,
                    'scrollspy_offset' => '',
                    'msg' => 'Success',
                    'id' => $request->id
                ];                       
                return view('pages.users.reset')->with($data);   
            }else{
                $msg = "mismatch password";
            }
        }else{
            $msg = null;
        }

        if($id == null){
            $id = $request->id;
        }
        
        $data = [
            'category_name' => 'users',
            'page_name' => 'create_user',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',    
            'user_id' => $id,
            'msg' => $msg,
            'msg' => 1
        ];       
        return view('pages.users.reset')->with($data);   
    

    }


    public function getDelete($id = '', Request $request)
    {
        if($id == null){
            $id = $request->id;
        }
        User::where('id', $id)->delete();
        return $this->getView('Successful', 1);
    }
    

    
}
