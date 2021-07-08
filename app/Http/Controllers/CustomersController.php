<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomersController extends Controller
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


    public function getView($msg, $msg_type = 1)
    {

        //$customers = Customer::all();
        \Log::Debug("msg type".$msg_type);
        \Log::Debug("msg ".$msg);
        
        $data = [
            'category_name' => 'customers',
            'page_name' => 'view_customers',
            'has_scrollspy' => 0,
            'scrollspy_offset' => '',
            //'customers' =>$customers,
            'msg' => $msg,
            'msg_type'=>$msg_type
        ];
        
        // $pageName = 'analytics';
        return view('pages.customers.lists')->with($data);        
    }

    public function getCreate($id = '', Request $request)
    {
        if($request->company_name){
            try{
                $data = $request->all();
                $email = $request->contact_person_1_email;
                $customer =  json_decode(json_encode($data), FALSE);                
                $check = Customer::where('contact_person_1_email', $email)->first();
                if($check){
                    //Customer::create($data);
                    $msg = "Same Email Exist";                  
                    return $this->getView($msg, 0);
                }else{
                    Customer::create($data);
                    $msg = "Success";
                    return $this->getView("Success", 1);
                }                
                
            }catch(\Exception $e){
                $msg = "Fail";                
                $data = [
                    'category_name' => 'customers',
                    'page_name' => 'create_customer',
                    'has_scrollspy' => 0,
                    'scrollspy_offset' => '',
                    'customer' =>$customer,
                    'msg' => $msg
                ];       
                return view('pages.customers.create')->with($data);    
            }
            
        }else{
            $customer = null;
            if($id != null){
                $customer = Customer::find($id);
            }
            $data = [
                'category_name' => 'customers',
                'page_name' => 'create_customer',
                'has_scrollspy' => 0,
                'scrollspy_offset' => '',
                'customer' =>$customer
            ];       
            return view('pages.customers.create')->with($data);        

        }        
    }

    public function getUpdate($id = '', Request $request)
    {
                
        $customer  = Customer::findOrFail($request->id);
        $input = $request->all();
        $customer->fill($input)->save();
        return $this->getView("Success", 1);
    }

    public function getDelete($id = '', Request $request)
    {
        if($id == null){
            $id = $request->id;
        }
        Customer::where('id', $id)->delete();
        return $this->getView("Success", 1);
    }
    

    
}
